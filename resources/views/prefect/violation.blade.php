@extends('prefect.layout')

@section('content')
<div class="main-container">
<meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- ✅ Toolbar -->
  <div class="toolbar">
    <h2>Violation Management</h2>
    <div class="actions">
      <input type="search" placeholder="🔍 Search by student name or ID..." id="searchInput">
      <a href="{{ route('violations.create') }}" class="btn-primary" id="createBtn">
        <i class="fas fa-plus"></i> Add Violation
      </a>
      <a href="{{ route('violation-anecdotal.create') }}" class="btn-secondary" id="createAnecBtn">
        <i class="fas fa-plus"></i>📝 Create Anecdotal
      </a>
      <button class="btn-info" id="archiveBtn">🗃️ Archive</button>
    </div>
  </div>

  <!-- ✅ Summary Cards -->
  <div class="summary">
    <div class="card">
      <h2>{{ $monthlyViolations }}</h2>
      <p>This Month</p>
    </div>
    <div class="card">
      <h2>{{ $weeklyViolations }}</h2>
      <p>This Week</p>
    </div>
    <div class="card">
      <h2>{{ $dailyViolations }}</h2>
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

      <div class="dropdown">
        <button class="btn-info dropdown-btn">⬇️ View Records</button>
        <div class="dropdown-content">
          <a href="#" id="violationRecords">Violation Records</a>
          <a href="#" id="violationAppointments">Violation Appointments</a>
          <a href="#" id="violationAnecdotals">Violation Anecdotals</a>
        </div>
      </div>
    </div>

    <div class="right-controls">
      <!-- Violation Records Buttons -->
      <div id="violationRecordsActions" class="action-buttons">
        <button class="btn-cleared" id="markAsClearedBtn">Cleared</button>
        <button class="btn-danger" id="moveToTrashBtn">🗑️ Move Selected to Trash</button>
      </div>

      <!-- Violation Appointments Buttons -->
      <div id="violationAppointmentsActions" class="action-buttons" style="display:none;">
        <button class="btn-cleared" id="markAppointmentCompletedBtn">Mark as Completed</button>
        <button class="btn-danger" id="moveAppointmentToTrashBtn">🗑️ Move Selected to Trash</button>
      </div>

      <!-- Violation Anecdotals Buttons -->
      <div id="violationAnecdotalsActions" class="action-buttons" style="display:none;">
        <button class="btn-cleared" id="markAnecdotalCompletedBtn">Mark as Completed</button>
        <button class="btn-danger" id="moveAnecdotalToTrashBtn">🗑️ Move Selected to Trash</button>
      </div>
    </div>
  </div>

  <div class="table-container">

    <!-- 📋 VIOLATION RECORDS TABLE -->
    <div id="violationRecordsTable" class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th></th>
            <th>ID</th>
            <th>Student Name</th>
            <th>Incident</th>
            <th>Offense Type</th>
            <th>Sanction</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          @forelse($violations as $violation)
            @if($violation->status === 'active')
            <tr
              data-violation-id="{{ $violation->violation_id }}"
              data-student-id="{{ $violation->student->student_id }}"
              data-student-name="{{ $violation->student->student_fname }} {{ $violation->student->student_lname }}"
              data-offense-id="{{ $violation->offense->offense_sanc_id }}"
              data-offense-type="{{ $violation->offense->offense_type }}"
              data-sanction="{{ $violation->offense->sanction_consequences }}"
              data-incident="{{ $violation->violation_incident }}"
              data-date="{{ $violation->violation_date }}"
              data-status="{{ $violation->status }}"
              data-time="{{ \Carbon\Carbon::parse($violation->violation_time)->format('h:i A') }}"
            >
              <td><input type="checkbox" class="rowCheckbox violationCheckbox" value="{{ $violation->violation_id }}"></td>
              <td>{{ $violation->violation_id }}</td>
              <td>{{ $violation->student->student_fname }} {{ $violation->student->student_lname }}</td>
              <td>{{ $violation->violation_incident }}</td>
              <td>{{ $violation->offense->offense_type }}</td>
              <td>{{ $violation->offense->sanction_consequences }}</td>
              <td>{{ $violation->violation_date }}</td>
              <td>{{ \Carbon\Carbon::parse($violation->violation_time)->format('h:i A') }}</td>
              <td>
                <span class="status-badge status-active">
                  Active
                </span>
              </td>
              <td><button class="btn-primary editViolationBtn">✏️ Edit</button></td>
              
            </tr>
            @endif
          @empty
          <tr class="no-data-row">
            <td colspan="10" style="text-align:center;">No active violations found</td>
          </tr>
          @endforelse
        </tbody>
      </table>

      <div class="pagination-wrapper">
        <div class="pagination-summary">
          @if($violations instanceof \Illuminate\Pagination\LengthAwarePaginator)
            @php
              $activeCount = $violations->where('status', 'active')->count();
            @endphp
            Showing {{ $activeCount > 0 ? '1' : '0' }} to {{ $activeCount }} of {{ $activeCount }} record(s)
          @endif
        </div>
        <div class="pagination-links">
          {{ $violations->links() }}
        </div>
      </div>
    </div>

    <!-- 📅 VIOLATION APPOINTMENTS TABLE -->
    <div id="violationAppointmentsTable" class="table-wrapper" style="display:none;">
      <table>
        <thead>
          <tr>
            <th></th>
            <th>ID</th>
            <th>Student Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($vappointments as $appt)
            @if($appt->violation_app_status === 'Pending' || $appt->violation_app_status === 'Scheduled')
            <tr
              data-app-id="{{ $appt->violation_app_id }}"
              data-status="{{ $appt->violation_app_status }}"
              data-date="{{ $appt->violation_app_date }}"
              data-time="{{ \Carbon\Carbon::parse($appt->violation_app_time)->format('h:i A') }}"
            >
              <td><input type="checkbox" class="rowCheckbox appointmentCheckbox" value="{{ $appt->violation_app_id }}"></td>
              <td>{{ $appt->violation_app_id }}</td>
              <td>
                {{ $appt->violation->student->student_fname ?? 'N/A' }}
                {{ $appt->violation->student->student_lname ?? '' }}
              </td>
              <td>{{ $appt->violation_app_date }}</td>
              <td>{{ \Carbon\Carbon::parse($appt->violation_app_time)->format('h:i A') }}</td>
              <td>
                <span class="status-badge {{ $appt->violation_app_status === 'Pending' ? 'status-pending' : 'status-scheduled' }}">
                  {{ $appt->violation_app_status }}
                </span>
              </td>
              <td><button class="btn-primary editAppointmentBtn">✏️ Edit</button></td>
            </tr>
            @endif
          @empty
          <tr><td colspan="7" style="text-align:center;">No active appointments found</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- 📝 VIOLATION ANECDOTALS TABLE -->
    <div id="violationAnecdotalsTable" class="table-wrapper" style="display:none;">
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
    </div>
  </div>

  <!-- ✏️ Edit Modal -->
  <div class="modal" id="editViolationModal">
    <div class="modal-content">
      <button class="close-btn" id="closeViolationEditModal">✖</button>
      <h2>Edit Record</h2>

      <form id="editViolationForm" method="POST" action="">
        @csrf
        @method('PUT')

        <input type="hidden" name="record_id" id="edit_record_id">

        <div class="form-grid">
          <div class="form-group">
            <label>Details</label>
            <textarea id="edit_details" name="details"></textarea>
          </div>
          <div class="form-group">
            <label>Date</label>
            <input type="date" id="edit_date" name="date" required>
          </div>
          <div class="form-group">
            <label>Time</label>
            <input type="time" id="edit_time" name="time" required>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn-primary">💾 Save Changes</button>
          <button type="button" class="btn-secondary" id="cancelViolationEditBtn">❌ Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 🗃️ VIOLATION RECORDS ARCHIVE MODAL -->
  <div class="modal" id="violationRecordsArchiveModal">
    <div class="modal-content">
      <div class="modal-header">🗃️ Archived Violation Records</div>
      <div class="modal-body">
        <div class="modal-actions">
          <label class="select-all-label">
            <input type="checkbox" id="selectAllViolationRecordsArchived">
            <span>Select All</span>
          </label>
          <div class="filter-container">
            <select id="violationRecordsStatusFilter" class="filter-select">
              <option value="all">All Status</option>
              <option value="cleared">Cleared</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="search-container">
            <input type="search" id="violationRecordsArchiveSearch" placeholder="🔍 Search archived violation records..." class="search-input">
          </div>
        </div>

        <div class="archive-table-container">
          <div id="archiveViolationRecordsTable" class="archive-table-wrapper">
            <table class="archive-table">
              <thead>
                <tr>
                  <th>✔</th>
                  <th>ID</th>
                  <th>Student Name</th>
                  <th>Incident</th>
                  <th>Offense Type</th>
                  <th>Sanction</th>
                  <th>Status</th>
                  <th>Date Archived</th>
                </tr>
              </thead>
              <tbody id="archiveViolationRecordsBody">
                <!-- Archived violation records will be loaded here via AJAX -->
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secondary" id="restoreViolationRecordsBtn">🔄 Restore</button>
          <button class="btn-danger" id="deleteViolationRecordsBtn">🗑️ Delete</button>
          <button class="btn-close" id="closeViolationRecordsArchive">❌ Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- 🗃️ VIOLATION APPOINTMENTS ARCHIVE MODAL -->
  <div class="modal" id="violationAppointmentsArchiveModal">
    <div class="modal-content">
      <div class="modal-header">🗃️ Archived Violation Appointments</div>
      <div class="modal-body">
        <div class="modal-actions">
          <label class="select-all-label">
            <input type="checkbox" id="selectAllViolationAppointmentsArchived">
            <span>Select All</span>
          </label>
          <div class="filter-container">
            <select id="violationAppointmentsStatusFilter" class="filter-select">
              <option value="all">All Status</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>
          <div class="search-container">
            <input type="search" id="violationAppointmentsArchiveSearch" placeholder="🔍 Search archived appointments..." class="search-input">
          </div>
        </div>

        <div class="archive-table-container">
          <div id="archiveViolationAppointmentsTable" class="archive-table-wrapper">
            <table class="archive-table">
              <thead>
                <tr>
                  <th>✔</th>
                  <th>ID</th>
                  <th>Student Name</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Status</th>
                  <th>Date Archived</th>
                </tr>
              </thead>
              <tbody id="archiveViolationAppointmentsBody">
                <!-- Archived violation appointments will be loaded here via AJAX -->
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secondary" id="restoreViolationAppointmentsBtn">🔄 Restore</button>
          <button class="btn-danger" id="deleteViolationAppointmentsBtn">🗑️ Delete</button>
          <button class="btn-close" id="closeViolationAppointmentsArchive">❌ Close</button>
        </div>
      </div>
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

