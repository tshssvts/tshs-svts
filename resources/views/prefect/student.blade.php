@extends('prefect.layout')

@section('content')
<div class="main-container">
<meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Toolbar -->
  <div class="toolbar">
    <h2>Student Management</h2>
    <div class="actions">
      <input type="search" placeholder="🔍 Search by student name or ID..." id="searchInput">
      <a href="{{ route('create.student') }}" class="btn-primary" id="createBtn">
        <i class="fas fa-plus"></i> Add Student
      </a>
      <button class="btn-info" id="archiveBtn">🗃️ Archive</button>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="summary">
    <div class="card">
        <h2>{{ $totalStudents }}</h2>
        <p>Total Students</p>
    </div>
    <div class="card">
        <h2>{{ $grade11Students }}</h2>
        <p>Grade 11 Students</p>
    </div>
    <div class="card">
        <h2>{{ $grade12Students }}</h2>
        <p>Grade 12 Students</p>
    </div>
</div>


  <!-- Bulk Action / Select Options -->
  <div class="select-options">
    <div class="left-controls">
      <label for="selectAll" class="select-label">
        <input type="checkbox" id="selectAll">
        <span>Select All</span>
      </label>

      {{-- <div class="dropdown">
        <button class="btn-info dropdown-btn">⬇️ Select Section</button>
        <div class="dropdown-content">
          <a href="#" id="violationRecords">Violation Records</a>
          <a href="#" id="violationAppointments">Violation Appointments</a>
          <a href="#" id="violationAnecdotals">Violation Anecdotals</a>
        </div>
      </div> --}}
    </div>

    <div class="right-controls">
      <button class="btn-cleared" id="markAsClearedBtn">✅ Mark as Cleared</button>
      <button class="btn-danger" id="moveToTrashBtn">🗑️ Move Selected to Trash</button>
    </div>
  </div>

  <!-- Student Table -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Sex</th>
          <th>Birthdate</th>
          <th>Address</th>
          <th>Contact</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        @forelse($students as $student)
        <tr data-student-id="{{ $student->student_id }}">
          <td><input type="checkbox" class="rowCheckbox" value="{{ $student->student_id }}"></td>
          <td>{{ $student->student_id }}</td>
          <td>{{ $student->student_fname }}</td>
          <td>{{ $student->student_lname }}</td>
          <td>{{ ucfirst($student->student_sex) }}</td>
          <td>{{ \Carbon\Carbon::parse($student->student_birthdate)->format('F j, Y') }}</td>
          <td>{{ $student->student_address }}</td>
          <td>{{ $student->student_contactinfo }}</td>
          <td>
            <span class="status-badge {{ $student->status === 'active' ? 'status-active' : 'status-inactive' }}">
              {{ ucfirst($student->status) }}
            </span>
          </td>
          <td>
            <button class="btn-primary edit-btn">✏️ Edit</button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="10" style="text-align:center;">⚠️ No students found</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="pagination-wrapper">
    <div class="pagination-summary">
      Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} of {{ $students->total() }} results
    </div>
    <div class="pagination-links">
      {{ $students->links() }}
    </div>
  </div>

  <!-- Notification Modal -->
  <div class="notification-modal" id="notificationModal">
    <div class="notification-content" id="notificationContent">
      <div class="notification-icon" id="notificationIcon"></div>
      <div class="notification-message" id="notificationMessage"></div>
      <div class="notification-actions" id="notificationActions">
        <!-- OK button removed for success messages -->
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div class="notification-modal" id="confirmationModal">
    <div class="notification-content">
      <div class="notification-icon">⚠️</div>
      <div class="notification-message" id="confirmationMessage"></div>
      <div class="notification-actions">
        <button class="btn-confirm" id="confirmAction">Confirm</button>
        <button class="btn-cancel" id="cancelAction">Cancel</button>
      </div>
    </div>
  </div>

  <!-- ✏️ Edit Student Modal -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <button class="close-btn" id="closeEditModal">✖</button>
      <h2>Edit Student</h2>

      <form id="editStudentForm" method="POST" action="">
        @csrf
        @method('PUT')
        <input type="hidden" name="student_id" id="edit_student_id">

        <div class="form-grid">
          <div class="form-group">
            <label>First Name</label>
            <input type="text" name="student_fname" id="edit_student_fname" required>
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="student_lname" id="edit_student_lname" required>
          </div>
          <div class="form-group">
            <label>Sex</label>
            <select name="student_sex" id="edit_student_sex" required>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>Birthdate</label>
            <input type="date" name="student_birthdate" id="edit_student_birthdate" required>
          </div>
          <div class="form-group">
            <label>Address</label>
            <input type="text" name="student_address" id="edit_student_address" required>
          </div>
          <div class="form-group">
            <label>Contact Info</label>
            <input type="text" name="student_contactinfo" id="edit_student_contactinfo" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" id="edit_student_status" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="transferred">Transferred</option>
              <option value="graduated">Graduated</option>
            </select>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn-primary" id="saveEditBtn">💾 Save Changes</button>
          <button type="button" class="btn-secondary" id="cancelEditBtn">❌ Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 🗃️ Archive Modal -->
  <div class="modal" id="archiveModal">
    <div class="modal-content">
      <div class="modal-header">🗃️ Archived Students</div>
      <div class="modal-body">
        <div class="modal-actions">
          <label class="select-all-label">
            <input type="checkbox" id="selectAllArchived">
            <span>Select All</span>
          </label>
          <div class="search-container">
            <input type="search" id="archiveSearch" placeholder="🔍 Search archived..." class="search-input">
          </div>
        </div>

        <div class="archive-table-container">
          <table class="archive-table">
            <thead>
              <tr>
                <th>✔</th>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Sex</th>
                <th>Status</th>
                <th>Date Archived</th>
              </tr>
            </thead>
            <tbody id="archiveTableBody">
              <!-- Archived students will be loaded here via AJAX -->
            </tbody>
          </table>
        </div>

        <div class="modal-footer">
          <button class="btn-secondary" id="restoreArchiveBtn">🔄 Restore</button>
          <button class="btn-danger" id="deleteArchiveBtn">🗑️ Delete</button>
          <button class="btn-close" id="closeArchive">❌ Close</button>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
