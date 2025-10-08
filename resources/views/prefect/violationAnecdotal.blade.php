@extends('prefect.layout')

@section('content')
<div class="main-container">
<meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- ✅ Toolbar -->
  <div class="toolbar">
    <h2>Anecdotal Management</h2>
    <div class="actions">
      <input type="search" placeholder="🔍 Search by student name or ID..." id="searchInput">
      <a href="{{ route('violation-anecdotal.create') }}" class="btn-primary" id="createAnecBtn">
        <i class="fas fa-plus"></i>📝 Create Anecdotal
      </a>
      <button class="btn-info" id="archiveBtn">🗃️ Archive</button>
    </div>
  </div>

  <!-- ✅ Summary Cards -->
  <div class="summary">
    <div class="card">
      <h2>{{ $monthlyAnecdotals ?? '0' }}</h2>
      <p>This Month</p>
    </div>
    <div class="card">
      <h2>{{ $weeklyAnecdotals ?? '0' }}</h2>
      <p>This Week</p>
    </div>
    <div class="card">
      <h2>{{ $dailyAnecdotals ?? '0' }}</h2>
      <p>Today</p>
    </div>
  </div>

  <!-- ✅ Bulk Action / Dropdown -->
  <div class="select-options">
    <div class="left-controls">
      <label for="selectAll" class="select-label">
        <input type="checkbox" id="selectAll">
        <span>Select All</span>
      </label>
    </div>

    <div class="right-controls">
      <!-- Violation Anecdotals Buttons -->
      <div id="violationAnecdotalsActions" class="action-buttons">
        <button class="btn-cleared" id="markAnecdotalCompletedBtn">Mark as Completed</button>
        <button class="btn-danger" id="moveAnecdotalToTrashBtn">🗑️ Move Selected to Trash</button>
      </div>
    </div>
  </div>

  <div class="table-container">
    <!-- 📝 VIOLATION ANECDOTALS TABLE -->
    <div id="violationAnecdotalsTable" class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th></th>
            <th>ID</th>
            <th>Student Name</th>
            <th>Solution</th>
            <th>Recommendation</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($vanecdotals as $anec)
            @if($anec->status === 'active' || $anec->status === 'in_progress')
            <tr
              data-anec-id="{{ $anec->violation_anec_id }}"
              data-solution="{{ $anec->violation_anec_solution }}"
              data-recommendation="{{ $anec->violation_anec_recommendation }}"
              data-date="{{ $anec->violation_anec_date }}"
              data-time="{{ \Carbon\Carbon::parse($anec->violation_anec_time)->format('h:i A') }}"
              data-status="{{ $anec->status }}"
            >
              <td><input type="checkbox" class="rowCheckbox anecdotalCheckbox" value="{{ $anec->violation_anec_id }}"></td>
              <td>{{ $anec->violation_anec_id }}</td>
              <td>
                {{ $anec->violation->student->student_fname ?? 'N/A' }}
                {{ $anec->violation->student->student_lname ?? '' }}
              </td>
              <td>{{ $anec->violation_anec_solution }}</td>
              <td>{{ $anec->violation_anec_recommendation }}</td>
              <td>{{ $anec->violation_anec_date }}</td>
              <td>{{ \Carbon\Carbon::parse($anec->violation_anec_time)->format('h:i A') }}</td>
              <td>
                <span class="status-badge {{ $anec->status === 'active' ? 'status-active' : 'status-in-progress' }}">
                  {{ ucfirst($anec->status) }}
                </span>
              </td>
              <td><button class="btn-primary editAnecdotalBtn">✏️ Edit</button></td>
            </tr>
            @endif
          @empty
          <tr><td colspan="9" style="text-align:center;">No active anecdotal records found</td></tr>
          @endforelse
        </tbody>
      </table>

      <div class="pagination-wrapper">
        <div class="pagination-summary">
          @if($vanecdotals instanceof \Illuminate\Pagination\LengthAwarePaginator)
            @php
              $activeCount = $vanecdotals->whereIn('status', ['active', 'in_progress'])->count();
            @endphp
            Showing {{ $activeCount > 0 ? '1' : '0' }} to {{ $activeCount }} of {{ $activeCount }} record(s)
          @endif
        </div>
        <div class="pagination-links">
          {{ $vanecdotals->links() }}
        </div>
      </div>
    </div>
  </div>

  <!-- 👁️ Anecdotal Details Modal -->
  <div class="modal" id="anecdotalDetailsModal">
    <div class="modal-content">
      <button class="close-btn" id="closeAnecdotalDetailsModal">✖</button>
      <h2>Anecdotal Details</h2>

      <div class="anecdotal-details-container">
        <div class="detail-section">
          <h3>Student Information</h3>
          <div class="detail-grid">
            <div class="detail-item">
              <label>Student Name:</label>
              <span id="detail-anecdotal-student-name">-</span>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h3>Anecdotal Information</h3>
          <div class="detail-grid">
            <div class="detail-item">
              <label>Anecdotal ID:</label>
              <span id="detail-anecdotal-id">-</span>
            </div>
            <div class="detail-item">
              <label>Solution:</label>
              <span id="detail-solution">-</span>
            </div>
            <div class="detail-item">
              <label>Recommendation:</label>
              <span id="detail-recommendation">-</span>
            </div>
            <div class="detail-item">
              <label>Date:</label>
              <span id="detail-anecdotal-date">-</span>
            </div>
            <div class="detail-item">
              <label>Time:</label>
              <span id="detail-anecdotal-time">-</span>
            </div>
            <div class="detail-item">
              <label>Status:</label>
              <span id="detail-anecdotal-status" class="status-badge">-</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn-primary" id="viewRelatedViolationBtn">📋 VIEW RELATED VIOLATION</button>
      </div>
    </div>
  </div>

  <!-- ✏️ Edit Anecdotal Modal -->
  <div class="modal" id="editAnecdotalModal">
    <div class="modal-content">
      <button class="close-btn" id="closeAnecdotalEditModal">✖</button>
      <h2>Edit Anecdotal Record</h2>

      <form id="editAnecdotalForm" method="POST" action="">
        @csrf
        @method('PUT')

        <input type="hidden" name="record_id" id="edit_anecdotal_record_id">

        <div class="form-grid">
          <div class="form-group">
            <label>Solution</label>
            <textarea id="edit_solution" name="solution" required></textarea>
          </div>
          <div class="form-group">
            <label>Recommendation</label>
            <textarea id="edit_recommendation" name="recommendation" required></textarea>
          </div>
          <div class="form-group">
            <label>Date</label>
            <input type="date" id="edit_anecdotal_date" name="date" required>
          </div>
          <div class="form-group">
            <label>Time</label>
            <input type="time" id="edit_anecdotal_time" name="time" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select id="edit_anecdotal_status" name="status" required>
              <option value="active">Active</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn-primary">💾 Save Changes</button>
          <button type="button" class="btn-secondary" id="cancelAnecdotalEditBtn">❌ Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 🗃️ VIOLATION ANECDOTALS ARCHIVE MODAL -->
  <div class="modal" id="violationAnecdotalsArchiveModal">
    <div class="modal-content">
      <div class="modal-header">🗃️ Archived Violation Anecdotals</div>
      <div class="modal-body">
        <div class="modal-actions">
          <label class="select-all-label">
            <input type="checkbox" id="selectAllViolationAnecdotalsArchived">
            <span>Select All</span>
          </label>
          <div class="filter-container">
            <select id="violationAnecdotalsStatusFilter" class="filter-select">
              <option value="all">All Status</option>
              <option value="completed">Completed</option>
              <option value="closed">Closed</option>
            </select>
          </div>
          <div class="search-container">
            <input type="search" id="violationAnecdotalsArchiveSearch" placeholder="🔍 Search archived anecdotals..." class="search-input">
          </div>
        </div>

        <div class="archive-table-container">
          <div id="archiveViolationAnecdotalsTable" class="archive-table-wrapper">
            <table class="archive-table">
              <thead>
                <tr>
                  <th>✔</th>
                  <th>ID</th>
                  <th>Student Name</th>
                  <th>Solution</th>
                  <th>Recommendation</th>
                  <th>Status</th>
                  <th>Date Archived</th>
                </tr>
              </thead>
              <tbody id="archiveViolationAnecdotalsBody">
                <!-- Archived violation anecdotals will be loaded here via AJAX -->
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secondary" id="restoreViolationAnecdotalsBtn">🔄 Restore</button>
          <button class="btn-danger" id="deleteViolationAnecdotalsBtn">🗑️ Delete</button>
          <button class="btn-close" id="closeViolationAnecdotalsArchive">❌ Close</button>
        </div>
      </div>
    </div>
  </div>