<script>
// Get CSRF Token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

const csrfToken = getCsrfToken();

// Current active table type
let currentTableType = 'violationRecords';

// 🔍 Search Functionality for Main Tables
document.getElementById('searchInput').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const currentTable = document.querySelector('.table-wrapper:not([style*="display: none"])');
    if (currentTable) {
        const rows = currentTable.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }
});

// ✅ Select All - Main Table
document.getElementById('selectAll').addEventListener('change', function() {
    const currentTable = document.querySelector('.table-wrapper:not([style*="display: none"])');
    if (currentTable) {
        const checkboxes = currentTable.querySelectorAll('.rowCheckbox');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
        });
    }
});

// ==================== VIOLATION RECORDS FUNCTIONALITY ====================
// 🗑️ Move to Trash (Archive as Inactive)
document.getElementById('moveToTrashBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.violationCheckbox:checked');
    
    if (!selectedCheckboxes.length) {
        alert('Please select at least one violation.');
        return;
    }

    const violationIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (!confirm(`Are you sure you want to move ${violationIds.length} violation(s) to archive as Inactive?`)) {
        return;
    }

    try {
        const response = await fetch('/prefect/violations/archive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                violation_ids: violationIds,
                status: 'inactive'
            })
        });

        const result = await response.json();
        
        if (result.success) {
            alert(`${violationIds.length} violation(s) moved to archive as Inactive.`);
            // Remove the archived rows from the main table
            violationIds.forEach(id => {
                const row = document.querySelector(`tr[data-violation-id="${id}"]`);
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
        alert('Error moving violations to archive.');
    }
});

