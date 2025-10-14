@extends('prefect.layout')

@section('content')
<div class="main-container">
<meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Toolbar -->
  <div class="toolbar">
    <h2>Adviser Management</h2>
    <div class="actions">
      <input type="search" placeholder="🔍 Search by adviser name or ID..." id="searchInput">
      <a href="{{ route('create.adviser') }}" class="btn-primary" id="createBtn">
        <i class="fas fa-plus"></i> Add Adviser
      </a>
      <button class="btn-info" id="archiveBtn">🗃️ Archive</button>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="summary">
    <div class="card">
        <h2>{{ $totalAdvisers }}</h2>
        <p>Total Adviser</p>
    </div>
    <div class="card">
        <h2>{{ $grade11Advisers }}</h2>
        <p>Grade 11 Advisers</p>
    </div>
    <div class="card">
        <h2>{{ $grade12Advisers }}</h2>
        <p>Grade 12 Advisers</p>
    </div>
</div>

  <!-- Bulk Action / Select Options -->
  <div class="select-options">
    <div class="left-controls">
      <label for="selectAll" class="select-label">
        <input type="checkbox" id="selectAll">
        <span>Select All</span>
      </label>
    </div>

    <div class="right-controls">
      <button class="btn-danger" id="moveToTrashBtn">🗑️ Move Selected to Trash</button>
    </div>
  </div>

  <!-- Adviser Table -->
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Section</th>
          <th>Grade Level</th>
          <th>Email</th>
          <th>Contact</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        @forelse($advisers as $adviser)
        <tr data-adviser-id="{{ $adviser->adviser_id }}">
          <td><input type="checkbox" class="rowCheckbox" value="{{ $adviser->adviser_id }}"></td>
          <td>{{ $adviser->adviser_id }}</td>
          <td>{{ $adviser->adviser_fname }}</td>
          <td>{{ $adviser->adviser_lname }}</td>
          <td>{{ $adviser->adviser_section }}</td>
          <td>{{ $adviser->adviser_gradelevel }}</td>
          <td>{{ $adviser->adviser_email }}</td>
          <td>{{ $adviser->adviser_contactinfo }}</td>
          <td>
            <span class="status-badge {{ $adviser->status === 'active' ? 'status-active' : 'status-inactive' }}">
              {{ ucfirst($adviser->status) }}
            </span>
          </td>
          <td>
            <button class="btn-primary edit-btn">✏️ Edit</button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="10" style="text-align:center;">⚠️ No advisers found</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="pagination-wrapper">
    <div class="pagination-summary">
      Showing {{ $advisers->firstItem() }} to {{ $advisers->lastItem() }} of {{ $advisers->total() }} results
    </div>
    <div class="pagination-links">
      {{ $advisers->links() }}
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

  <!-- ✏️ Edit Adviser Modal -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <button class="close-btn" id="closeEditModal">✖</button>
      <h2>Edit Adviser</h2>
      <form id="editAdviserForm" method="POST" action="">
        @csrf
        @method('PUT')
        <input type="hidden" name="adviser_id" id="edit_adviser_id">
        <div class="form-grid">
          <div class="form-group">
            <label>First Name</label>
            <input type="text" name="adviser_fname" id="edit_adviser_fname" required>
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="adviser_lname" id="edit_adviser_lname" required>
          </div>
          <div class="form-group">
            <label>Section</label>
            <input type="text" name="adviser_section" id="edit_adviser_section" required>
          </div>
          <div class="form-group">
            <label>Grade Level</label>
            <input type="text" name="adviser_gradelevel" id="edit_adviser_gradelevel" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="adviser_email" id="edit_adviser_email">
          </div>
          <div class="form-group">
            <label>Contact Info</label>
            <input type="text" name="adviser_contactinfo" id="edit_adviser_contactinfo">
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
      <div class="modal-header">🗃️ Archived Advisers</div>
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
                <th>Section</th>
                <th>Grade Level</th>
                <th>Status</th>
                <th>Date Archived</th>
              </tr>
            </thead>
            <tbody id="archiveTableBody">
              <!-- Archived advisers will be loaded here via AJAX -->
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

// 🗑️ Move to Trash (Archive)
document.getElementById('moveToTrashBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.rowCheckbox:checked');

    if (!selectedCheckboxes.length) {
        notifications.showNotification('Please select at least one adviser.', 'warning');
        return;
    }

    const adviserIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    notifications.showConfirmation(
        `Are you sure you want to archive ${adviserIds.length} adviser(s)?`,
        async function() {
            try {
                const response = await fetch('{{ route("advisers.move-to-trash") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ adviser_ids: adviserIds })
                });

                const result = await response.json();

                if (result.success) {
                    notifications.showNotification(`${adviserIds.length} adviser(s) moved to archive.`, 'success');
                    // Remove the archived rows from the main table
                    adviserIds.forEach(id => {
                        const row = document.querySelector(`tr[data-adviser-id="${id}"]`);
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
                notifications.showNotification('Error moving advisers to archive.', 'error');
            }
        }
    );
});