</div>

<style>
.clickable-row {
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.clickable-row:hover {
  background-color: #f5f5f5;
}

.anecdotal-details-container {
  margin: 20px 0;
}

.detail-section {
  margin-bottom: 25px;
  padding: 15px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #fafafa;
}

.detail-section h3 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #333;
  border-bottom: 2px solid #007bff;
  padding-bottom: 8px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 15px;
}

.detail-item {
  display: flex;
  flex-direction: column;
}

.detail-item label {
  font-weight: bold;
  color: #555;
  margin-bottom: 5px;
  font-size: 0.9em;
}

.detail-item span {
  color: #333;
  padding: 8px 12px;
  background-color: white;
  border-radius: 4px;
  border: 1px solid #ddd;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e0e0e0;
}

.status-in-progress {
  background-color: #ffc107;
  color: #212529;
}

.status-completed {
  background-color: #28a745;
  color: white;
}

.status-closed {
  background-color: #6c757d;
  color: white;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: bold;
  margin-bottom: 5px;
  color: #333;
}

.form-group textarea,
.form-group input,
.form-group select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-group textarea {
  min-height: 80px;
  resize: vertical;
}

.actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}
</style>

<script>
// Get CSRF Token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

const csrfToken = getCsrfToken();

// Current active table type
let currentTableType = 'violationAnecdotals';

