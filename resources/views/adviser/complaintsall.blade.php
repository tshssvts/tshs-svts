@extends('adviser.layout')
@section('content')
<div class="main-container">
<meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- ✅ Toolbar -->
  <div class="toolbar">
    <h2>Complaint Management</h2>
    <div class="actions">
      <input type="search" placeholder="🔍 Search by student name or ID..." id="searchInput">
      <a href="{{ route('adviser.complaints.create') }}" class="btn-primary" id="createBtn">
        <i class="fas fa-plus"></i> Add Complaint
      </a>
      <button class="btn-info" id="archiveBtn">🗃️ Archive</button>
    </div>
  </div>

  <!-- ✅ Summary Cards -->
  <div class="summary">
    <div class="card"><h2>{{ $monthlyComplaints }}</h2><p>This Month</p></div>
    <div class="card"><h2>{{ $weeklyComplaints }}</h2><p>This Week</p></div>
    <div class="card"><h2>{{ $dailyComplaints }}</h2><p>Today</p></div>
  </div>

  <!-- ✅ Bulk Actions / Tabs -->
  <div class="select-options">
    <div class="left-controls">
      <label for="selectAll" class="select-label">
        <input type="checkbox" id="selectAll"><span>Select All</span>
      </label>

      <div class="dropdown">
        <button class="btn-info dropdown-btn">⬇️ View Records</button>
        <div class="dropdown-content">
          <a href="#" id="complaintRecords">Complaint Records</a>
          <a href="#" id="complaintAppointments">Complaint Appointments</a>
          <a href="#" id="complaintAnecdotals">Complaint Anecdotals</a>
        </div>
      </div>
    </div>

    <div class="right-controls">
      <button class="btn-appointment" id="setAppointmentBtn">📅 Set Appointment</button>
      <button class="btn-anecdotal" id="createAnecdotalBtn">📝 Create Anecdotal</button>
      <button class="btn-cleared">✅ Cleared</button>
      <button class="btn-danger">🗑️ Move Selected to Trash</button>
    </div>
  </div>

  <div class="table-container">

    <!-- 📋 COMPLAINT RECORDS TABLE -->
    <div id="complaintRecordsTable" class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th></th><th>ID</th><th>Complainant</th><th>Respondent</th>
            <th>Offense Type</th><th>Sanction</th><th>Incident</th>
            <th>Date</th><th>Time</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($complaints as $comp)
          <tr
            data-complaint-id="{{ $comp->complaints_id }}"
            data-incident="{{ $comp->complaints_incident }}"
            data-date="{{ $comp->complaints_date }}"
            data-time="{{ \Carbon\Carbon::parse($comp->complaints_time)->format('h:i A') }}"
          >
            <td><input type="checkbox" class="rowCheckbox" data-complaint-id="{{ $comp->complaints_id }}"></td>
            <td>{{ $comp->complaints_id }}</td>
            <td>{{ $comp->complainant_fname }} {{ $comp->complainant_lname }}</td>
            <td>{{ $comp->respondent_fname }} {{ $comp->respondent_lname }}</td>
            <td>{{ $comp->offense_type }}</td>
            <td>{{ $comp->sanction_consequences }}</td>
            <td>{{ $comp->complaints_incident }}</td>
            <td>{{ $comp->complaints_date }}</td>
            <td>{{ \Carbon\Carbon::parse($comp->complaints_time)->format('h:i A') }}</td>
            <td><button class="btn-primary editComplaintBtn">✏️ Edit</button></td>
          </tr>
          @empty
          <tr><td colspan="10" style="text-align:center;">No complaints found</td></tr>
          @endforelse
        </tbody>
      </table>

      <div class="pagination-wrapper">
        <div class="pagination-summary">
          @if($complaints instanceof \Illuminate\Pagination\LengthAwarePaginator)
            Showing {{ $complaints->firstItem() ?? 0 }} to {{ $complaints->lastItem() ?? 0 }} of {{ $complaints->total() ?? 0 }} record(s)
          @endif
        </div>
        <div class="pagination-links">{{ $complaints->links() }}</div>
      </div>
    </div>

    <!-- 📅 COMPLAINT APPOINTMENTS TABLE -->
    <div id="complaintAppointmentsTable" class="table-wrapper" style="display:none;">
      <table>
        <thead>
          <tr><th>ID</th><th>Complainant</th><th>Respondent</th><th>Status</th><th>Date</th><th>Time</th><th>Action</th></tr>
        </thead>
        <tbody>
          @forelse($cappointments as $appt)
          <tr
            data-app-id="{{ $appt->comp_app_id }}"
            data-status="{{ $appt->comp_app_status }}"
            data-date="{{ $appt->comp_app_date }}"
            data-time="{{ \Carbon\Carbon::parse($appt->comp_app_time)->format('h:i A') }}"
          >
            <td>{{ $appt->comp_app_id }}</td>
            <td>{{ $appt->complaint->complainant->student_fname ?? 'N/A' }} {{ $appt->complaint->complainant->student_lname ?? '' }}</td>
            <td>{{ $appt->complaint->respondent->student_fname ?? 'N/A' }} {{ $appt->complaint->respondent->student_lname ?? '' }}</td>
            <td>{{ $appt->comp_app_status }}</td>
            <td>{{ $appt->comp_app_date }}</td>
            <td>{{ \Carbon\Carbon::parse($appt->comp_app_time)->format('h:i A') }}</td>
            <td><button class="btn-primary editAppointmentBtn">✏️ Edit</button></td>
          </tr>
          @empty
          <tr><td colspan="7" style="text-align:center;">No appointments found</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- 📝 COMPLAINT ANECDOTALS TABLE -->
    <div id="complaintAnecdotalsTable" class="table-wrapper" style="display:none;">
      <table>
        <thead>
          <tr><th>ID</th><th>Complainant</th><th>Respondent</th><th>Solution</th><th>Recommendation</th><th>Date</th><th>Time</th><th>Action</th></tr>
        </thead>
        <tbody>
          @forelse($canecdotals as $anec)
          <tr
            data-anec-id="{{ $anec->comp_anec_id }}"
            data-solution="{{ $anec->comp_anec_solution }}"
            data-recommendation="{{ $anec->comp_anec_recommendation }}"
            data-date="{{ $anec->comp_anec_date }}"
            data-time="{{ \Carbon\Carbon::parse($anec->comp_anec_time)->format('h:i A') }}"
          >
            <td>{{ $anec->comp_anec_id }}</td>
            <td>{{ $anec->complaint->complainant->student_fname ?? 'N/A' }} {{ $anec->complaint->complainant->student_lname ?? '' }}</td>
            <td>{{ $anec->complaint->respondent->student_fname ?? 'N/A' }} {{ $anec->complaint->respondent->student_lname ?? '' }}</td>
            <td>{{ Str::limit($anec->comp_anec_solution, 50) }}</td>
            <td>{{ Str::limit($anec->comp_anec_recommendation, 50) }}</td>
            <td>{{ $anec->comp_anec_date }}</td>
            <td>{{ \Carbon\Carbon::parse($anec->comp_anec_time)->format('h:i A') }}</td>
            <td><button class="btn-primary editAnecdotalBtn">✏️ Edit</button></td>
          </tr>
          @empty
          <tr><td colspan="8" style="text-align:center;">No anecdotal records found</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- ✏️ Edit Modal -->
  <div class="modal" id="editComplaintModal">
    <div class="modal-content">
      <button class="close-btn" id="closeComplaintEditModal">✖</button>
      <h2>Edit Record</h2>

      <form id="editComplaintForm" method="POST" action="">
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
          <button type="button" class="btn-secondary" id="cancelComplaintEditBtn">❌ Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 📅 Set Appointment Modal -->
  <div class="modal" id="appointmentModal">
    <div class="modal-content">
      <button class="close-btn" id="closeAppointmentModal">✖</button>
      <h2>Set Appointment for Selected Complaints</h2>

      <form id="appointmentForm" method="POST" action="{{ route('adviser.complaint-appointments.store') }}">
        @csrf

        <div class="selected-violations">
          <h3>Selected Complaints:</h3>
          <div id="selectedComplaintsForAppointment" class="selected-list">
            <!-- Selected complaints will be listed here -->
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Appointment Date</label>
            <input type="date" name="comp_app_date" id="appointment_date" required>
          </div>
          <div class="form-group">
            <label>Appointment Time</label>
            <input type="time" name="comp_app_time" id="appointment_time" required>
          </div>
          <div class="form-group full-width">
            <label>Status</label>
            <select name="comp_app_status" id="appointment_status" required>
              <option value="scheduled">Scheduled</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
              <option value="rescheduled">Rescheduled</option>
            </select>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn-primary">📅 Create Appointments</button>
          <button type="button" class="btn-secondary" id="cancelAppointmentBtn">❌ Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 📝 Create Anecdotal Modal -->
  <div class="modal" id="createAnecdotalModal">
    <div class="modal-content">
      <button class="close-btn" id="closeAnecdotalModal">✖</button>
      <h2>Create Anecdotal Record for Selected Complaints</h2>

      <form id="createAnecdotalForm" method="POST" action="{{ route('adviser.complaint-anecdotals.store') }}">
        @csrf

        <div class="selected-violations">
          <h3>Selected Complaints:</h3>
          <div id="selectedComplaintsForAnecdotal" class="selected-list">
            <!-- Selected complaints for anecdotal will be listed here -->
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label for="anecdotal_date">Anecdotal Date</label>
            <input type="date" id="anecdotal_date" name="anecdotal_date" required value="{{ date('Y-m-d') }}">
          </div>
          <div class="form-group">
            <label for="anecdotal_time">Anecdotal Time</label>
            <input type="time" id="anecdotal_time" name="anecdotal_time" required value="{{ date('H:i') }}">
          </div>
          <div class="form-group full-width">
            <label for="comp_anec_solution">Solution</label>
            <textarea id="comp_anec_solution" name="comp_anec_solution" placeholder="Describe the solution implemented..." required rows="4"></textarea>
          </div>
          <div class="form-group full-width">
            <label for="comp_anec_recommendation">Recommendation</label>
            <textarea id="comp_anec_recommendation" name="comp_anec_recommendation" placeholder="Provide recommendations for future prevention..." required rows="4"></textarea>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn-primary">📝 Create Anecdotal Records</button>
          <button type="button" class="btn-secondary" id="cancelAnecdotalBtn">❌ Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ✅ Anecdotal Success Modal -->
  <div class="modal" id="anecdotalSuccessModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>✅ Anecdotal Records Created Successfully</h2>
      </div>
      <div class="modal-body">
        <p id="successMessage"></p>
        <div class="success-actions">
          <button class="btn-print" id="printAnecdotalBtn">🖨️ Print Records</button>
          <button class="btn-primary" id="closeSuccessModal">OK</button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.btn-appointment {
  background-color: #28a745;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 8px;
}

