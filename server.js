const express = require("express");
const path = require("path");
const sqlite3 = require("sqlite3").verbose();
const multer = require("multer");
const fs = require("fs");
const bcrypt = require("bcrypt");
const saltRounds = 10;

const app = express();
const PORT = 3000;

// Setup multer for file uploads (PDF modules)
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const dir = path.join(__dirname, "public", "modules");
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: (req, file, cb) => {
    const uniqueName = `${Date.now()}-${file.originalname}`;
    cb(null, uniqueName);
  },
});
const upload = multer({ storage });

// Middleware
app.use(express.static(path.join(__dirname, "public")));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Initialize database
const db = new sqlite3.Database("students.db", (err) => {
  if (err) {
    console.error("❌ Failed to connect to DB:", err.message);
  } else {
    console.log("✅ Connected to SQLite database.");
  }
});

// Create tables
db.serialize(() => {
  db.run(`
    CREATE TABLE IF NOT EXISTS admins (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE,
      password TEXT
    )
  `);

  db.run(`
    CREATE TABLE IF NOT EXISTS students (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      firstName TEXT,
      lastName TEXT,
      section TEXT,
      lrn TEXT UNIQUE,
      progress TEXT DEFAULT '{}',
      admin_id INTEGER,
      FOREIGN KEY (admin_id) REFERENCES admins(id)
    )
  `);

  db.run(`
    CREATE TABLE IF NOT EXISTS modules (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      quarter TEXT,
      filename TEXT,
      questions TEXT,
      admin_id INTEGER,
      FOREIGN KEY (admin_id) REFERENCES admins(id)
    )
  `);
});

// ROUTES

// Home
app.get("/", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "login.html"));
});

// Admin panel
app.get("/admin", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "admin.html"));
});

// ✅ Admin Registration (with bcrypt)
app.post("/api/register-admin", async (req, res) => {
  const { username, password } = req.body;

  if (!username || !password) {
    return res.json({ success: false, error: "All fields are required." });
  }

  db.get("SELECT * FROM admins WHERE username = ?", [username], async (err, existing) => {
    if (err) {
      return res.json({ success: false, error: "Database error." });
    }
    if (existing) {
      return res.json({ success: false, error: "Username already exists." });
    }

    const hashedPassword = await bcrypt.hash(password, saltRounds);
    db.run(
      `INSERT INTO admins (username, password) VALUES (?, ?)`,
      [username, hashedPassword],
      function (err) {
        if (err) {
          return res.json({ success: false, error: "Failed to register." });
        }
        res.json({ success: true, id: this.lastID });
      }
    );
  });
});

// ✅ Admin Login (JSON-based)
app.post("/admin-login", (req, res) => {
  const { username, password } = req.body;

  db.get(`SELECT * FROM admins WHERE username = ?`, [username], async (err, admin) => {
    if (err || !admin) {
      console.log("❌ Login failed");
      return res.json({ success: false, error: "Invalid credentials" });
    }

    const match = await bcrypt.compare(password, admin.password);
    if (!match) {
      console.log("❌ Wrong password");
      return res.json({ success: false, error: "Invalid credentials" });
    }

    console.log("✅ Admin login success!");
    return res.json({ success: true });
  });
});

// Get all students
app.get("/students", (req, res) => {
  db.all("SELECT * FROM students", [], (err, rows) => {
    if (err) return res.status(500).json({ error: "Database error" });
    rows.forEach((student) => {
      student.progress = student.progress ? JSON.parse(student.progress) : {};
    });
    res.json(rows);
  });
});

// Update student progress
app.post("/update-progress", (req, res) => {
  const { lrn, module, score } = req.body;

  db.get("SELECT * FROM students WHERE lrn = ?", [lrn], (err, student) => {
    if (err || !student) return res.status(404).json({ error: "Student not found." });

    let progress = {};
    try {
      progress = student.progress ? JSON.parse(student.progress) : {};
    } catch {
      progress = {};
    }

    progress[module] = score;

    db.run(
      `UPDATE students SET progress = ? WHERE lrn = ?`,
      [JSON.stringify(progress), lrn],
      function (err) {
        if (err) return res.status(500).json({ error: "Failed to update progress." });
        res.json({ success: true });
      }
    );
  });
});

// Upload module with PDF + quiz
app.post("/api/create-module", upload.single("pdf"), (req, res) => {
  const { quarter, questions, adminId } = req.body;
  const filename = req.file ? req.file.filename : null;

  if (!quarter || !filename || !questions || !adminId) {
    return res.json({ success: false, error: "Missing required fields" });
  }

  try {
    const stmt = db.prepare(`INSERT INTO modules (quarter, filename, questions, admin_id) VALUES (?, ?, ?, ?)`);
    stmt.run(quarter, filename, questions, adminId);
    res.json({ success: true });
  } catch (err) {
    console.error("❌ Module creation error:", err.message);
    res.json({ success: false, error: "Database error" });
  }
});

// ✅ Update module (from edit-module.html)
app.post("/api/update-module/:id", upload.single("pdf"), (req, res) => {
  const moduleId = req.params.id;
  const { quarter, questions } = req.body;
  const filename = req.file ? req.file.filename : null;

  if (!quarter || !questions) {
    return res.json({ success: false, error: "Missing required fields" });
  }

  const updateQuery = filename
    ? `UPDATE modules SET quarter = ?, filename = ?, questions = ? WHERE id = ?`
    : `UPDATE modules SET quarter = ?, questions = ? WHERE id = ?`;

  const params = filename
    ? [quarter, filename, questions, moduleId]
    : [quarter, questions, moduleId];

  db.run(updateQuery, params, function (err) {
    if (err) {
      console.error("❌ Failed to update module:", err.message);
      return res.json({ success: false, error: "Database error" });
    }

    console.log("✅ Module updated:", moduleId);
    res.json({ success: true });
  });
});

// Get modules
app.get("/api/modules", (req, res) => {
  db.all("SELECT * FROM modules", [], (err, rows) => {
    if (err) return res.status(500).json({ error: "Database error" });
    rows.forEach((mod) => {
      mod.questions = mod.questions ? JSON.parse(mod.questions) : [];
    });
    res.json(rows);
  });
});

// 404 fallback
app.use((req, res) => {
  res.status(404).send("❌ Page not found.");
});

// Start server
app.listen(PORT, () => {
  console.log(`🚀 Server running at http://localhost:${PORT}`);
});
