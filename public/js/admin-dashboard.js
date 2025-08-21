// Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    checkAdminAuth();
    loadDashboardData();
});

function checkAdminAuth() {
    const adminToken = localStorage.getItem('adminToken');
    const adminUser = localStorage.getItem('adminUser');
    
    if (!adminToken || !adminUser) {
        window.location.href = 'admin-login.html';
        return;
    }
    
    try {
        const admin = JSON.parse(adminUser);
        document.getElementById('adminName').textContent = admin.username || 'Admin';
    } catch (error) {
        console.error('Error parsing admin user:', error);
        logout();
    }
}

async function loadDashboardData() {
    try {
        // Load statistics
        await loadStats();
        
        // Load teachers and students
        await loadTeachers();
        await loadStudents();
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

async function loadStats() {
    try {
        const [teachersRes, studentsRes, modulesRes] = await Promise.all([
            fetch('../api/admin-get-teachers.php'),
            fetch('../api/admin-get-students.php'),
            fetch('../api/modules.php')
        ]);

        const teachers = await teachersRes.json();
        const students = await studentsRes.json();
        const modules = await modulesRes.json();

        document.getElementById('teacherCount').textContent = teachers.success ? teachers.data.length : 0;
        document.getElementById('studentCount').textContent = students.success ? students.data.length : 0;
        document.getElementById('moduleCount').textContent = modules.success ? modules.data.length : 0;
        document.getElementById('sessionCount').textContent = Math.floor(Math.random() * 20) + 5; // Mock data for now
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadTeachers() {
    try {
        const response = await fetch('../api/admin-get-teachers.php');
        const data = await response.json();
        
        if (data.success) {
            const teachersList = document.getElementById('teachersList');
            teachersList.innerHTML = '';
            
            if (data.data.length === 0) {
                teachersList.innerHTML = '<p class="text-gray-500 text-center py-4">No teachers found</p>';
                return;
            }
            
            data.data.forEach(teacher => {
                const teacherDiv = document.createElement('div');
                teacherDiv.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                teacherDiv.innerHTML = `
                    <div>
                        <p class="font-medium text-gray-900">${teacher.name || teacher.username}</p>
                        <p class="text-sm text-gray-500">@${teacher.username}${teacher.subject ? ' • ' + teacher.subject : ''}</p>
                        <p class="text-xs text-gray-400">Created: ${new Date(teacher.created_at).toLocaleDateString()}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="editTeacher(${teacher.id})" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteTeacher(${teacher.id})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                teachersList.appendChild(teacherDiv);
            });
        }
    } catch (error) {
        console.error('Error loading teachers:', error);
    }
}

async function loadStudents() {
    try {
        const response = await fetch('../api/admin-get-students.php');
        const data = await response.json();
        
        if (data.success) {
            const studentsList = document.getElementById('studentsList');
            studentsList.innerHTML = '';
            
            if (data.data.length === 0) {
                studentsList.innerHTML = '<p class="text-gray-500 text-center py-4">No students found</p>';
                return;
            }
            
            data.data.forEach(student => {
                const studentDiv = document.createElement('div');
                studentDiv.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                studentDiv.innerHTML = `
                    <div>
                        <p class="font-medium text-gray-900">${student.name}</p>
                        <p class="text-sm text-gray-500">Section: ${student.section} | Username: ${student.username}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="editStudent(${student.id})" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteStudent(${student.id})" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                studentsList.appendChild(studentDiv);
            });
        }
    } catch (error) {
        console.error('Error loading students:', error);
    }
}

// Modal functions
function showAddTeacherModal() {
    document.getElementById('addTeacherModal').classList.remove('hidden');
}

function closeAddTeacherModal() {
    document.getElementById('addTeacherModal').classList.add('hidden');
    document.getElementById('addTeacherForm').reset();
}

function showAddStudentModal() {
    document.getElementById('addStudentModal').classList.remove('hidden');
    loadTeachersForStudentModal();
}

async function loadTeachersForStudentModal() {
    try {
        const response = await fetch('../api/admin-get-teachers.php');
        const data = await response.json();
        
        const teacherSelect = document.getElementById('studentTeacher');
        teacherSelect.innerHTML = '<option value="">-- Select Teacher --</option>';
        
        if (data.success && Array.isArray(data.data)) {
            data.data.forEach(teacher => {
                const option = document.createElement('option');
                option.value = teacher.id;
                option.textContent = `${teacher.name || teacher.username}${teacher.subject ? ' (' + teacher.subject + ')' : ''}`;
                teacherSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading teachers for student modal:', error);
    }
}

function closeAddStudentModal() {
    document.getElementById('addStudentModal').classList.add('hidden');
    document.getElementById('addStudentForm').reset();
}

// Form submissions
document.getElementById('addTeacherForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        username: document.getElementById('teacherUsername').value,
        password: document.getElementById('teacherPassword').value,
        name: document.getElementById('teacherName').value,
        email: document.getElementById('teacherEmail').value,
        subject: document.getElementById('teacherSubject').value,
        grade: document.getElementById('teacherGrade').value,
        sections: document.getElementById('teacherSections').value
    };
    
    try {
        const response = await fetch('../api/admin-add-teacher.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeAddTeacherModal();
            loadTeachers();
            loadStats();
            alert('Teacher added successfully!');
        } else {
            alert(data.message || 'Error adding teacher');
        }
    } catch (error) {
        console.error('Error adding teacher:', error);
        alert('Error adding teacher');
    }
});

document.getElementById('addStudentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Build full name from components
    const lastName = document.getElementById('studentLastName').value;
    const firstName = document.getElementById('studentFirstName').value;
    const middleInitial = document.getElementById('studentMiddleInitial').value;
    const fullName = `${lastName}, ${firstName}${middleInitial ? ' ' + middleInitial + '.' : ''}`;
    
    // Generate username from LRN
    const lrn = document.getElementById('studentLRN').value;
    const username = lrn; // Use LRN as username
    
    const formData = {
        username: username,
        password: document.getElementById('studentPassword').value,
        name: fullName,
        section: document.getElementById('studentGrade').value + '-' + document.getElementById('studentSection').value,
        grade: document.getElementById('studentGrade').value,
        lrn: lrn,
        teacher_id: document.getElementById('studentTeacher').value
    };
    
    try {
        const response = await fetch('../api/admin-add-student.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeAddStudentModal();
            loadStudents();
            loadStats();
            alert('Student added successfully!');
        } else {
            alert(data.message || 'Error adding student');
        }
    } catch (error) {
        console.error('Error adding student:', error);
        alert('Error adding student');
    }
});

// Action functions
async function deleteTeacher(id) {
    if (!confirm('Are you sure you want to delete this teacher?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/admin-delete-teacher.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadTeachers();
            loadStats();
            alert('Teacher deleted successfully!');
        } else {
            alert(data.message || 'Error deleting teacher');
        }
    } catch (error) {
        console.error('Error deleting teacher:', error);
        alert('Error deleting teacher');
    }
}

async function deleteStudent(id) {
    if (!confirm('Are you sure you want to delete this student?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/admin-delete-student.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadStudents();
            loadStats();
            alert('Student deleted successfully!');
        } else {
            alert(data.message || 'Error deleting student');
        }
    } catch (error) {
        console.error('Error deleting student:', error);
        alert('Error deleting student');
    }
}

function editTeacher(id) {
    // TODO: Implement edit functionality
    alert('Edit teacher functionality will be implemented soon!');
}

function editStudent(id) {
    // TODO: Implement edit functionality
    alert('Edit student functionality will be implemented soon!');
}

function exportStudents() {
    // TODO: Implement export functionality
    alert('Export functionality will be implemented soon!');
}

function viewSystemLogs() {
    // TODO: Implement system logs
    alert('System logs functionality will be implemented soon!');
}

function backupDatabase() {
    // TODO: Implement database backup
    alert('Database backup functionality will be implemented soon!');
}

function systemSettings() {
    // TODO: Implement system settings
    alert('System settings functionality will be implemented soon!');
}

function logout() {
    localStorage.removeItem('adminToken');
    localStorage.removeItem('adminUser');
    window.location.href = 'index.html';
}
