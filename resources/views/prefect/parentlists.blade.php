@extends('prefect.layout')

@section('content')
<div class="main-container">
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .error-message {
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.form-control.error {
    border-color: #e74c3c;
}

.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
    </style>

  <!-- Toolbar -->
  <div class="toolbar">
    <h2>Parent Management</h2>
    <div class="actions">
      <input type="search" placeholder="🔍 Search by parent name or ID..." id="searchInput">
      <a href="{{ route('create.parent') }}" class="btn-primary" id="createBtn">
        <i class="fas fa-plus"></i> Add Parent
      </a>
      <button class="btn-info" id="archiveBtn">🗃️ Archive</button>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="summary">
    <div class="card">
        <h2>{{ $totalParents }}</h2>
        <p>Total Parents</p>
    </div>
    <div class="card">
        <h2>{{ $activeParents }}</h2>
        <p>Active Parents</p>
    </div>
    <div class="card">
        <h2 id="archivedCount">{{ $archivedParents }}</h2>
        <p>Archived Parents</p>
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
        <div class="dropdown-content">
        </div>
      </div>
    </div>

    <div class="right-controls">
      <button class="btn-danger" id="moveToTrashBtn">🗑️ Move Selected to Trash</button>
    </div>
  </div>

  <!-- Table -->
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
          <th>Email</th>
          <th>Contact Info</th>
          <th>Relationship</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        @forelse($parents as $parent)
        <tr data-parent-id="{{ $parent->parent_id }}" data-details="{{ $parent->parent_fname }} {{ $parent->parent_lname }}|{{ $parent->parent_relationship ?? 'N/A' }}|{{ $parent->parent_contactinfo ?? 'N/A' }}|{{ $parent->parent_birthdate ?? 'N/A' }}|{{ $parent->parent_email ?? 'N/A' }}">
          <td><input type="checkbox" class="rowCheckbox" value="{{ $parent->parent_id }}"></td>
          <td>{{ $parent->parent_id }}</td>
          <td>{{ $parent->parent_fname }}</td>
          <td>{{ $parent->parent_lname }}</td>
          <td>{{ ucfirst($parent->parent_sex) }}</td>
          <td>{{ $parent->parent_birthdate ? \Carbon\Carbon::parse($parent->parent_birthdate)->format('F j, Y') : 'N/A' }}</td>
          <td>{{ $parent->parent_email ?? 'N/A' }}</td>
          <td>{{ $parent->parent_contactinfo }}</td>
          <td>{{ $parent->parent_relationship ?? 'N/A' }}</td>
          <td>
            <span class="status-badge {{ $parent->status === 'active' ? 'status-active' : 'status-inactive' }}">
              {{ ucfirst($parent->status) }}
            </span>
          </td>
          <td>
            <button class="btn-primary edit-btn">✏️ Edit</button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="11" style="text-align:center;">No active parents found</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination-wrapper">
      <div class="pagination-summary">
        Showing {{ $parents->firstItem() }} to {{ $parents->lastItem() }} of {{ $parents->total() }} results
      </div>
      <div class="pagination-links">
        {{ $parents->links() }}
      </div>
    </div>
  </div>

 <!-- ✏️ Edit Parent Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <button class="close-btn" id="closeEditModal">✖</button>
        <h2>Edit Parent</h2>

        <!-- Success/Error Messages -->
        <div id="editModalMessages" style="display: none;"></div>

        <form id="editParentForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="parent_id" id="edit_parent_id">

            <div class="form-grid">
                <div class="form-group">
                    <label for="edit_parent_fname">First Name *</label>
                    <input type="text" name="parent_fname" id="edit_parent_fname" class="form-control" required>
                    <span class="error-message" id="fname_error"></span>
                </div>

                <div class="form-group">
                    <label for="edit_parent_lname">Last Name *</label>
                    <input type="text" name="parent_lname" id="edit_parent_lname" class="form-control" required>
                    <span class="error-message" id="lname_error"></span>
                </div>

                <div class="form-group">
                    <label for="edit_parent_sex">Sex *</label>
                    <select name="parent_sex" id="edit_parent_sex" class="form-control" required>
                        <option value="">Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <span class="error-message" id="sex_error"></span>
                </div>

                <div class="form-group">
                    <label for="edit_parent_birthdate">Birthdate *</label>
                    <input type="date" name="parent_birthdate" id="edit_parent_birthdate" class="form-control" required>
                    <span class="error-message" id="birthdate_error"></span>
                </div>

                <div class="form-group">
                    <label for="edit_parent_email">Email</label>
                    <input type="email" name="parent_email" id="edit_parent_email" class="form-control">
                    <span class="error-message" id="email_error"></span>
                </div>

                <div class="form-group">
                    <label for="edit_parent_contactinfo">Contact Info *</label>
                    <input type="text" name="parent_contactinfo" id="edit_parent_contactinfo" class="form-control" required>
                    <span class="error-message" id="contactinfo_error"></span>
                </div>

                <div class="form-group">
                    <label for="edit_parent_relationship">Relationship *</label>
                    <select name="parent_relationship" id="edit_parent_relationship" class="form-control" required>
                        <option value="">Select Relationship</option>
                        <option value="father">Father</option>
                        <option value="mother">Mother</option>
                        <option value="guardian">Guardian</option>
                        <option value="grandparent">Grandparent</option>
                        <option value="other">Other</option>
                    </select>
                    <span class="error-message" id="relationship_error"></span>
                </div>

                <div class="form-group">
                    <label for="edit_parent_status">Status *</label>
                    <select name="status" id="edit_parent_status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <span class="error-message" id="status_error"></span>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary" id="saveEditBtn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button type="button" class="btn-secondary" id="cancelEditBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

  <!-- 🗃️ Archive Modal -->
  <div class="modal" id="archiveModal">
    <div class="modal-content">
      <div class="modal-header">
        🗃️ Archived Parents
      </div>
      <div class="modal-body">
        <div class="modal-actions">
          <label class="select-all-label">
            <input type="checkbox" id="selectAllArchived" class="select-all-checkbox">
            <span>Select All</span>
          </label>

          <div class="search-container">
            <input type="search" placeholder="🔍 Search archived..." id="archiveSearch" class="search-input">
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
                <th>Birthdate</th>
                <th>Email</th>
                <th>Contact Info</th>
                <th>Relationship</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="archiveTableBody">
              <!-- Archived parents will be loaded here via AJAX -->
            </tbody>
          </table>
        </div>

        <div class="modal-note">
          ⚠️ Note: Deleting records will permanently remove them.
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
// Search filter for main table
// ==========================
document.getElementById('searchInput').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#tableBody tr');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// ==========================
// Select all checkboxes
// ==========================
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
});

