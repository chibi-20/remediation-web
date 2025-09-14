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
        // Show user-friendly error message
        showNotification('Error loading dashboard data. Please refresh the page.', 'error');
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
    }
}

async function loadStudents() {
    try {
        const response = await fetch('../api/admin-get-students.php');
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
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
                        <p class="font-medium text-gray-900">${student.displayName || student.fullName || 'Unknown Student'}</p>
                        <p class="text-sm text-gray-500">LRN: ${student.lrn} | Section: ${student.section || 'N/A'} | Grade: ${student.grade || 'N/A'}</p>
                        <p class="text-xs text-gray-400">Teacher: ${student.teacher_name || 'Not assigned'}</p>
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
        } else {
            const studentsList = document.getElementById('studentsList');
            studentsList.innerHTML = '<p class="text-red-500 text-center py-4">Error loading students: ' + (data.message || 'Unknown error') + '</p>';
        }
    } catch (error) {
        const studentsList = document.getElementById('studentsList');
        studentsList.innerHTML = '<p class="text-red-500 text-center py-4">Error connecting to server. Please try again.</p>';
    }
}

// Global variable to store grade levels data
let gradeLevelsData = [];

// Modal functions
function showAddTeacherModal() {
    document.getElementById('addTeacherModal').classList.remove('hidden');
    loadGradeLevelsForTeacher();
}

async function loadGradeLevelsForTeacher() {
    try {
        const response = await fetch('../api/grade-levels.php');
        const data = await response.json();
        
        if (data.success && Array.isArray(data.data)) {
            gradeLevelsData = data.data;
            populateGradeLevelsDropdown();
            
            // Add event listener for grade level changes
            const gradeSelect = document.getElementById('teacherGrade');
            gradeSelect.removeEventListener('change', handleGradeChange); // Remove existing listener
            gradeSelect.addEventListener('change', handleGradeChange);
        } else {
        }
    } catch (error) {
    }
}

function populateGradeLevelsDropdown() {
    const gradeSelect = document.getElementById('teacherGrade');
    
    // Clear existing options
    gradeSelect.innerHTML = '<option value="">Select grade level</option>';
    
    // Add grade levels
    gradeLevelsData.forEach(grade => {
        const option = document.createElement('option');
        option.value = grade.level;
        option.textContent = `Grade ${grade.level}`;
        gradeSelect.appendChild(option);
    });
}

function handleGradeChange() {
    const gradeLevel = document.getElementById('teacherGrade').value;
    loadSectionsForGradeLevel(gradeLevel);
}

function loadSectionsForGradeLevel(gradeLevel) {
    const advisorySectionSelect = document.getElementById('teacherAdvisorySection');
    
    // Clear existing sections
    advisorySectionSelect.innerHTML = '<option value="">Select advisory section (optional)</option>';
    
    if (!gradeLevel) {
        return;
    }
    
    // Find the grade data
    const gradeData = gradeLevelsData.find(g => g.level == gradeLevel);
    
    if (gradeData && gradeData.sections && gradeData.sections.length > 0) {
        gradeData.sections.forEach(section => {
            const option = document.createElement('option');
            option.value = section.name;
            option.textContent = section.name;
            advisorySectionSelect.appendChild(option);
        });
    } else {
        // No sections available for this grade
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No sections available for this grade';
        option.disabled = true;
        advisorySectionSelect.appendChild(option);
    }
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
        teacherSelect.innerHTML = '<option value="">-- Select Adviser --</option>';
        
        if (data.success && Array.isArray(data.data)) {
            // Store teacher data globally for reference
            window.teachersData = data.data;
            
            data.data.forEach(teacher => {
                const option = document.createElement('option');
                option.value = teacher.id;
                option.textContent = `${teacher.name || teacher.username}${teacher.advisory_section ? ' (Advisory: ' + teacher.advisory_section + ')' : ' (No Advisory Section)'}`;
                option.setAttribute('data-advisory-section', teacher.advisory_section || '');
                teacherSelect.appendChild(option);
            });
        }
    } catch (error) {
    }
}

function updateStudentSection() {
    const teacherSelect = document.getElementById('studentTeacher');
    const selectedOption = teacherSelect.options[teacherSelect.selectedIndex];
    const advisorySection = selectedOption.getAttribute('data-advisory-section');
    const advisoryInfo = document.getElementById('advisoryInfo');
    
    if (advisorySection && advisorySection.trim() !== '') {
        // Parse advisory section to set grade and section
        const sectionParts = advisorySection.split('-');
        if (sectionParts.length === 2) {
            const grade = sectionParts[0];
            const section = sectionParts[1];
            
            // Set grade and section automatically
            document.getElementById('studentGrade').value = grade;
            document.getElementById('studentSection').value = section;
            
            advisoryInfo.textContent = `Student will be assigned to ${advisorySection}`;
            advisoryInfo.className = 'text-xs text-green-600 mt-1';
        }
    } else {
        advisoryInfo.textContent = 'Selected teacher has no advisory section. Please select grade and section manually.';
        advisoryInfo.className = 'text-xs text-yellow-600 mt-1';
    }
}

function closeAddStudentModal() {
    document.getElementById('addStudentModal').classList.add('hidden');
    document.getElementById('addStudentForm').reset();
    document.getElementById('advisoryInfo').textContent = 'Student will be assigned to the teacher\'s advisory section';
    document.getElementById('advisoryInfo').className = 'text-xs text-gray-500 mt-1';
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
        advisory_section: document.getElementById('teacherAdvisorySection').value
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

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.getElementById('notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.id = 'notification';
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg max-w-sm`;
    
    // Set color based on type
    switch (type) {
        case 'error':
            notification.className += ' bg-red-500 text-white';
            break;
        case 'success':
            notification.className += ' bg-green-500 text-white';
            break;
        case 'warning':
            notification.className += ' bg-yellow-500 text-white';
            break;
        default:
            notification.className += ' bg-blue-500 text-white';
    }
    
    notification.innerHTML = `
        <div class="flex items-center">
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification && notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function backupDatabase() {
    // TODO: Implement database backup
    alert('Database backup functionality will be implemented soon!');
}

function systemSettings() {
    // TODO: Implement system settings
    alert('System settings functionality will be implemented soon!');
}

function openSecurityDashboard() {
    // Get admin authentication data
    const adminToken = localStorage.getItem('adminToken');
    const adminUser = localStorage.getItem('adminUser');
    
    if (!adminToken || !adminUser) {
        showNotification('Please login as admin first', 'error');
        return;
    }
    
    // Open security dashboard in a new tab/window with authentication context
    const securityWindow = window.open('security-dashboard.html', '_blank');
    
    // Pass authentication context to the new window when it loads
    securityWindow.addEventListener('load', function() {
        if (securityWindow.localStorage) {
            securityWindow.localStorage.setItem('adminToken', adminToken);
            securityWindow.localStorage.setItem('adminUser', adminUser);
        }
    });
}

function logout() {
    localStorage.removeItem('adminToken');
    localStorage.removeItem('adminUser');
    window.location.href = 'index.html';
}