// 🗃️ Archive Modal - Load archived advisers
document.getElementById('archiveBtn').addEventListener('click', async function() {
    try {
        const response = await fetch('{{ route("advisers.getArchived") }}');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const archivedAdvisers = await response.json();
        const archiveTableBody = document.getElementById('archiveTableBody');
        archiveTableBody.innerHTML = '';

        if (archivedAdvisers.length === 0) {
            archiveTableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;">⚠️ No archived advisers found</td></tr>';
        } else {
            archivedAdvisers.forEach(adviser => {
                const row = document.createElement('tr');
                row.setAttribute('data-adviser-id', adviser.adviser_id);
                row.innerHTML = `
                    <td><input type="checkbox" class="archiveCheckbox" value="${adviser.adviser_id}"></td>
                    <td>${adviser.adviser_id}</td>
                    <td>${adviser.adviser_fname}</td>
                    <td>${adviser.adviser_lname}</td>
                    <td>${adviser.adviser_section}</td>
                    <td>${adviser.adviser_gradelevel}</td>
                    <td><span class="status-badge status-inactive">${adviser.status}</span></td>
                    <td>${new Date(adviser.updated_at).toLocaleDateString()}</td>
                `;
                archiveTableBody.appendChild(row);
            });
        }

        document.getElementById('archiveModal').style.display = 'flex';
    } catch (error) {
        console.error('Error loading archived advisers:', error);
        notifications.showNotification('Error loading archived advisers.', 'error');
    }
});

// 🔄 Restore Archived Advisers
document.getElementById('restoreArchiveBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.archiveCheckbox:checked');

    if (!selectedCheckboxes.length) {
        notifications.showNotification('Please select at least one adviser to restore.', 'warning');
        return;
    }

    const adviserIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    notifications.showConfirmation(
        `Are you sure you want to restore ${adviserIds.length} adviser(s)?`,
        async function() {
            try {
                const response = await fetch('{{ route("advisers.restore") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ adviser_ids: adviserIds })
                });

                const result = await response.json();

                if (result.success) {
                    notifications.showNotification(`${adviserIds.length} adviser(s) restored successfully.`, 'success');
                    // Remove the restored rows from archive table
                    adviserIds.forEach(id => {
                        const row = document.querySelector(`#archiveTableBody tr[data-adviser-id="${id}"]`);
                        if (row) row.remove();
                    });

                    // Reload the page to show restored advisers in main table
                    location.reload();
                } else {
                    notifications.showNotification('Error: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                notifications.showNotification('Error restoring advisers.', 'error');
            }
        }
    );
});

// 🗑️ Delete Archived Advisers Permanently
document.getElementById('deleteArchiveBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.archiveCheckbox:checked');

    if (!selectedCheckboxes.length) {
        notifications.showNotification('Please select at least one adviser to delete permanently.', 'warning');
        return;
    }

    const adviserIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    notifications.showConfirmation(
        'WARNING: This will permanently delete these advisers. This action cannot be undone!',
        async function() {
            try {
                const response = await fetch('{{ route("advisers.destroyMultiple") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ adviser_ids: adviserIds })
                });

                const result = await response.json();

                if (result.success) {
                    notifications.showNotification(`${adviserIds.length} adviser(s) deleted permanently.`, 'success');
                    // Remove the deleted rows from archive table
                    adviserIds.forEach(id => {
                        const row = document.querySelector(`#archiveTableBody tr[data-adviser-id="${id}"]`);
                        if (row) row.remove();
                    });

                    // If no more archived advisers, show message
                    const remainingRows = document.querySelectorAll('#archiveTableBody tr');
                    if (remainingRows.length === 0) {
                        document.getElementById('archiveTableBody').innerHTML = '<tr><td colspan="8" style="text-align:center;">⚠️ No archived advisers found</td></tr>';
                    }
                } else {
                    notifications.showNotification('Error: ' + (result.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                notifications.showNotification('Error deleting advisers.', 'error');
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
    const editForm = document.getElementById('editAdviserForm');

    // 🎯 When "Edit" Button Clicked
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const adviserId = row.children[1].innerText.trim();
            const fname = row.children[2].innerText.trim();
            const lname = row.children[3].innerText.trim();
            const section = row.children[4].innerText.trim();
            const gradeLevel = row.children[5].innerText.trim();
            const email = row.children[6].innerText.trim();
            const contact = row.children[7].innerText.trim();

            // 📝 Fill Form
            document.getElementById('edit_adviser_id').value = adviserId;
            document.getElementById('edit_adviser_fname').value = fname;
            document.getElementById('edit_adviser_lname').value = lname;
            document.getElementById('edit_adviser_section').value = section;
            document.getElementById('edit_adviser_gradelevel').value = gradeLevel;
            document.getElementById('edit_adviser_email').value = email;
            document.getElementById('edit_adviser_contactinfo').value = contact;

            // Set form action dynamically
            editForm.action = `/prefect/advisers/update`;

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