// ==========================
// Notification System
// ==========================
class NotificationManager {
    constructor() {
        this.notificationModal = document.getElementById('notificationModal');
        this.confirmationModal = document.getElementById('confirmationModal');
        this.notificationMessage = document.getElementById('notificationMessage');
        this.confirmationMessage = document.getElementById('confirmationMessage');
        this.notificationIcon = document.getElementById('notificationIcon');
        this.notificationActions = document.getElementById('notificationActions');
        this.confirmAction = document.getElementById('confirmAction');
        this.cancelAction = document.getElementById('cancelAction');
        
        this.autoCloseTimeout = null;
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Confirmation modal
        this.confirmAction.addEventListener('click', () => {
            if (this.confirmCallback) {
                this.confirmCallback();
            }
            this.hideConfirmation();
        });

        this.cancelAction.addEventListener('click', () => {
            if (this.cancelCallback) {
                this.cancelCallback();
            }
            this.hideConfirmation();
        });

        // Close modals when clicking outside
        this.notificationModal.addEventListener('click', (e) => {
            if (e.target === this.notificationModal) {
                this.hideNotification();
            }
        });

        this.confirmationModal.addEventListener('click', (e) => {
            if (e.target === this.confirmationModal) {
                this.hideConfirmation();
            }
        });
    }