.btn-anecdotal {
  background-color: #17a2b8;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 8px;
}

.btn-cleared {
  background-color: #6c757d;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 8px;
}

.btn-print {
  background-color: #6f42c1;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  margin-right: 10px;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.right-controls {
  display: flex;
  gap: 8px;
  align-items: center;
}

.selected-violations {
  margin-bottom: 20px;
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.selected-list {
  max-height: 150px;
  overflow-y: auto;
  margin-top: 10px;
}

.selected-complaint-item {
  padding: 8px 12px;
  margin-bottom: 5px;
  background-color: white;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  font-size: 0.9em;
}

.success-actions {
  display: flex;
  gap: 10px;
  justify-content: center;
  margin-top: 20px;
}
</style>

<script>
// Get CSRF Token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

const csrfToken = getCsrfToken();

document.addEventListener('DOMContentLoaded', () => {
  // Existing modal functionality
  const modal = document.getElementById('editComplaintModal');
  const form = document.getElementById('editComplaintForm');
  const close = document.getElementById('closeComplaintEditModal');
  const cancel = document.getElementById('cancelComplaintEditBtn');

  const openModal = (action, data) => {
    form.action = action;
    document.getElementById('edit_record_id').value = data.id || '';
    document.getElementById('edit_details').value = data.details || '';
    document.getElementById('edit_date').value = data.date || '';
    document.getElementById('edit_time').value = convertTo24Hour(data.time || '');
    modal.style.display = 'flex';
  };

  const convertTo24Hour = t => {
    if (!t.includes(' ')) return t;
    const [time, mod] = t.split(' ');
    let [h, m] = time.split(':'); h = +h;
    if (mod === 'PM' && h !== 12) h += 12;
    if (mod === 'AM' && h === 12) h = 0;
    return `${h.toString().padStart(2,'0')}:${m}`;
  };

  document.querySelectorAll('.editComplaintBtn').forEach(btn => btn.addEventListener('click', e => {
    const r = e.target.closest('tr');
    openModal(`/adviser/complaints/update/${r.dataset.complaintId}`, {
      id: r.dataset.complaintId,
      details: r.dataset.incident,
      date: r.dataset.date,
      time: r.dataset.time
    });
  }));

  document.querySelectorAll('.editAppointmentBtn').forEach(btn => btn.addEventListener('click', e => {
    const r = e.target.closest('tr');
    openModal(`/adviser/complaint-appointments/update/${r.dataset.appId}`, {
      id: r.dataset.appId,
      details: r.dataset.status,
      date: r.dataset.date,
      time: r.dataset.time
    });
  }));

  document.querySelectorAll('.editAnecdotalBtn').forEach(btn => btn.addEventListener('click', e => {
    const r = e.target.closest('tr');
    openModal(`/adviser/complaint-anecdotals/update/${r.dataset.anecId}`, {
      id: r.dataset.anecId,
      details: r.dataset.solution,
      date: r.dataset.date,
      time: r.dataset.time
    });
  }));

  [close, cancel].forEach(b => b.addEventListener('click', () => modal.style.display = 'none'));

  // Table navigation
  const sections = {
    complaintRecords: document.getElementById('complaintRecordsTable'),
    complaintAppointments: document.getElementById('complaintAppointmentsTable'),
    complaintAnecdotals: document.getElementById('complaintAnecdotalsTable')
  };

  Object.keys(sections).forEach(key => {
    document.getElementById(key).addEventListener('click', e => {
      e.preventDefault();
      Object.values(sections).forEach(s => s.style.display = 'none');
      sections[key].style.display = 'block';
    });
  });

  // ==================== SET APPOINTMENT FUNCTIONALITY ====================
  document.getElementById('setAppointmentBtn').addEventListener('click', function() {
    const selectedCheckboxes = document.querySelectorAll('.rowCheckbox:checked');

    if (!selectedCheckboxes.length) {
      alert('Please select at least one complaint to schedule.');
      return;
    }

    // Get selected complaint data
    const selectedComplaints = Array.from(selectedCheckboxes).map(cb => {
      const row = cb.closest('tr');
      return {
        complaint_id: row.dataset.complaintId,
        complainant: row.cells[2].textContent,
        respondent: row.cells[3].textContent,
        incident: row.dataset.incident
      };
    });

    // Populate selected complaints list
    const selectedList = document.getElementById('selectedComplaintsForAppointment');
    selectedList.innerHTML = '';

    selectedComplaints.forEach(complaint => {
      const item = document.createElement('div');
      item.className = 'selected-complaint-item';
      item.innerHTML = `
        <strong>${complaint.complainant} vs ${complaint.respondent}</strong><br>
        <small>Incident: ${complaint.incident}</small>
        <input type="hidden" name="complaint_ids[]" value="${complaint.complaint_id}">
      `;
      selectedList.appendChild(item);
    });

    // Show the modal
    document.getElementById('appointmentModal').style.display = 'flex';
    document.getElementById('appointment_date').value = new Date().toISOString().split('T')[0];
  });

  // ==================== CREATE ANECDOTAL FUNCTIONALITY ====================
  document.getElementById('createAnecdotalBtn').addEventListener('click', function() {
    const selectedCheckboxes = document.querySelectorAll('.rowCheckbox:checked');

    if (!selectedCheckboxes.length) {
      alert('Please select at least one complaint to create anecdotal record.');
      return;
    }

    // Get selected complaint data
    const selectedComplaints = Array.from(selectedCheckboxes).map(cb => {
      const row = cb.closest('tr');
      return {
        complaint_id: row.dataset.complaintId,
        complainant: row.cells[2].textContent,
        respondent: row.cells[3].textContent,
        incident: row.dataset.incident,
        offense_type: row.cells[4].textContent,
        sanction: row.cells[5].textContent
      };
    });

    // Populate selected complaints list
    const selectedList = document.getElementById('selectedComplaintsForAnecdotal');
    selectedList.innerHTML = '';

    selectedComplaints.forEach(complaint => {
      const item = document.createElement('div');
      item.className = 'selected-complaint-item';
      item.innerHTML = `
        <strong>${complaint.complainant} vs ${complaint.respondent}</strong><br>
        <small>Incident: ${complaint.incident}</small><br>
        <small>Offense: ${complaint.offense_type}</small>
        <input type="hidden" name="complaint_ids[]" value="${complaint.complaint_id}">
      `;
      selectedList.appendChild(item);
    });

    // Show the modal
    document.getElementById('createAnecdotalModal').style.display = 'flex';
  });

  // Close modals
  document.getElementById('closeAppointmentModal').addEventListener('click', () => document.getElementById('appointmentModal').style.display = 'none');
  document.getElementById('closeAnecdotalModal').addEventListener('click', () => document.getElementById('createAnecdotalModal').style.display = 'none');
  document.getElementById('cancelAppointmentBtn').addEventListener('click', () => document.getElementById('appointmentModal').style.display = 'none');
  document.getElementById('cancelAnecdotalBtn').addEventListener('click', () => document.getElementById('createAnecdotalModal').style.display = 'none');

  // Handle anecdotal form submission
  document.getElementById('createAnecdotalForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    try {
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
      submitBtn.disabled = true;

      const response = await fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        // Show success modal
        document.getElementById('createAnecdotalModal').style.display = 'none';
        document.getElementById('successMessage').textContent = result.message;
        document.getElementById('anecdotalSuccessModal').style.display = 'flex';

        // Store created anecdotal data for printing
        window.lastCreatedAnecdotals = result.data;
      } else {
        if (result.errors) {
          let messages = Object.values(result.errors).flat().join('\n');
          alert('Validation failed:\n' + messages);
          console.error(result.errors);
        } else {
          alert('Error: ' + (result.message || 'Unknown error'));
        }
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Error creating anecdotal records.');
    } finally {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  });

  // Handle appointment form submission
  document.getElementById('appointmentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    try {
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
      submitBtn.disabled = true;

      const response = await fetch(this.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        alert('Appointments created successfully!');
        document.getElementById('appointmentModal').style.display = 'none';
        location.reload();
      } else {
        if (result.errors) {
          let messages = Object.values(result.errors).flat().join('\n');
          alert('Validation failed:\n' + messages);
        } else {
          alert('Error: ' + (result.message || 'Unknown error'));
        }
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Error creating appointments.');
    } finally {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  });

  // Print Anecdotal Records
  document.getElementById('printAnecdotalBtn').addEventListener('click', function() {
    if (!window.lastCreatedAnecdotals || window.lastCreatedAnecdotals.length === 0) {
      alert('No anecdotal records to print.');
      return;
    }

    const printWindow = window.open('', '_blank');
    const printContent = generateAnecdotalPrintContent(window.lastCreatedAnecdotals);

    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Complaint Anecdotal Records</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; }
          .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
          .anecdotal-record { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; page-break-inside: avoid; }
          .record-header { background: #f5f5f5; padding: 10px; margin: -15px -15px 15px -15px; border-bottom: 1px solid #ddd; }
          .field { margin-bottom: 10px; }
          .field label { font-weight: bold; display: inline-block; width: 150px; }
          .field-content { margin-left: 150px; margin-top: 5px; }
          @media print {
            .no-print { display: none; }
            .anecdotal-record { page-break-inside: avoid; }
          }
        </style>
      </head>
      <body>
        ${printContent}
        <div class="no-print" style="margin-top: 20px; text-align: center;">
          <button onclick="window.print()">Print</button>
          <button onclick="window.close()">Close</button>
        </div>
      </body>
      </html>
    `);

    printWindow.document.close();
  });

  // Close Success Modal
  document.getElementById('closeSuccessModal').addEventListener('click', function() {
    document.getElementById('anecdotalSuccessModal').style.display = 'none';
    location.reload(); // Reload to show the new anecdotal records
  });

  // Select All functionality
  document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.rowCheckbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
  });

  // Function to generate print content
  function generateAnecdotalPrintContent(anecdotals) {
    let content = `
      <div class="header">
        <h1>Complaint Anecdotal Records Report</h1>
        <p>Generated on: ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</p>
      </div>
    `;

    anecdotals.forEach((anecdotal, index) => {
      content += `
        <div class="anecdotal-record">
          <div class="record-header">
            <h3>Anecdotal Record #${index + 1}</h3>
          </div>
          <div class="field">
            <label>Anecdotal ID:</label> ${anecdotal.comp_anec_id || 'N/A'}
          </div>
          <div class="field">
            <label>Complaint ID:</label> ${anecdotal.complaints_id}
          </div>
          <div class="field">
            <label>Date:</label> ${anecdotal.comp_anec_date}
          </div>
          <div class="field">
            <label>Time:</label> ${anecdotal.comp_anec_time}
          </div>
          <div class="field">
            <label>Solution:</label>
            <div class="field-content">${anecdotal.comp_anec_solution}</div>
          </div>
          <div class="field">
            <label>Recommendation:</label>
            <div class="field-content">${anecdotal.comp_anec_recommendation}</div>
          </div>
          <div class="field">
            <label>Status:</label> ${anecdotal.status}
          </div>
          <div class="field">
            <label>Created:</label> ${new Date(anecdotal.created_at).toLocaleString()}
          </div>
        </div>
      `;
    });

    return content;
  }
});
</script>
@endsection