// ==========================
// Move to Archive (Trash)
// ==========================
document.getElementById('moveToTrashBtn').addEventListener('click', function() {
    const selected = Array.from(document.querySelectorAll('.rowCheckbox:checked'))
        .map(cb => cb.value);

    if (!selected.length) {
        alert('Please select at least one parent.');
        return;
    }

    if (confirm(`Are you sure you want to move ${selected.length} parent(s) to archive?`)) {
        fetch('{{ route("parents.archive") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ parent_ids: selected })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Reload to update the table and counts
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while archiving parents.');
        });
    }
});

// ==========================
// Load Archived Parents
// ==========================
function loadArchivedParents() {
    fetch('{{ route("parents.archived") }}')
        .then(response => response.json())
        .then(parents => {
            const archiveTableBody = document.getElementById('archiveTableBody');
            archiveTableBody.innerHTML = '';

            if (parents.length === 0) {
                archiveTableBody.innerHTML = `
                <tr>
                    <td colspan="10" style="text-align:center; padding:15px;">No archived parents found</td>
                </tr>
                `;
                return;
            }

            parents.forEach(parent => {
                const row = document.createElement('tr');
                row.innerHTML = `
                <td><input type="checkbox" class="archiveCheckbox" value="${parent.parent_id}"></td>
                <td>${parent.parent_id}</td>
                <td>${parent.parent_fname}</td>
                <td>${parent.parent_lname}</td>
                <td>${parent.parent_sex}</td>
                <td>${parent.parent_birthdate ? new Date(parent.parent_birthdate).toLocaleDateString() : 'N/A'}</td>
                <td>${parent.parent_email || 'N/A'}</td>
                <td>${parent.parent_contactinfo}</td>
                <td>${parent.parent_relationship || 'N/A'}</td>
                <td><span class="status-badge status-inactive">Inactive</span></td>
                `;
                archiveTableBody.appendChild(row);
            });

            // Update select all functionality for archived items
            updateArchiveSelectAll();
        })
        .catch(error => {
            console.error('Error loading archived parents:', error);
        });
}

// ==========================
// Update Archive Select All
// ==========================
function updateArchiveSelectAll() {
    const selectAllArchived = document.getElementById('selectAllArchived');
    const archivedCheckboxes = document.querySelectorAll('.archiveCheckbox');

    selectAllArchived.addEventListener('change', function() {
        archivedCheckboxes.forEach(cb => cb.checked = this.checked);
    });

    // Update select all when individual checkboxes change
    archivedCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(archivedCheckboxes).every(cb => cb.checked);
            selectAllArchived.checked = allChecked;
        });
    });
}

// ==========================
// Restore Archived Parents
// ==========================
document.getElementById('restoreArchiveBtn').addEventListener('click', function() {
    const selected = Array.from(document.querySelectorAll('.archiveCheckbox:checked'))
        .map(cb => cb.value);

    if (!selected.length) {
        alert('Please select at least one parent to restore.');
        return;
    }

    if (confirm(`Are you sure you want to restore ${selected.length} parent(s)?`)) {
        fetch('{{ route("parents.restore") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ parent_ids: selected })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadArchivedParents(); // Reload the archived list
                location.reload(); // Reload main page to update counts
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while restoring parents.');
        });
    }
});