    showNotification(message, type = 'info') {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        this.notificationIcon.textContent = icons[type] || icons.info;
        this.notificationMessage.textContent = message;
        this.notificationModal.className = `notification-modal notification-${type}`;
        
        // Clear any existing timeout
        if (this.autoCloseTimeout) {
            clearTimeout(this.autoCloseTimeout);
        }
        
        // For success messages, hide OK button and auto-close after 1 second
        if (type === 'success') {
            this.notificationActions.innerHTML = ''; // Remove OK button
            this.notificationModal.style.display = 'flex';
            
            // Auto-close after 1 second
            this.autoCloseTimeout = setTimeout(() => {
                this.hideNotification();
            }, 1000);
        } else {
            // For other message types, show OK button
            this.notificationActions.innerHTML = '<button class="btn-confirm" id="notificationConfirm">OK</button>';
            
            // Add event listener for the newly created button
            const okButton = document.getElementById('notificationConfirm');
            if (okButton) {
                okButton.addEventListener('click', () => {
                    this.hideNotification();
                });
            }
            
            this.notificationModal.style.display = 'flex';
        }
    }

    hideNotification() {
        this.notificationModal.style.display = 'none';
        if (this.autoCloseTimeout) {
            clearTimeout(this.autoCloseTimeout);
            this.autoCloseTimeout = null;
        }
    }

    showConfirmation(message, confirmCallback, cancelCallback = null) {
        this.confirmationMessage.textContent = message;
        this.confirmCallback = confirmCallback;
        this.cancelCallback = cancelCallback;
        this.confirmationModal.style.display = 'flex';
    }

    hideConfirmation() {
        this.confirmationModal.style.display = 'none';
        this.confirmCallback = null;
        this.cancelCallback = null;
    }
}

// Initialize notification manager
const notifications = new NotificationManager();

// Get CSRF Token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

const csrfToken = getCsrfToken();

// 🔍 Search Functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#tableBody tr');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// ✅ Select All - Main Table
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.rowCheckbox');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
    });
});

// ✅ Select All - Archive Table
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'selectAllArchived') {
        const archiveCheckboxes = document.querySelectorAll('.archiveCheckbox');
        archiveCheckboxes.forEach(cb => {
            cb.checked = e.target.checked;
        });
    }
});

// ✅ Mark as Cleared - Move to Archive with Cleared Status
document.getElementById('markAsClearedBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.rowCheckbox:checked');

    if (!selectedCheckboxes.length) {
        notifications.showNotification('Please select at least one student.', 'warning');
        return;
    }

    const studentIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    notifications.showConfirmation(
        `Are you sure you want to mark ${studentIds.length} student(s) as cleared and move to archive?`,
        async function() {
            try {
                const response = await fetch('{{ route("students.markAsCleared") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ student_ids: studentIds })
                });

                const result = await response.json();

                if (result.success) {
                    notifications.showNotification(`${studentIds.length} student(s) marked as cleared and moved to archive.`, 'success');
                    // Remove the cleared rows from the main table
                    studentIds.forEach(id => {
                        const row = document.querySelector(`tr[data-student-id="${id}"]`);
                        if (row) row.remove();
                    });

                    // Update UI
                    document.getElementById('selectAll').checked = false;

                    // Reload to update counts
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    notifications.showNotification('Error: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                notifications.showNotification('Error marking students as cleared.', 'error');
            }
        }
    );
});

// 🗑️ Move to Trash (Archive)
document.getElementById('moveToTrashBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.rowCheckbox:checked');

    if (!selectedCheckboxes.length) {
        notifications.showNotification('Please select at least one student.', 'warning');
        return;
    }

    const studentIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    notifications.showConfirmation(
        `Are you sure you want to archive ${studentIds.length} student(s)?`,
        async function() {
            try {
                const response = await fetch('{{ route("students.archive") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ student_ids: studentIds })
                });

                const result = await response.json();

                if (result.success) {
                    notifications.showNotification(`${studentIds.length} student(s) moved to archive.`, 'success');
                    // Remove the archived rows from the main table
                    studentIds.forEach(id => {
                        const row = document.querySelector(`tr[data-student-id="${id}"]`);
                        if (row) row.remove();
                    });

                    // Update UI
                    document.getElementById('selectAll').checked = false;

                    // Reload to update counts
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    notifications.showNotification('Error: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                notifications.showNotification('Error moving students to archive.', 'error');
            }
        }
    );
});