// ✅ Mark as Cleared (Archive as Cleared)
document.getElementById('markAsClearedBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.violationCheckbox:checked');
    
    if (!selectedCheckboxes.length) {
        alert('Please select at least one violation.');
        return;
    }

    const violationIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (!confirm(`Are you sure you want to mark ${violationIds.length} violation(s) as Cleared?`)) {
        return;
    }

    try {
        const response = await fetch('/prefect/violations/archive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                violation_ids: violationIds,
                status: 'cleared'
            })
        });

        const result = await response.json();
        
        if (result.success) {
            alert(`${violationIds.length} violation(s) marked as Cleared and moved to archive.`);
            // Remove the cleared rows from the main table
            violationIds.forEach(id => {
                const row = document.querySelector(`tr[data-violation-id="${id}"]`);
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
        alert('Error marking violations as cleared.');
    }
});

// ==================== VIOLATION APPOINTMENTS FUNCTIONALITY ====================
// ✅ Mark Appointment as Completed
document.getElementById('markAppointmentCompletedBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.appointmentCheckbox:checked');
    
    if (!selectedCheckboxes.length) {
        alert('Please select at least one appointment.');
        return;
    }

    const appointmentIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (!confirm(`Are you sure you want to mark ${appointmentIds.length} appointment(s) as Completed?`)) {
        return;
    }

    try {
        const response = await fetch('/prefect/violation-appointments/archive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                appointment_ids: appointmentIds,
                status: 'Completed'
            })
        });

        const result = await response.json();
        
        if (result.success) {
            alert(`${appointmentIds.length} appointment(s) marked as Completed and moved to archive.`);
            // Remove the completed rows from the main table
            appointmentIds.forEach(id => {
                const row = document.querySelector(`tr[data-app-id="${id}"]`);
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
        alert('Error marking appointments as completed.');
    }
});

