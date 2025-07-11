const express = require('express');
const path = require('path');
const cors = require('cors');

const db = require('./database'); // our SQLite logic

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

// === ROUTES ===

// Serve main pages
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'login.html'));
});

app.get('/admin', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'admin.html'));
});

// === API Routes ===

// Get all students with module progress
app.get('/api/students', (req, res) => {
  try {
    const students = db.getAllStudentsWithProgress();
    const formatted = students.map(s => ({
      id: s.id,
      lastName: s.lastName,
      firstName: s.firstName,
      section: `${s.grade} - ${s.section}`,
      lrn: s.lrn,
      progress: {
        module1: !!s.module1,
        module2: !!s.module2,
        module3: !!s.module3,
        module4: !!s.module4
      }
    }));
    res.json(formatted);
  } catch (error) {
    console.error('Failed to fetch students:', error);
    res.status(500).json({ error: 'Internal Server Error' });
  }
});

// Reset student progress
app.post('/api/reset-progress', (req, res) => {
  const { id } = req.body;
  try {
    db.resetStudentProgress(id);
    res.json({ success: true });
  } catch (error) {
    console.error('Reset failed:', error);
    res.status(500).json({ error: 'Reset failed' });
  }
});

// Register new student
app.post('/api/register', (req, res) => {
  try {
    db.insertStudent(req.body);
    res.json({ success: true });
  } catch (error) {
    console.error('Registration error:', error);
    res.status(500).json({ error: 'Registration failed. LRN might already exist.' });
  }
});

// Fallback route
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'login.html'));
});

app.listen(PORT, () => {
  console.log(`🚀 Server running at http://localhost:${PORT}`);
});