// 🗃️ Archive Modal - Load archived students
document.getElementById('archiveBtn').addEventListener('click', async function() {
    try {
        const response = await fetch('{{ route("students.getArchived") }}');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const archivedStudents = await response.json();
        const archiveTableBody = document.getElementById('archiveTableBody');
        archiveTableBody.innerHTML = '';

        if (archivedStudents.length === 0) {
            archiveTableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;">⚠️ No archived students found</td></tr>';
        } else {
            archivedStudents.forEach(student => {
                const row = document.createElement('tr');
                row.setAttribute('data-student-id', student.student_id);
                row.innerHTML = `
                    <td><input type="checkbox" class="archiveCheckbox" value="${student.student_id}"></td>
                    <td>${student.student_id}</td>
                    <td>${student.student_fname}</td>
                    <td>${student.student_lname}</td>
                    <td>${student.student_sex}</td>
                    <td><span class="status-badge ${student.status === 'cleared' ? 'status-cleared' : 'status-inactive'}">${student.status}</span></td>
                    <td>${new Date(student.updated_at).toLocaleDateString()}</td>
                `;
                archiveTableBody.appendChild(row);
            });
        }

        document.getElementById('archiveModal').style.display = 'flex';
    } catch (error) {
        console.error('Error loading archived students:', error);
        notifications.showNotification('Error loading archived students.', 'error');
    }
});

// 🔄 Restore Archived Students
document.getElementById('restoreArchiveBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.archiveCheckbox:checked');

    if (!selectedCheckboxes.length) {
        notifications.showNotification('Please select at least one student to restore.', 'warning');
        return;
    }

    const studentIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    notifications.showConfirmation(
        `Are you sure you want to restore ${studentIds.length} student(s)?`,
        async function() {
            try {
                const response = await fetch('{{ route("students.restore") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ student_ids: studentIds })
                });

                const result = await response.json();

                if (result.success) {
                    notifications.showNotification(`${studentIds.length} student(s) restored successfully.`, 'success');
                    // Remove the restored rows from archive table
                    studentIds.forEach(id => {
                        const row = document.querySelector(`#archiveTableBody tr[data-student-id="${id}"]`);
                        if (row) row.remove();
                    });

                    // Reload the page to show restored students in main table
                    location.reload();
                } else {
                    notifications.showNotification('Error: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                notifications.showNotification('Error restoring students.', 'error');
            }
        }
    );
});

// 🗑️ Delete Archived Students Permanently
document.getElementById('deleteArchiveBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.archiveCheckbox:checked');

    if (!selectedCheckboxes.length) {
        notifications.showNotification('Please select at least one student to delete permanently.', 'warning');
        return;
    }

    const studentIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    notifications.showConfirmation(
        'WARNING: This will permanently delete these students. This action cannot be undone!',
        async function() {
            try {
                const response = await fetch('{{ route("students.destroyMultiple") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ student_ids: studentIds })
                });

                const result = await response.json();

                if (result.success) {
                    notifications.showNotification(`${studentIds.length} student(s) deleted permanently.`, 'success');
                    // Remove the deleted rows from archive table
                    studentIds.forEach(id => {
                        const row = document.querySelector(`#archiveTableBody tr[data-student-id="${id}"]`);
                        if (row) row.remove();
                    });

                    // If no more archived students, show message
                    const remainingRows = document.querySelectorAll('#archiveTableBody tr');
                    if (remainingRows.length === 0) {
                        document.getElementById('archiveTableBody').innerHTML = '<tr><td colspan="7" style="text-align:center;">⚠️ No archived students found</td></tr>';
                    }
                } else {
                    notifications.showNotification('Error: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                notifications.showNotification('Error deleting students.', 'error');
            }
        }
    );
});

