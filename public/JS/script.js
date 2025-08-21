// script.js (Universal Script for Login, Register, and Dashboard)

document.addEventListener("DOMContentLoaded", () => {
  const currentPage = window.location.pathname.split("/").pop();

  if (currentPage === "login.html") {
    const lrnInput = document.getElementById("lrn");
    if (lrnInput) {
      document.querySelector("button").addEventListener("click", () => {
        const lrn = lrnInput.value.trim();
        if (!lrn) {
          alert("Please enter your LRN.");
          return;
        }
        localStorage.setItem("studentLRN", lrn);
        window.location.href = "dashboard.html";
      });
    }
  }

  if (currentPage === "register.html") {
    const form = document.getElementById("registerForm");
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const student = {
        lastName: document.getElementById("lastName").value.trim(),
        firstName: document.getElementById("firstName").value.trim(),
        middleInitial: document.getElementById("middleInitial").value.trim(),
        gradeSection: document.getElementById("gradeSection").value.trim(),
        lrn: document.getElementById("lrn").value.trim(),
        progress: {
          module1: false,
          module2: false,
          module3: false,
          module4: false
        }
      };

      let students = JSON.parse(localStorage.getItem("students") || "[]");
      students.push(student);
      localStorage.setItem("students", JSON.stringify(students));

      alert("Account successfully created!");
      window.location.href = "login.html";
    });
  }

  if (currentPage === "teacher-login.html") {
    const loginBtn = document.querySelector("button");
    loginBtn.addEventListener("click", () => {
      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();
      const errorDiv = document.getElementById("error");

      if (username === "jayadmin" && password === "rosemarie2025") {
        localStorage.setItem("isAdmin", "true");
        window.location.href = "teacher-dashboard.html";
      } else {
        errorDiv.textContent = "Access denied. Admin only.";
      }
    });
  }

  if (currentPage === "teacher-dashboard.html") {
    if (localStorage.getItem("isAdmin") !== "true") {
      window.location.href = "teacher-login.html";
      return;
    }

    const tableBody = document.getElementById("studentTableBody");
    const searchInput = document.getElementById("searchInput");

    function getStudents() {
      let sampleStudents = [
        {
          lastName: "Dela Cruz",
          firstName: "Juan",
          section: "Grade 8 - Rizal",
          lrn: "123456789012",
          progress: { module1: true, module2: true, module3: false, module4: false }
        },
        {
          lastName: "Reyes",
          firstName: "Maria",
          section: "Grade 8 - Bonifacio",
          lrn: "987654321098",
          progress: { module1: true, module2: false, module3: false, module4: false }
        }
      ];

      let students = JSON.parse(localStorage.getItem("studentProgress"));
      if (!students) {
        localStorage.setItem("studentProgress", JSON.stringify(sampleStudents));
        students = sampleStudents;
      }
      return students;
    }

    function saveStudents(students) {
      localStorage.setItem("studentProgress", JSON.stringify(students));
    }

    function renderTable(filter = "") {
      const students = getStudents();
      tableBody.innerHTML = "";

      students.forEach((student, index) => {
        const searchTarget = `${student.lastName} ${student.firstName} ${student.section} ${student.lrn}`.toLowerCase();
        if (!searchTarget.includes(filter.toLowerCase())) return;

        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${student.lastName}</td>
          <td>${student.firstName}</td>
          <td>${student.section}</td>
          <td>${student.lrn}</td>
          <td class="${student.progress.module1 ? 'status-complete' : 'status-pending'}">${student.progress.module1 ? '✅' : '❌'}</td>
          <td class="${student.progress.module2 ? 'status-complete' : 'status-pending'}">${student.progress.module2 ? '✅' : '❌'}</td>
          <td class="${student.progress.module3 ? 'status-complete' : 'status-pending'}">${student.progress.module3 ? '✅' : '❌'}</td>
          <td class="${student.progress.module4 ? 'status-complete' : 'status-pending'}">${student.progress.module4 ? '✅' : '❌'}</td>
          <td><button class="reset-btn" onclick="resetProgress(${index})">Reset</button></td>
        `;
        tableBody.appendChild(tr);
      });
    }

    window.resetProgress = function(index) {
      const students = getStudents();
      students[index].progress = {
        module1: false,
        module2: false,
        module3: false,
        module4: false
      };
      saveStudents(students);
      renderTable(searchInput.value);
    };

    document.querySelector(".logout-btn").addEventListener("click", () => {
      localStorage.removeItem("isAdmin");
      window.location.href = "login.html";
    });

    searchInput.addEventListener("input", () => {
      renderTable(searchInput.value);
    });

    renderTable();
  }
});