// ==========================
// Delete Archived Parents Permanently
// ==========================
document.getElementById('deleteArchiveBtn').addEventListener('click', function() {
    const selected = Array.from(document.querySelectorAll('.archiveCheckbox:checked'))
        .map(cb => cb.value);

    if (!selected.length) {
        alert('Please select at least one parent to delete permanently.');
        return;
    }

    if (confirm(`WARNING: This will permanently delete ${selected.length} parent(s). This action cannot be undone!`)) {
        fetch('{{ route("parents.destroy.permanent") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ parent_ids: selected })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadArchivedParents(); // Reload the archived list
                // Update archived count
                fetch('{{ route("parents.archived.count") }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('archivedCount').innerText = data.count;
                });
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting parents.');
        });
    }
});

// ==========================
// Open Archive Modal
// ==========================
document.getElementById('archiveBtn').addEventListener('click', function() {
    loadArchivedParents();
    document.getElementById('archiveModal').style.display = 'flex';
});

// ==========================
// Close Archive Modal
// ==========================
document.getElementById('closeArchive').addEventListener('click', function() {
    document.getElementById('archiveModal').style.display = 'none';
});

// ==========================
// Archive Search
// ==========================
document.getElementById('archiveSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#archiveTableBody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// ==========================
// Edit Modal Functionality
// ==========================
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-btn');
    const editModal = document.getElementById('editModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const editForm = document.getElementById('editParentForm');
    const saveEditBtn = document.getElementById('saveEditBtn');

    // Helper functions
    function clearEditErrors() {
        document.querySelectorAll('.error-message').forEach(el => {
            el.textContent = '';
        });
    }

    function showEditMessage(message, type) {
        const messageDiv = document.getElementById('editModalMessages');
        messageDiv.textContent = message;
        messageDiv.className = `alert alert-${type}`;
        messageDiv.style.display = 'block';
    }

    function hideEditMessage() {
        document.getElementById('editModalMessages').style.display = 'none';
    }

    function closeEditModalFunc() {
        editModal.style.display = 'none';
        clearEditErrors();
        hideEditMessage();
    }

    // Open edit modal
    editButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const row = this.closest('tr');
            const parentId = row.getAttribute('data-parent-id');

            // Get current values from table cells
            const fname = row.children[2].innerText.trim();
            const lname = row.children[3].innerText.trim();
            const sex = row.children[4].innerText.trim();
            const birthdate = row.children[5].innerText.trim();
            const email = row.children[6].innerText.trim();
            const contact = row.children[7].innerText.trim();
            const relationship = row.children[8].innerText.trim();
            const status = row.children[9].querySelector('.status-badge').innerText.trim();

            // Convert birthdate to YYYY-MM-DD format
            let birthdateInput = '';
            if (birthdate !== 'N/A') {
                const date = new Date(birthdate);
                birthdateInput = date.toISOString().split('T')[0];
            }

            // Clear previous errors
            clearEditErrors();
            hideEditMessage();

            // Fill form with current data
            document.getElementById('edit_parent_id').value = parentId;
            document.getElementById('edit_parent_fname').value = fname;
            document.getElementById('edit_parent_lname').value = lname;
            document.getElementById('edit_parent_sex').value = sex;
            document.getElementById('edit_parent_birthdate').value = birthdateInput;
            document.getElementById('edit_parent_email').value = email === 'N/A' ? '' : email;
            document.getElementById('edit_parent_contactinfo').value = contact;
            document.getElementById('edit_parent_relationship').value = relationship === 'N/A' ? '' : relationship.toLowerCase();
            document.getElementById('edit_parent_status').value = status.toLowerCase();

            // Set form action with correct route
            editForm.action = `/prefect/parents/update/${parentId}`;

            // Show modal
            editModal.style.display = 'flex';
        });
    });

    // Handle form submission with AJAX
  // Temporary debug version
editForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const parentId = document.getElementById('edit_parent_id').value;

    // Show loading state
    saveEditBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveEditBtn.disabled = true;

    console.log('Submitting to:', this.action);
    console.log('Form data:', Object.fromEntries(formData));

    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(formData).toString()
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        // Read the response as text first to see what we're getting
        return response.text().then(text => {
            console.log('Raw response:', text);

            try {
                // Try to parse as JSON
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                throw new Error('Server returned: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log('Parsed data:', data);
        if (data.success) {
            showEditMessage(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Update failed');
        }
    })
    .catch(error => {
        console.error('Full error:', error);
        showEditMessage('Error: ' + error.message, 'error');
    })
    .finally(() => {
        saveEditBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
        saveEditBtn.disabled = false;
    });
});

    // Close modal functions
    closeEditModal.addEventListener('click', closeEditModalFunc);
    cancelEditBtn.addEventListener('click', closeEditModalFunc);

    // Close modal when clicking outside
    editModal.addEventListener('click', function(e) {
        if (e.target === editModal) {
            closeEditModalFunc();
        }
    });
});

// ==========================
// Load archived count on page load
// ==========================
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route("parents.archived.count") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('archivedCount').innerText = data.count;
        })
        .catch(error => {
            console.error('Error loading archived count:', error);
        });
});
</script>

@endsection