// 🗑️ Move Appointment to Trash (Archive as Cancelled)
document.getElementById('moveAppointmentToTrashBtn').addEventListener('click', async function() {
    const selectedCheckboxes = document.querySelectorAll('.appointmentCheckbox:checked');
    
    if (!selectedCheckboxes.length) {
        alert('Please select at least one appointment.');
        return;
    }

    const appointmentIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (!confirm(`Are you sure you want to move ${appointmentIds.length} appointment(s) to archive as Cancelled?`)) {
        return;
    }

    try {
        const response = await fetch('/prefect/violation-appointments/archive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                appointment_ids: appointmentIds,
                status: 'Cancelled'
            })
        });

        const result = await response.json();
        
        if (result.success) {
            alert(`${appointmentIds.length} appointment(s) moved to archive as Cancelled.`);
            // Remove the archived rows from the main table
            appointmentIds.forEach(id => {
                const row = document.querySelector(`tr[data-app-id="${id}"]`);
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
        alert('Error moving appointments to archive.');
    }
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

// 🗃️ Archive Button - Opens appropriate archive modal based on current table
document.getElementById('archiveBtn').addEventListener('click', async function() {
    try {
        console.log('Loading archived data for:', currentTableType);
        
        if (currentTableType === 'violationRecords') {
            // Load archived violation records
            const violationResponse = await fetch('/prefect/violations/archived');
            console.log('Violation response status:', violationResponse.status);
            const archivedViolations = await violationResponse.json();
            console.log('Archived violations:', archivedViolations);
            
            // Populate violation records table
            populateArchiveTable('archiveViolationRecordsBody', archivedViolations, 'violation');
            document.getElementById('violationRecordsArchiveModal').style.display = 'flex';
            
        } else if (currentTableType === 'violationAppointments') {
            // Load archived appointments
            const appointmentResponse = await fetch('/prefect/violation-appointments/archived');
            console.log('Appointment response status:', appointmentResponse.status);
            const archivedAppointments = await appointmentResponse.json();
            console.log('Archived appointments:', archivedAppointments);
            
            // Populate appointments table
            populateArchiveTable('archiveViolationAppointmentsBody', archivedAppointments, 'appointment');
            document.getElementById('violationAppointmentsArchiveModal').style.display = 'flex';
            
        } else if (currentTableType === 'violationAnecdotals') {
            // Load archived anecdotals
            const anecdotalResponse = await fetch('/prefect/violation-anecdotals/archived');
            console.log('Anecdotal response status:', anecdotalResponse.status);
            const archivedAnecdotals = await anecdotalResponse.json();
            console.log('Archived anecdotals:', archivedAnecdotals);
            
            // Populate anecdotals table
            populateArchiveTable('archiveViolationAnecdotalsBody', archivedAnecdotals, 'anecdotal');
            document.getElementById('violationAnecdotalsArchiveModal').style.display = 'flex';
        }
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
        tableBody.innerHTML = `<tr><td colspan="8" style="text-align:center;">⚠️ No archived ${type} records found</td></tr>`;
        return;
    }

    data.forEach(item => {
        const row = document.createElement('tr');
        
        if (type === 'violation') {
            row.setAttribute('data-record-id', item.violation_id);
            row.setAttribute('data-record-type', 'violation');
            row.innerHTML = `
                <td><input type="checkbox" class="archiveCheckbox" value="${item.violation_id}" data-type="violation"></td>
                <td>${item.violation_id}</td>
                <td>${item.student_fname} ${item.student_lname}</td>
                <td>${item.violation_incident}</td>
                <td>${item.offense_type}</td>
                <td>${item.sanction_consequences || 'N/A'}</td>
                <td><span class="status-badge ${item.status === 'cleared' ? 'status-cleared' : 'status-inactive'}">${item.status}</span></td>
                <td>${new Date(item.updated_at).toLocaleDateString()}</td>
            `;
        } else if (type === 'appointment') {
            row.setAttribute('data-record-id', item.violation_app_id);
            row.setAttribute('data-record-type', 'appointment');
            row.innerHTML = `
                <td><input type="checkbox" class="archiveCheckbox" value="${item.violation_app_id}" data-type="appointment"></td>
                <td>${item.violation_app_id}</td>
                <td>${item.student_fname} ${item.student_lname}</td>
                <td><span class="status-badge ${item.violation_app_status === 'Completed' ? 'status-cleared' : 'status-inactive'}">${item.violation_app_status}</span></td>
                <td>${item.violation_app_date}</td>
                <td>${item.violation_app_time}</td>
                <td>${new Date(item.updated_at).toLocaleDateString()}</td>
            `;
        } else if (type === 'anecdotal') {
            row.setAttribute('data-record-id', item.violation_anec_id);
            row.setAttribute('data-record-type', 'anecdotal');
            row.innerHTML = `
                <td><input type="checkbox" class="archiveCheckbox" value="${item.violation_anec_id}" data-type="anecdotal"></td>
                <td>${item.violation_anec_id}</td>
                <td>${item.student_fname} ${item.student_lname}</td>
                <td>${item.violation_anec_solution}</td>
                <td>${item.violation_anec_recommendation}</td>
                <td><span class="status-badge ${item.status === 'completed' ? 'status-cleared' : 'status-inactive'}">${item.status}</span></td>
                <td>${new Date(item.updated_at).toLocaleDateString()}</td>
            `;
        }
        
        tableBody.appendChild(row);
    });
}

// Archive Search for each modal
document.getElementById('violationRecordsArchiveSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const tableBody = document.getElementById('archiveViolationRecordsBody');
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

document.getElementById('violationAppointmentsArchiveSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const tableBody = document.getElementById('archiveViolationAppointmentsBody');
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

document.getElementById('violationAnecdotalsArchiveSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const tableBody = document.getElementById('archiveViolationAnecdotalsBody');
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// Archive Status Filters
document.getElementById('violationRecordsStatusFilter').addEventListener('change', function() {
    const filter = this.value;
    const tableBody = document.getElementById('archiveViolationRecordsBody');
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

document.getElementById('violationAppointmentsStatusFilter').addEventListener('change', function() {
    const filter = this.value;
    const tableBody = document.getElementById('archiveViolationAppointmentsBody');
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

// Select All for each archive modal
document.getElementById('selectAllViolationRecordsArchived').addEventListener('change', function() {
    const tableBody = document.getElementById('archiveViolationRecordsBody');
    const checkboxes = tableBody.querySelectorAll('.archiveCheckbox');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
    });
});

document.getElementById('selectAllViolationAppointmentsArchived').addEventListener('change', function() {
    const tableBody = document.getElementById('archiveViolationAppointmentsBody');
    const checkboxes = tableBody.querySelectorAll('.archiveCheckbox');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
    });
});

document.getElementById('selectAllViolationAnecdotalsArchived').addEventListener('change', function() {
    const tableBody = document.getElementById('archiveViolationAnecdotalsBody');
    const checkboxes = tableBody.querySelectorAll('.archiveCheckbox');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
    });
});

// 🔄 Restore Archived Records for each type
document.getElementById('restoreViolationRecordsBtn').addEventListener('click', async function() {
    const tableBody = document.getElementById('archiveViolationRecordsBody');
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

document.getElementById('restoreViolationAppointmentsBtn').addEventListener('click', async function() {
    const tableBody = document.getElementById('archiveViolationAppointmentsBody');
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

// 🗑️ Delete Archived Records Permanently for each type
document.getElementById('deleteViolationRecordsBtn').addEventListener('click', async function() {
    const tableBody = document.getElementById('archiveViolationRecordsBody');
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
                tableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;">⚠️ No archived records found</td></tr>';
            }
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting records.');
    }
});

document.getElementById('deleteViolationAppointmentsBtn').addEventListener('click', async function() {
    const tableBody = document.getElementById('archiveViolationAppointmentsBody');
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
                tableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;">⚠️ No archived records found</td></tr>';
            }
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting records.');
    }
});

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
                tableBody.innerHTML = '<tr><td colspan="8" style="text-align:center;">⚠️ No archived records found</td></tr>';
            }
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting records.');
    }
});