// 👁️ Anecdotal Details Modal Functionality
const anecdotalDetailsModal = document.getElementById('anecdotalDetailsModal');
const closeAnecdotalDetailsModal = document.getElementById('closeAnecdotalDetailsModal');
const viewRelatedViolationBtn = document.getElementById('viewRelatedViolationBtn');

// Function to open anecdotal details modal
function openAnecdotalDetailsModal(anecdotalData) {
    // Populate the modal with anecdotal data
    document.getElementById('detail-anecdotal-student-name').textContent = anecdotalData.studentName || '-';
    document.getElementById('detail-anecdotal-id').textContent = anecdotalData.anecdotalId || '-';
    document.getElementById('detail-solution').textContent = anecdotalData.solution || '-';
    document.getElementById('detail-recommendation').textContent = anecdotalData.recommendation || '-';
    document.getElementById('detail-anecdotal-date').textContent = anecdotalData.date || '-';
    document.getElementById('detail-anecdotal-time').textContent = anecdotalData.time || '-';

    // Set status with appropriate badge
    const statusElement = document.getElementById('detail-anecdotal-status');
    statusElement.textContent = anecdotalData.status ? anecdotalData.status.charAt(0).toUpperCase() + anecdotalData.status.slice(1) : '-';
    statusElement.className = 'status-badge ' +
        (anecdotalData.status === 'active' ? 'status-active' :
         anecdotalData.status === 'in_progress' ? 'status-in-progress' :
         anecdotalData.status === 'completed' ? 'status-completed' : 'status-closed');

    // Show the modal
    anecdotalDetailsModal.style.display = 'flex';
}

