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

      <div class="dropdown">
        <button class="btn-info dropdown-btn">⬇️ Select Section</button>
        <div class="dropdown-content">
          <a href="#" id="violationRecords">Violation Records</a>
          <a href="#" id="violationAppointments">Violation Appointments</a>
          <a href="#" id="violationAnecdotals">Violation Anecdotals</a>
        </div>
      </div>
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
    <div class="notification-content">
        <div class="notification-icon" id="notificationIcon"></div>
        <div class="notification-message" id="notificationMessage"></div>
        <div class="notification-actions" id="notificationActions"></div>
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
          <button type="submit" class="btn-primary">💾 Save Changes</button>
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
// Get CSRF Token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

const csrfToken = getCsrfToken();

// Notification Modal Functions
function showNotification(message, type = 'info', confirmCallback = null, cancelCallback = null) {
    const modal = document.getElementById('notificationModal');
    const messageEl = document.getElementById('notificationMessage');
    const iconEl = document.getElementById('notificationIcon');
    const actionsEl = document.getElementById('notificationActions');

    // Set message
    messageEl.textContent = message;

    // Set icon and styling based on type
    modal.className = 'notification-modal notification-' + type;
    if (type === 'success') {
        iconEl.textContent = '✅';
    } else if (type === 'error') {
        iconEl.textContent = '❌';
    } else if (type === 'warning') {
        iconEl.textContent = '⚠️';
    } else {
        iconEl.textContent = 'ℹ️';
    }

    // Set up actions
    actionsEl.innerHTML = '';

    if (confirmCallback) {
        // This is a confirmation dialog (with OK/Cancel)
        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'btn-confirm';
        confirmBtn.textContent = 'OK';
        confirmBtn.onclick = function() {
            hideNotification();
            confirmCallback();
        };
        actionsEl.appendChild(confirmBtn);

        // Always add cancel button for confirmation dialogs
        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn-cancel';
        cancelBtn.textContent = 'Cancel';
        cancelBtn.onclick = function() {
            hideNotification();
            if (cancelCallback) cancelCallback();
        };
        actionsEl.appendChild(cancelBtn);
    } else {
        // This is just an alert (only OK button)
        const okBtn = document.createElement('button');
        okBtn.className = 'btn-confirm';
        okBtn.textContent = 'OK';
        okBtn.onclick = hideNotification;
        actionsEl.appendChild(okBtn);
    }

    // Show modal
    modal.style.display = 'flex';
}

function hideNotification() {
    document.getElementById('notificationModal').style.display = 'none';
}

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

// 🗑️ Move to Trash (Archive)
document.getElementById('moveToTrashBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.rowCheckbox:checked');

    if (!selectedCheckboxes.length) {
        showNotification('Please select at least one student.', 'warning');
        return;
    }

    const studentIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    showNotification(
        `Are you sure you want to archive ${studentIds.length} student(s)?`,
        'warning',
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
                    showNotification(`${studentIds.length} student(s) moved to archive.`, 'success');
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
                    showNotification('Error: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error moving students to archive.', 'error');
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
                    <td><span class="status-badge status-inactive">${student.status}</span></td>
                    <td>${new Date(student.updated_at).toLocaleDateString()}</td>
                `;
                archiveTableBody.appendChild(row);
            });
        }

        document.getElementById('archiveModal').style.display = 'flex';
    } catch (error) {
        console.error('Error loading archived students:', error);
        showNotification('Error loading archived students.', 'error');
    }
});

// 🔄 Restore Archived Students
document.getElementById('restoreArchiveBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.archiveCheckbox:checked');

    if (!selectedCheckboxes.length) {
        showNotification('Please select at least one student to restore.', 'warning');
        return;
    }

    const studentIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    showNotification(
        `Are you sure you want to restore ${studentIds.length} student(s)?`,
        'warning',
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
                    showNotification(`${studentIds.length} student(s) restored successfully.`, 'success');
                    // Remove the restored rows from archive table
                    studentIds.forEach(id => {
                        const row = document.querySelector(`#archiveTableBody tr[data-student-id="${id}"]`);
                        if (row) row.remove();
                    });

                    // Reload the page to show restored students in main table
                    location.reload();
                } else {
                    showNotification('Error: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error restoring students.', 'error');
            }
        }
    );
});

// 🗑️ Delete Archived Students Permanently
document.getElementById('deleteArchiveBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.archiveCheckbox:checked');

    if (!selectedCheckboxes.length) {
        showNotification('Please select at least one student to delete permanently.', 'warning');
        return;
    }

    const studentIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    showNotification(
        'WARNING: This will permanently delete these students. This action cannot be undone!',
        'error',
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
                    showNotification(`${studentIds.length} student(s) deleted permanently.`, 'success');
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
                    showNotification('Error: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Error deleting students.', 'error');
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
        hideNotification();
    }
});

// ✏️ Edit Modal Functionality
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-btn');
    const editModal = document.getElementById('editModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const editForm = document.getElementById('editStudentForm');

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

@endsection