// Close Archive Modals
document.getElementById('closeViolationRecordsArchive').addEventListener('click', function() {
    document.getElementById('violationRecordsArchiveModal').style.display = 'none';
});

document.getElementById('closeViolationAppointmentsArchive').addEventListener('click', function() {
    document.getElementById('violationAppointmentsArchiveModal').style.display = 'none';
});

document.getElementById('closeViolationAnecdotalsArchive').addEventListener('click', function() {
    document.getElementById('violationAnecdotalsArchiveModal').style.display = 'none';
});

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const modals = [
        'violationRecordsArchiveModal',
        'violationAppointmentsArchiveModal',
        'violationAnecdotalsArchiveModal'
    ];
    
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// Switch between main tables and show appropriate action buttons
document.addEventListener('DOMContentLoaded', () => {
  const editModal = document.getElementById('editViolationModal');
  const editForm = document.getElementById('editViolationForm');
  const closeModal = document.getElementById('closeViolationEditModal');
  const cancelBtn = document.getElementById('cancelViolationEditBtn');

  function openModal(action, data) {
    editForm.action = action;
    document.getElementById('edit_record_id').value = data.id || '';
    document.getElementById('edit_details').value = data.details || '';
    document.getElementById('edit_date').value = data.date || '';
    document.getElementById('edit_time').value = convertTo24Hour(data.time || '');
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

  document.querySelectorAll('.editViolationBtn').forEach(btn => {
    btn.addEventListener('click', e => {
      const row = e.target.closest('tr');
      openModal(`/prefect/violations/update/${row.dataset.violationId}`, {
        id: row.dataset.violationId,
        details: row.dataset.incident,
        date: row.dataset.date,
        time: row.dataset.time
      });
    });
  });

  document.querySelectorAll('.editAppointmentBtn').forEach(btn => {
    btn.addEventListener('click', e => {
      const row = e.target.closest('tr');
      openModal(`/prefect/violation-appointments/update/${row.dataset.appId}`, {
        id: row.dataset.appId,
        details: row.dataset.status,
        date: row.dataset.date,
        time: row.dataset.time
      });
    });
  });

  document.querySelectorAll('.editAnecdotalBtn').forEach(btn => {
    btn.addEventListener('click', e => {
      const row = e.target.closest('tr');
      openModal(`/prefect/violation-anecdotals/update/${row.dataset.anecId}`, {
        id: row.dataset.anecId,
        details: `${row.dataset.solution} | ${row.dataset.recommendation}`,
        date: row.dataset.date,
        time: row.dataset.time
      });
    });
  });

  [closeModal, cancelBtn].forEach(btn => btn.addEventListener('click', () => editModal.style.display = 'none'));

  const sections = {
    violationRecords: document.getElementById('violationRecordsTable'),
    violationAppointments: document.getElementById('violationAppointmentsTable'),
    violationAnecdotals: document.getElementById('violationAnecdotalsTable')
  };

  const actionButtons = {
    violationRecords: document.getElementById('violationRecordsActions'),
    violationAppointments: document.getElementById('violationAppointmentsActions'),
    violationAnecdotals: document.getElementById('violationAnecdotalsActions')
  };

  Object.keys(sections).forEach(key => {
    document.getElementById(key).addEventListener('click', e => {
      e.preventDefault();
      // Hide all tables and action buttons
      Object.values(sections).forEach(sec => sec.style.display = 'none');
      Object.values(actionButtons).forEach(btn => btn.style.display = 'none');
      
      // Show selected table and action buttons
      sections[key].style.display = 'block';
      actionButtons[key].style.display = 'flex';
      
      // Update current table type
      currentTableType = key;
      
      // Reset select all checkbox
      document.getElementById('selectAll').checked = false;
    });
  });
});
</script>
@endsection