// Add click event listeners to anecdotal rows
document.addEventListener('DOMContentLoaded', function() {
    const anecdotalRows = document.querySelectorAll('#violationAnecdotalsTable tbody tr');

    anecdotalRows.forEach(row => {
        if (!row.classList.contains('no-data-row')) {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on checkbox or edit button
                if (e.target.type === 'checkbox' || e.target.classList.contains('editAnecdotalBtn')) {
                    return;
                }

                const anecdotalData = {
                    studentName: this.cells[2].textContent.trim(),
                    anecdotalId: this.dataset.anecId,
                    solution: this.dataset.solution,
                    recommendation: this.dataset.recommendation,
                    date: this.dataset.date,
                    time: this.dataset.time,
                    status: this.dataset.status
                };

                openAnecdotalDetailsModal(anecdotalData);
            });
        }
    });
});

// Close anecdotal details modal
closeAnecdotalDetailsModal.addEventListener('click', function() {
    anecdotalDetailsModal.style.display = 'none';
});

// View Related Violation button functionality
viewRelatedViolationBtn.addEventListener('click', function() {
    const anecdotalId = document.getElementById('detail-anecdotal-id').textContent;
    const studentName = document.getElementById('detail-anecdotal-student-name').textContent;

    alert(`Viewing related violation for anecdotal ${anecdotalId} - ${studentName}`);
    // Here you would typically navigate to violations page or load related violation
    // Example: window.location.href = `/violations?anecdotal_id=${anecdotalId}`;
});

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modals = [
        'anecdotalDetailsModal',
        'editAnecdotalModal',
        'violationAnecdotalsArchiveModal'
    ];

    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// 🔍 Search Functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const tableBody = document.querySelector('#violationAnecdotalsTable tbody');
    const rows = tableBody.querySelectorAll('tr');

    rows.forEach(row => {
        if (!row.classList.contains('no-data-row')) {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        }
    });
});

// ✅ Select All - Main Table
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.anecdotalCheckbox');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
    });
});

// ==================== VIOLATION ANECDOTALS FUNCTIONALITY ====================
// ✅ Mark Anecdotal as Completed
document.getElementById('markAnecdotalCompletedBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.anecdotalCheckbox:checked');

    if (!selectedCheckboxes.length) {
        alert('Please select at least one anecdotal record.');
        return;
    }

    const anecdotalIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    if (!confirm(`Are you sure you want to mark ${anecdotalIds.length} anecdotal record(s) as Completed?`)) {
        return;
    }

    try {
        const response = await fetch('/prefect/violation-anecdotals/archive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                anecdotal_ids: anecdotalIds,
                status: 'completed'
            })
        });

        const result = await response.json();

        if (result.success) {
            alert(`${anecdotalIds.length} anecdotal record(s) marked as Completed and moved to archive.`);
            // Remove the completed rows from the main table
            anecdotalIds.forEach(id => {
                const row = document.querySelector(`tr[data-anec-id="${id}"]`);
                if (row) row.remove();
            });

            // Update UI
            document.getElementById('selectAll').checked = false;

            // Reload to update counts
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error marking anecdotal records as completed.');
    }
});

// 🗑️ Move Anecdotal to Trash (Archive as Closed)
document.getElementById('moveAnecdotalToTrashBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.anecdotalCheckbox:checked');

    if (!selectedCheckboxes.length) {
        alert('Please select at least one anecdotal record.');
        return;
    }

    const anecdotalIds = Array.from(selectedCheckboxes).map(cb => cb.value);

    if (!confirm(`Are you sure you want to move ${anecdotalIds.length} anecdotal record(s) to archive as Closed?`)) {
        return;
    }

    try {
        const response = await fetch('/prefect/violation-anecdotals/archive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                anecdotal_ids: anecdotalIds,
                status: 'closed'
            })
        });

        const result = await response.json();

        if (result.success) {
            alert(`${anecdotalIds.length} anecdotal record(s) moved to archive as Closed.`);
            // Remove the archived rows from the main table
            anecdotalIds.forEach(id => {
                const row = document.querySelector(`tr[data-anec-id="${id}"]`);
                if (row) row.remove();
            });

            // Update UI
            document.getElementById('selectAll').checked = false;

            // Reload to update counts
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error moving anecdotal records to archive.');
    }
});