// Close Archive Modal
document.getElementById('closeArchive').addEventListener('click', function() {
    document.getElementById('archiveModal').style.display = 'none';
});

// Archive Search
document.getElementById('archiveSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#archiveTableBody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const archiveModal = document.getElementById('archiveModal');
    if (event.target === archiveModal) {
        archiveModal.style.display = 'none';
    }

    const notificationModal = document.getElementById('notificationModal');
    if (event.target === notificationModal) {
        notifications.hideNotification();
    }
});

// ✏️ Edit Modal Functionality
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-btn');
    const editModal = document.getElementById('editModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const editForm = document.getElementById('editStudentForm');
    const saveEditBtn = document.getElementById('saveEditBtn');

    // Helper function to close edit modal
    function closeEditModalFunc() {
        editModal.style.display = 'none';
    }

    // 🎯 When "Edit" Button Clicked
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const studentId = row.children[1].innerText.trim();
            const fname = row.children[2].innerText.trim();
            const lname = row.children[3].innerText.trim();
            const sex = row.children[4].innerText.trim();
            const birthdate = row.children[5].innerText.trim();
            const address = row.children[6].innerText.trim();
            const contact = row.children[7].innerText.trim();
            const status = row.querySelector('.status-badge').innerText.trim();

            // Convert birthdate back to YYYY-MM-DD format
            const birthdateObj = new Date(birthdate);
            const formattedBirthdate = birthdateObj.toISOString().split('T')[0];

            // 📝 Fill Form
            document.getElementById('edit_student_id').value = studentId;
            document.getElementById('edit_student_fname').value = fname;
            document.getElementById('edit_student_lname').value = lname;
            document.getElementById('edit_student_sex').value = sex.toLowerCase();
            document.getElementById('edit_student_birthdate').value = formattedBirthdate;
            document.getElementById('edit_student_address').value = address;
            document.getElementById('edit_student_contactinfo').value = contact;
            document.getElementById('edit_student_status').value = status.toLowerCase();

            // Set form action dynamically
            editForm.action = `/prefect/students/update/${studentId}`;

            // Show modal
            editModal.style.display = 'flex';
        });
    });

    // Handle form submission with AJAX
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const studentId = document.getElementById('edit_student_id').value;

        // Debug: Log what we're sending
        console.log('Form action:', this.action);
        console.log('Student ID:', studentId);
        console.log('Form data:', Object.fromEntries(formData));

        // Show loading state
        saveEditBtn.innerHTML = '💾 Saving...';
        saveEditBtn.disabled = true;

        fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(formData).toString()
        })
        .then(response => {
            // First, try to parse as JSON
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    // If it's not JSON, check if it's a redirect or HTML
                    if (response.ok && text.includes('success') || response.redirected) {
                        return { success: true, message: 'Student updated successfully!' };
                    }
                    throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Show success notification modal
                notifications.showNotification(data.message || 'Student updated successfully!', 'success');
                
                // Close the edit modal
                closeEditModalFunc();
                
                // Reload the page after a short delay to show the success message
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                notifications.showNotification(data.message || 'Update failed.', 'error');
            }
        })
        .catch(error => {
            console.error('Full error:', error);
            notifications.showNotification('An error occurred while updating the student. Check console for details.', 'error');
        })
        .finally(() => {
            saveEditBtn.innerHTML = '💾 Save Changes';
            saveEditBtn.disabled = false;
        });
    });

    // ❌ Close / Cancel Modal
    [closeEditModal, cancelEditBtn].forEach(btn => {
        btn.addEventListener('click', () => {
            editModal.style.display = 'none';
        });
    });

    // Close edit modal when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target === editModal) {
            editModal.style.display = 'none';
        }
    });
});
</script>

<style>
.status-cleared {
    background-color: #28a745;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}
</style>

@endsection