// ==================== ARCHIVE MODAL FUNCTIONALITY ====================
// 🗃️ Archive Button - Opens anecdotal archive modal
document.getElementById('archiveBtn').addEventListener('click', async function() {
    try {
        console.log('Loading archived anecdotal data');

        // Load archived anecdotals
        const anecdotalResponse = await fetch('/prefect/violation-anecdotals/archived');
        console.log('Anecdotal response status:', anecdotalResponse.status);
        const archivedAnecdotals = await anecdotalResponse.json();
        console.log('Archived anecdotals:', archivedAnecdotals);

        // Populate anecdotals table
        populateArchiveTable('archiveViolationAnecdotalsBody', archivedAnecdotals, 'anecdotal');
        document.getElementById('violationAnecdotalsArchiveModal').style.display = 'flex';

    } catch (error) {
        console.error('Error loading archived data:', error);
        alert('Error loading archived data. Check console for details.');
    }
});

// Function to populate archive tables
function populateArchiveTable(tableBodyId, data, type) {
    const tableBody = document.getElementById(tableBodyId);
    tableBody.innerHTML = '';

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center;">⚠️ No archived ${type} records found</td></tr>`;
        return;
    }

    data.forEach(item => {
        const row = document.createElement('tr');

        if (type === 'anecdotal') {
            row.setAttribute('data-record-id', item.violation_anec_id);
            row.setAttribute('data-record-type', 'anecdotal');
            row.innerHTML = `
                <td><input type="checkbox" class="archiveCheckbox" value="${item.violation_anec_id}" data-type="anecdotal"></td>
                <td>${item.violation_anec_id}</td>
                <td>${item.student_fname} ${item.student_lname}</td>
                <td>${item.violation_anec_solution}</td>
                <td>${item.violation_anec_recommendation}</td>
                <td><span class="status-badge ${item.status === 'completed' ? 'status-completed' : 'status-closed'}">${item.status}</span></td>
                <td>${new Date(item.updated_at).toLocaleDateString()}</td>
            `;
        }

        tableBody.appendChild(row);
    });
}

// Archive Search
document.getElementById('violationAnecdotalsArchiveSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const tableBody = document.getElementById('archiveViolationAnecdotalsBody');
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Archive Status Filter
document.getElementById('violationAnecdotalsStatusFilter').addEventListener('change', function() {
    const filter = this.value;
    const tableBody = document.getElementById('archiveViolationAnecdotalsBody');
    const rows = tableBody.querySelectorAll('tr');

    if (filter !== 'all') {
        rows.forEach(row => {
            const status = row.querySelector('.status-badge').innerText.toLowerCase();
            row.style.display = status === filter.toLowerCase() ? '' : 'none';
        });
    } else {
        rows.forEach(row => row.style.display = '');
    }
});

// Select All for archive modal
document.getElementById('selectAllViolationAnecdotalsArchived').addEventListener('change', function() {
    const tableBody = document.getElementById('archiveViolationAnecdotalsBody');
    const checkboxes = tableBody.querySelectorAll('.archiveCheckbox');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
    });
});

// 🔄 Restore Archived Anecdotals
document.getElementById('restoreViolationAnecdotalsBtn').addEventListener('click', async function() {
    const tableBody = document.getElementById('archiveViolationAnecdotalsBody');
    const selectedCheckboxes = tableBody.querySelectorAll('.archiveCheckbox:checked');

    if (!selectedCheckboxes.length) {
        alert('Please select at least one record to restore.');
        return;
    }

    const records = Array.from(selectedCheckboxes).map(cb => ({
        id: cb.value,
        type: cb.dataset.type
    }));

    if (!confirm(`Are you sure you want to restore ${records.length} record(s)?`)) {
        return;
    }

    try {
        const response = await fetch('/prefect/violations/restore-multiple', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ records: records })
        });

        const result = await response.json();

        if (result.success) {
            alert(`${records.length} record(s) restored successfully.`);
            // Remove the restored rows from archive table
            records.forEach(record => {
                const row = document.querySelector(`tr[data-record-id="${record.id}"][data-record-type="${record.type}"]`);
                if (row) row.remove();
            });

            // Reload the page to show restored records in main table
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error restoring records.');
    }
});

// 🗑️ Delete Archived Anecdotals Permanently
document.getElementById('deleteViolationAnecdotalsBtn').addEventListener('click', async function() {
    const tableBody = document.getElementById('archiveViolationAnecdotalsBody');
    const selectedCheckboxes = tableBody.querySelectorAll('.archiveCheckbox:checked');

    if (!selectedCheckboxes.length) {
        alert('Please select at least one record to delete permanently.');
        return;
    }

    if (!confirm('WARNING: This will permanently delete these records. This action cannot be undone!')) {
        return;
    }

    const records = Array.from(selectedCheckboxes).map(cb => ({
        id: cb.value,
        type: cb.dataset.type
    }));

    try {
        const response = await fetch('/prefect/violations/destroy-multiple-archived', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ records: records })
        });

        const result = await response.json();

        if (result.success) {
            alert(`${records.length} record(s) deleted permanently.`);
            // Remove the deleted rows from archive table
            records.forEach(record => {
                const row = document.querySelector(`tr[data-record-id="${record.id}"][data-record-type="${record.type}"]`);
                if (row) row.remove();
            });

            // If no more archived records in current table, show message
            const remainingRows = tableBody.querySelectorAll('tr');
            if (remainingRows.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;">⚠️ No archived records found</td></tr>';
            }
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting records.');
    }
});

// Close Archive Modal
document.getElementById('closeViolationAnecdotalsArchive').addEventListener('click', function() {
    document.getElementById('violationAnecdotalsArchiveModal').style.display = 'none';
});

// ==================== EDIT ANECDOTAL MODAL FUNCTIONALITY ====================
document.addEventListener('DOMContentLoaded', () => {
  const editModal = document.getElementById('editAnecdotalModal');
  const editForm = document.getElementById('editAnecdotalForm');
  const closeModal = document.getElementById('closeAnecdotalEditModal');
  const cancelBtn = document.getElementById('cancelAnecdotalEditBtn');

  function openEditModal(action, data) {
    editForm.action = action;
    document.getElementById('edit_anecdotal_record_id').value = data.id || '';
    document.getElementById('edit_solution').value = data.solution || '';
    document.getElementById('edit_recommendation').value = data.recommendation || '';
    document.getElementById('edit_anecdotal_date').value = data.date || '';
    document.getElementById('edit_anecdotal_time').value = convertTo24Hour(data.time || '');
    document.getElementById('edit_anecdotal_status').value = data.status || 'active';
    editModal.style.display = 'flex';
  }

  function convertTo24Hour(timeStr) {
    if (!timeStr.includes(' ')) return timeStr;
    const [time, mod] = timeStr.split(' ');
    let [h, m] = time.split(':');
    h = parseInt(h);
    if (mod === 'PM' && h !== 12) h += 12;
    if (mod === 'AM' && h === 12) h = 0;
    return `${h.toString().padStart(2, '0')}:${m}`;
  }

  // Edit Anecdotal Button functionality
  document.querySelectorAll('.editAnecdotalBtn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation(); // Prevent triggering the row click event
      const row = e.target.closest('tr');
      openEditModal(`/prefect/violation-anecdotals/update/${row.dataset.anecId}`, {
        id: row.dataset.anecId,
        solution: row.dataset.solution,
        recommendation: row.dataset.recommendation,
        date: row.dataset.date,
        time: row.dataset.time,
        status: row.dataset.status
      });
    });
  });

  // Close modal events
  [closeModal, cancelBtn].forEach(btn => btn.addEventListener('click', () => editModal.style.display = 'none'));
});
</script>
@endsection
