@extends('prefect.layout')

@section('content')
<style>
  /* Archive Modal Styles */
.archive-tabs {
  display: flex;
  border-bottom: 2px solid #e0e0e0;
  margin-bottom: 20px;
  background: #f8f9fa;
  border-radius: 8px 8px 0 0;
  padding: 5px;
}

.archive-tab {
  padding: 12px 24px;
  border: none;
  background: transparent;
  cursor: pointer;
  border-radius: 6px;
  margin-right: 5px;
  font-weight: 500;
  transition: all 0.3s ease;
  color: #6c757d;
  position: relative;
}

.archive-tab:hover {
  background: #e9ecef;
  color: #495057;
  transform: translateY(-1px);
}

.archive-tab.active {
  background: #007bff;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

.archive-tab.active::after {
  content: '';
  position: absolute;
  bottom: -7px;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-top: 6px solid #007bff;
}

.archive-table-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 20px;
}

.archive-table-wrapper {
  display: none;
  max-height: 60vh;
  overflow-y: auto;
  animation: fadeIn 0.3s ease-in-out;
}

.archive-table-wrapper.active {
  display: block;
}

.archive-table-wrapper table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.archive-table-wrapper thead {
  position: sticky;
  top: 0;
  z-index: 10;
  background: #4b0000;
}

.archive-table-wrapper th {
  padding: 15px 12px;
  text-align: left;
  font-weight: 600;
  color: white;
  border-bottom: 2px solid #dee2e6;
  font-size: 0.9em;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.archive-table-wrapper td {
  padding: 12px;
  border-bottom: 1px solid #e9ecef;
  vertical-align: middle;
  transition: background-color 0.2s ease;
}

.archive-table-wrapper tbody tr {
  transition: all 0.2s ease;
}

.archive-table-wrapper tbody tr:hover {
  background-color: #f8f9fa;
  transform: scale(1.01);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.archive-table-wrapper tbody tr:nth-child(even) {
  background-color: #fafafa;
}

.archive-table-wrapper tbody tr:nth-child(even):hover {
  background-color: #f1f3f4;
}

.archive-search {
  display: flex;
  gap: 15px;
  margin-bottom: 25px;
  align-items: center;
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.archive-search input {
  flex: 1;
  padding: 12px 16px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
  background: #f8f9fa;
}

.archive-search input:focus {
  outline: none;
  border-color: #007bff;
  background: white;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.archive-search select {
  padding: 12px 16px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  background: #f8f9fa;
  font-size: 14px;
  min-width: 150px;
  transition: all 0.3s ease;
}

.archive-search select:focus {
  outline: none;
  border-color: #007bff;
  background: white;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.archive-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 25px;
  padding-top: 20px;
  border-top: 2px solid #e9ecef;
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.archive-actions button {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

#restoreSelectedBtn {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

#restoreSelectedBtn:hover {
  background: linear-gradient(135deg, #218838, #1e9e8a);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

#deletePermanentlyBtn {
  background: linear-gradient(135deg, #dc3545, #e83e8c);
  color: white;
}

#deletePermanentlyBtn:hover {
  background: linear-gradient(135deg, #c82333, #d91a7a);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

#closeArchiveModalBtn {
  background: #6c757d;
  color: white;
}

#closeArchiveModalBtn:hover {
  background: #5a6268;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

/* Checkbox Styles */
.archiveCheckbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #007bff;
  transition: all 0.2s ease;
}

.archiveCheckbox:hover {
  transform: scale(1.1);
}

/* Action Buttons in Table */
.btn-restore {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.btn-restore:hover {
  background: linear-gradient(135deg, #218838, #1e9e8a);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.btn-delete-permanent {
  background: linear-gradient(135deg, #dc3545, #e83e8c);
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.btn-delete-permanent:hover {
  background: linear-gradient(135deg, #c82333, #d91a7a);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

/* Status Badges */
.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75em;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: inline-block;
  min-width: 80px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.status-active { 
  background: linear-gradient(135deg, #d4edda, #c3e6cb);
  color: #155724;
  border: 1px solid #c3e6cb;
}

.status-cleared { 
  background: linear-gradient(135deg, #e2e3e5, #d6d8db);
  color: #383d41;
  border: 1px solid #d6d8db;
}

.status-inactive { 
  background: linear-gradient(135deg, #f8d7da, #f1b0b7);
  color: #721c24;
  border: 1px solid #f1b0b7;
}

.status-scheduled { 
  background: linear-gradient(135deg, #cce7ff, #b3d9ff);
  color: #004085;
  border: 1px solid #b3d9ff;
}

.status-completed { 
  background: linear-gradient(135deg, #d4edda, #c3e6cb);
  color: #155724;
  border: 1px solid #c3e6cb;
}

.status-cancelled { 
  background: linear-gradient(135deg, #f8d7da, #f1b0b7);
  color: #721c24;
  border: 1px solid #f1b0b7;
}

.status-pending { 
  background: linear-gradient(135deg, #fff3cd, #ffeaa7);
  color: #856404;
  border: 1px solid #ffeaa7;
}

.status-rescheduled { 
  background: linear-gradient(135deg, #d1ecf1, #b9e3ea);
  color: #0c5460;
  border: 1px solid #b9e3ea;
}

/* Scrollbar Styling */
.archive-table-wrapper::-webkit-scrollbar {
  width: 8px;
}

.archive-table-wrapper::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.archive-table-wrapper::-webkit-scrollbar-thumb {
  background: #4b0000;
  border-radius: 4px;
}

.archive-table-wrapper::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #5a6fd8, #6a4190);
}

/* Empty State */
.archive-table-wrapper tbody tr td[colspan] {
  text-align: center;
  padding: 40px;
  color: #6c757d;
  font-style: italic;
  background: #f8f9fa;
}

/* Loading Animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .archive-tabs {
    flex-direction: column;
    gap: 5px;
  }
  
  .archive-tab {
    margin-right: 0;
    text-align: center;
  }
  
  .archive-search {
    flex-direction: column;
    gap: 10px;
  }
  
  .archive-search select {
    min-width: 100%;
  }
  
  .archive-actions {
    flex-direction: column;
  }
  
  .archive-table-wrapper {
    font-size: 0.9em;
  }
  
  .archive-table-wrapper th,
  .archive-table-wrapper td {
    padding: 8px 6px;
  }
  
  .btn-restore,
  .btn-delete-permanent {
    padding: 6px 12px;
    font-size: 11px;
  }
}

/* Modal Header Enhancement */
#archiveModal .modal-content h2 {
  background: #4b0000;
  color: white;
  padding: 20px;
  margin: -20px -20px 20px -20px;
  border-radius: 8px 8px 0 0;
  text-align: center;
  font-size: 1.5em;
  font-weight: 600;
}

/* Close Button Styling */
#closeArchiveModal {
  position: absolute;
  top: 15px;
  right: 20px;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  font-size: 1.2em;
  cursor: pointer;
  padding: 5px 10px;
  border-radius: 50%;
  transition: all 0.3s ease;
  z-index: 1000;
}

#closeArchiveModal:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

/* Select All Checkbox */
#selectAllArchiveComplaints,
#selectAllArchiveAppointments,
#selectAllArchiveAnecdotals {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #007bff;
}
</style>
<div class="main-container">
<meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- ✅ Toolbar -->
  <div class="toolbar">
    <h2>Complaint Management</h2>
    <div class="actions">
      <input type="search" placeholder="🔍 Search by student name or ID..." id="searchInput">
      <a href="{{ route('complaints.create') }}" class="btn-primary" id="createBtn">
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
      <button class="btn-cleared" id="clearedBtn">✅ Cleared</button>
      <button class="btn-danger" id="moveToTrashBtn">🗑️ Move Selected to Trash</button>
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
            <th>Date</th><th>Time</th><th>Status</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($complaints as $comp)
          @if($comp->status === 'active')
          <tr
            data-complaint-id="{{ $comp->complaints_id }}"
            data-incident="{{ $comp->complaints_incident }}"
            data-date="{{ $comp->complaints_date }}"
            data-time="{{ \Carbon\Carbon::parse($comp->complaints_time)->format('h:i A') }}"
            data-offense-type="{{ $comp->offense_type }}"
            data-sanction="{{ $comp->sanction_consequences }}"
            data-complainant="{{ $comp->complainant_fname }} {{ $comp->complainant_lname }}"
            data-respondent="{{ $comp->respondent_fname }} {{ $comp->respondent_lname }}"
            data-status="{{ $comp->status }}"
          >
            <td><input type="checkbox" class="rowCheckbox" data-type="complaint" data-id="{{ $comp->complaints_id }}"></td>
            <td>{{ $comp->complaints_id }}</td>
            <td>{{ $comp->complainant_fname }} {{ $comp->complainant_lname }}</td>
            <td>{{ $comp->respondent_fname }} {{ $comp->respondent_lname }}</td>
            <td>{{ $comp->offense_type }}</td>
            <td>{{ $comp->sanction_consequences }}</td>
            <td>{{ $comp->complaints_incident }}</td>
            <td>{{ $comp->complaints_date }}</td>
            <td>{{ \Carbon\Carbon::parse($comp->complaints_time)->format('h:i A') }}</td>
            <td><span class="status-badge status-{{ $comp->status }}">{{ ucfirst($comp->status) }}</span></td>
            <td><button class="btn-primary editComplaintBtn">✏️ Edit</button></td>
          </tr>
          @endif
          @empty
          <tr><td colspan="11" style="text-align:center;">No active complaints found</td></tr>
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
          <tr><th></th><th>ID</th><th>Complainant</th><th>Respondent</th><th>Status</th><th>Date</th><th>Time</th><th>Action</th></tr>
        </thead>
        <tbody>
          @forelse($cappointments as $appt)
          @if($appt->status === 'active')
          <tr
            data-app-id="{{ $appt->comp_app_id }}"
            data-status="{{ $appt->comp_app_status }}"
            data-date="{{ $appt->comp_app_date }}"
            data-time="{{ \Carbon\Carbon::parse($appt->comp_app_time)->format('h:i A') }}"
            data-complaint-id="{{ $appt->complaints_id }}"
            data-archive-status="{{ $appt->status }}"
          >
            <td><input type="checkbox" class="rowCheckbox" data-type="appointment" data-id="{{ $appt->comp_app_id }}"></td>
            <td>{{ $appt->comp_app_id }}</td>
            <td>{{ $appt->complaint->complainant->student_fname ?? 'N/A' }} {{ $appt->complaint->complainant->student_lname ?? '' }}</td>
            <td>{{ $appt->complaint->respondent->student_fname ?? 'N/A' }} {{ $appt->complaint->respondent->student_lname ?? '' }}</td>
            <td><span class="status-badge status-{{ $appt->comp_app_status }}">{{ ucfirst($appt->comp_app_status) }}</span></td>
            <td>{{ $appt->comp_app_date }}</td>
            <td>{{ \Carbon\Carbon::parse($appt->comp_app_time)->format('h:i A') }}</td>
            <td><button class="btn-primary editAppointmentBtn">✏️ Edit</button></td>
          </tr>
          @endif
          @empty
          <tr><td colspan="8" style="text-align:center;">No active appointments found</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- 📝 COMPLAINT ANECDOTALS TABLE -->
    <div id="complaintAnecdotalsTable" class="table-wrapper" style="display:none;">
      <table>
        <thead>
          <tr><th></th><th>ID</th><th>Complainant</th><th>Respondent</th><th>Solution</th><th>Recommendation</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
          @forelse($canecdotals as $anec)
          @if($anec->status === 'active')
          <tr
            data-anec-id="{{ $anec->comp_anec_id }}"
            data-solution="{{ $anec->comp_anec_solution }}"
            data-recommendation="{{ $anec->comp_anec_recommendation }}"
            data-date="{{ $anec->comp_anec_date }}"
            data-time="{{ \Carbon\Carbon::parse($anec->comp_anec_time)->format('h:i A') }}"
            data-complaint-id="{{ $anec->complaints_id }}"
            data-archive-status="{{ $anec->status }}"
          >
            <td><input type="checkbox" class="rowCheckbox" data-type="anecdotal" data-id="{{ $anec->comp_anec_id }}"></td>
            <td>{{ $anec->comp_anec_id }}</td>
            <td>{{ $anec->complaint->complainant->student_fname ?? 'N/A' }} {{ $anec->complaint->complainant->student_lname ?? '' }}</td>
            <td>{{ $anec->complaint->respondent->student_fname ?? 'N/A' }} {{ $anec->complaint->respondent->student_lname ?? '' }}</td>
            <td>{{ Str::limit($anec->comp_anec_solution, 50) }}</td>
            <td>{{ Str::limit($anec->comp_anec_recommendation, 50) }}</td>
            <td>{{ $anec->comp_anec_date }}</td>
            <td>{{ \Carbon\Carbon::parse($anec->comp_anec_time)->format('h:i A') }}</td>
            <td><span class="status-badge status-{{ $anec->status }}">{{ ucfirst($anec->status) }}</span></td>
            <td><button class="btn-primary editAnecdotalBtn">✏️ Edit</button></td>
          </tr>
          @endif
          @empty
          <tr><td colspan="10" style="text-align:center;">No active anecdotal records found</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- 🗃️ Archive Modal -->
  <div class="modal" id="archiveModal">
    <div class="modal-content" style="max-width: 95%; width: 95%;">
      <button class="close-btn" id="closeArchiveModal">✖</button>
      <h2>🗃️ Archive Records</h2>
      
      <!-- Archive Tabs -->
      <div class="archive-tabs">
        <button class="archive-tab active" data-tab="complaintRecordsArchive">Complaint Records</button>
        <button class="archive-tab" data-tab="complaintAppointmentsArchive">Complaint Appointments</button>
        <button class="archive-tab" data-tab="complaintAnecdotalsArchive">Complaint Anecdotals</button>
      </div>

      <!-- Search Filter -->
      <div class="archive-search">
        <input type="search" id="archiveSearchInput" placeholder="🔍 Search in archive...">
        <select id="archiveStatusFilter">
          <option value="">All Status</option>
          <option value="cleared">Cleared</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>

      <!-- Archive Tables -->
      <div class="archive-table-container">
        <!-- Complaint Records Archive -->
        <div id="complaintRecordsArchive" class="archive-table-wrapper active">
          <table>
            <thead>
              <tr>
                <th><input type="checkbox" id="selectAllArchiveComplaints"></th>
                <th>ID</th><th>Complainant</th><th>Respondent</th>
                <th>Offense Type</th><th>Sanction</th><th>Incident</th>
                <th>Date</th><th>Time</th><th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="complaintRecordsArchiveBody">
              <!-- Will be populated dynamically -->
            </tbody>
          </table>
        </div>

        <!-- Complaint Appointments Archive -->
        <div id="complaintAppointmentsArchive" class="archive-table-wrapper">
          <table>
            <thead>
              <tr>
                <th><input type="checkbox" id="selectAllArchiveAppointments"></th>
                <th>ID</th><th>Complainant</th><th>Respondent</th>
                <th>Appointment Status</th><th>Date</th><th>Time</th><th>Archive Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="complaintAppointmentsArchiveBody">
              <!-- Will be populated dynamically -->
            </tbody>
          </table>
        </div>

        <!-- Complaint Anecdotals Archive -->
        <div id="complaintAnecdotalsArchive" class="archive-table-wrapper">
          <table>
            <thead>
              <tr>
                <th><input type="checkbox" id="selectAllArchiveAnecdotals"></th>
                <th>ID</th><th>Complainant</th><th>Respondent</th>
                <th>Solution</th><th>Recommendation</th>
                <th>Date</th><th>Time</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="complaintAnecdotalsArchiveBody">
              <!-- Will be populated dynamically -->
            </tbody>
          </table>
        </div>
      </div>

      <div class="archive-actions">
        <button class="btn-primary" id="restoreSelectedBtn">🔄 Restore Selected</button>
        <button class="btn-danger" id="deletePermanentlyBtn">🗑️ Delete Permanently</button>
        <button class="btn-secondary" id="closeArchiveModalBtn">Close</button>
      </div>
    </div>
  </div>

  <!-- ✏️ Edit Complaint Modal -->
  <div class="modal" id="editComplaintModal">
    <div class="modal-content">
      <button class="close-btn" id="closeComplaintEditModal">✖</button>
      <h2>Edit Complaint Record</h2>

      <form id="editComplaintForm" method="POST" action="">
        @csrf
        @method('PUT')
        <input type="hidden" name="record_id" id="edit_complaint_id">

        <div class="form-grid">
          <div class="form-group full-width">
            <label>Incident Description</label>
            <textarea id="edit_incident" name="complaints_incident" required rows="4"></textarea>
          </div>
          <div class="form-group">
            <label>Offense Type</label>
            <input type="text" id="edit_offense_type" name="offense_type" required>
          </div>
          <div class="form-group">
            <label>Sanction</label>
            <input type="text" id="edit_sanction" name="sanction_consequences" required>
          </div>
          <div class="form-group">
            <label>Date</label>
            <input type="date" id="edit_complaint_date" name="complaints_date" required>
          </div>
          <div class="form-group">
            <label>Time</label>
            <input type="time" id="edit_complaint_time" name="complaints_time" required>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn-primary">💾 Save Changes</button>
          <button type="button" class="btn-secondary" id="cancelComplaintEditBtn">❌ Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ✏️ Edit Appointment Modal -->
  <div class="modal" id="editAppointmentModal">
    <div class="modal-content">
      <button class="close-btn" id="closeAppointmentEditModal">✖</button>
      <h2>Edit Appointment</h2>

      <form id="editAppointmentForm" method="POST" action="">
        @csrf
        @method('PUT')
        <input type="hidden" name="record_id" id="edit_appointment_id">

        <div class="form-grid">
          <div class="form-group">
            <label>Status</label>
            <select id="edit_app_status" name="comp_app_status" required>
              <option value="scheduled">Scheduled</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
              <option value="rescheduled">Rescheduled</option>
              <option value="pending">Pending</option>
            </select>
          </div>
          <div class="form-group">
            <label>Date</label>
            <input type="date" id="edit_app_date" name="comp_app_date" required>
          </div>
          <div class="form-group">
            <label>Time</label>
            <input type="time" id="edit_app_time" name="comp_app_time" required>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn-primary">💾 Save Changes</button>
          <button type="button" class="btn-secondary" id="cancelAppointmentEditBtn">❌ Cancel</button>
        </div>
      </form>
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
        <input type="hidden" name="record_id" id="edit_anecdotal_id">

        <div class="form-grid">
          <div class="form-group full-width">
            <label>Solution</label>
            <textarea id="edit_solution" name="comp_anec_solution" required rows="4" placeholder="Describe the solution implemented..."></textarea>
          </div>
          <div class="form-group full-width">
            <label>Recommendation</label>
            <textarea id="edit_recommendation" name="comp_anec_recommendation" required rows="4" placeholder="Provide recommendations for future prevention..."></textarea>
          </div>
          <div class="form-group">
            <label>Date</label>
            <input type="date" id="edit_anec_date" name="comp_anec_date" required>
          </div>
          <div class="form-group">
            <label>Time</label>
            <input type="time" id="edit_anec_time" name="comp_anec_time" required>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn-primary">💾 Save Changes</button>
          <button type="button" class="btn-secondary" id="cancelAnecdotalEditBtn">❌ Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 📅 Set Appointment Modal -->
  <div class="modal" id="appointmentModal">
    <div class="modal-content">
      <button class="close-btn" id="closeAppointmentModal">✖</button>
      <h2>Set Appointment for Selected Complaints</h2>

      <form id="appointmentForm" method="POST" action="{{ route('complaint-appointments.store') }}">
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
              <option value="pending">Pending</option>
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

      <form id="createAnecdotalForm" method="POST" action="{{ route('complaint-anecdotals.store') }}">
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

/* Archive Modal Styles */
.archive-tabs {
  display: flex;
  border-bottom: 2px solid #ddd;
  margin-bottom: 15px;
}

.archive-tab {
  padding: 10px 20px;
  border: none;
  background: #f8f9fa;
  cursor: pointer;
  border-radius: 4px 4px 0 0;
  margin-right: 5px;
}

.archive-tab.active {
  background: #007bff;
  color: white;
}

.archive-table-wrapper {
  display: none;
  max-height: 60vh;
  overflow-y: auto;
}

.archive-table-wrapper.active {
  display: block;
}

.archive-search {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}

.archive-search input,
.archive-search select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.archive-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #ddd;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.8em;
  font-weight: bold;
}

.status-active { background: #d4edda; color: #155724; }
.status-cleared { background: #e2e3e5; color: #383d41; }
.status-inactive { background: #f8d7da; color: #721c24; }
.status-scheduled { background: #cce7ff; color: #004085; }
.status-completed { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }
.status-pending { background: #fff3cd; color: #856404; }
.status-rescheduled { background: #d1ecf1; color: #0c5460; }

.btn-restore {
  background: #28a745;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 5px;
}

.btn-delete-permanent {
  background: #dc3545;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  let currentActiveTable = 'complaintRecords';
  let archiveData = {
    complaintRecords: [],
    complaintAppointments: [],
    complaintAnecdotals: []
  };

  // ==================== TABLE NAVIGATION ====================
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
      currentActiveTable = key;
    });
  });

  // ==================== SELECT ALL FUNCTIONALITY ====================
  document.getElementById('selectAll').addEventListener('change', function() {
    const activeTable = getActiveTable();
    const checkboxes = document.querySelectorAll(`#${activeTable} .rowCheckbox`);
    checkboxes.forEach(cb => cb.checked = this.checked);
  });

  // ==================== ARCHIVE MODAL FUNCTIONALITY ====================

  // Open Archive Modal
  document.getElementById('archiveBtn').addEventListener('click', function() {
    loadArchiveData();
    document.getElementById('archiveModal').style.display = 'flex';
  });

  // Close Archive Modal
  document.getElementById('closeArchiveModal').addEventListener('click', closeArchiveModal);
  document.getElementById('closeArchiveModalBtn').addEventListener('click', closeArchiveModal);

  function closeArchiveModal() {
    document.getElementById('archiveModal').style.display = 'none';
  }

  // Archive Tabs
  document.querySelectorAll('.archive-tab').forEach(tab => {
    tab.addEventListener('click', function() {
      // Remove active class from all tabs
      document.querySelectorAll('.archive-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.archive-table-wrapper').forEach(t => t.classList.remove('active'));
      
      // Add active class to clicked tab
      this.classList.add('active');
      const tabId = this.getAttribute('data-tab');
      document.getElementById(tabId).classList.add('active');
    });
  });

  // ==================== CLEARED & TRASH FUNCTIONALITY ====================

  // Cleared Button
  document.getElementById('clearedBtn').addEventListener('click', function() {
    const selectedItems = getSelectedItems();
    if (selectedItems.length === 0) {
      alert('Please select at least one record to mark as cleared.');
      return;
    }

    if (confirm('Are you sure you want to mark the selected records as cleared?')) {
      updateRecordsStatus(selectedItems, 'cleared');
    }
  });

  // Move to Trash Button
  document.getElementById('moveToTrashBtn').addEventListener('click', function() {
    const selectedItems = getSelectedItems();
    if (selectedItems.length === 0) {
      alert('Please select at least one record to move to trash.');
      return;
    }

    if (confirm('Are you sure you want to move the selected records to trash?')) {
      updateRecordsStatus(selectedItems, 'inactive');
    }
  });

  // ==================== UTILITY FUNCTIONS ====================

  function getSelectedItems() {
    const activeTable = getActiveTable();
    const checkboxes = document.querySelectorAll(`#${activeTable} .rowCheckbox:checked`);
    
    return Array.from(checkboxes).map(checkbox => {
      const row = checkbox.closest('tr');
      return {
        type: checkbox.dataset.type,
        id: checkbox.dataset.id,
        row: row
      };
    });
  }

  function getActiveTable() {
    if (document.getElementById('complaintRecordsTable').style.display !== 'none') {
      return 'complaintRecordsTable';
    } else if (document.getElementById('complaintAppointmentsTable').style.display !== 'none') {
      return 'complaintAppointmentsTable';
    } else {
      return 'complaintAnecdotalsTable';
    }
  }

  async function updateRecordsStatus(selectedItems, status) {
    try {
      const requests = selectedItems.map(item => {
        let url, data;
        
        switch(item.type) {
          case 'complaint':
            url = `/prefect/complaints/update-status/${item.id}`;
            data = { status: status };
            break;
          case 'appointment':
            url = `/prefect/complaint-appointments/update-status/${item.id}`;
            data = { status: status };
            break;
          case 'anecdotal':
            url = `/prefect/complaint-anecdotals/update-status/${item.id}`;
            data = { status: status };
            break;
        }

        return fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(data)
        });
      });

      const responses = await Promise.all(requests);
      const results = await Promise.all(responses.map(r => r.json()));

      // Check if all requests were successful
      const allSuccess = results.every(result => result.success);
      
      if (allSuccess) {
        // Remove rows from main table
        selectedItems.forEach(item => {
          if (item.row) {
            item.row.remove();
          }
        });

        alert(`Successfully updated ${selectedItems.length} record(s) to ${status}.`);
        
        // Reload archive data if archive modal is open
        if (document.getElementById('archiveModal').style.display === 'flex') {
          loadArchiveData();
        }
      } else {
        alert('Some records could not be updated. Please try again.');
      }

    } catch (error) {
      console.error('Error updating records:', error);
      alert('Error updating records. Please try again.');
    }
  }

  // ==================== ARCHIVE DATA MANAGEMENT ====================

  async function loadArchiveData() {
    try {
      // Load complaint records archive
      const complaintResponse = await fetch('/prefect/complaints/archive', {
        headers: { 'X-CSRF-TOKEN': csrfToken }
      });
      archiveData.complaintRecords = await complaintResponse.json();

      // Load appointments archive
      const appointmentResponse = await fetch('/prefect/complaint-appointments/archive', {
        headers: { 'X-CSRF-TOKEN': csrfToken }
      });
      archiveData.complaintAppointments = await appointmentResponse.json();

      // Load anecdotals archive
      const anecdotalResponse = await fetch('/prefect/complaint-anecdotals/archive', {
        headers: { 'X-CSRF-TOKEN': csrfToken }
      });
      archiveData.complaintAnecdotals = await anecdotalResponse.json();

      renderArchiveTables();
      setupArchiveSearch();

    } catch (error) {
      console.error('Error loading archive data:', error);
    }
  }

  function renderArchiveTables() {
    renderComplaintRecordsArchive();
    renderComplaintAppointmentsArchive();
    renderComplaintAnecdotalsArchive();
  }

  function renderComplaintRecordsArchive() {
    const tbody = document.getElementById('complaintRecordsArchiveBody');
    tbody.innerHTML = '';

    archiveData.complaintRecords.forEach(record => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td><input type="checkbox" class="archiveCheckbox" data-type="complaintRecords" data-id="${record.complaints_id}"></td>
        <td>${record.complaints_id}</td>
        <td>${record.complainant_fname} ${record.complainant_lname}</td>
        <td>${record.respondent_fname} ${record.respondent_lname}</td>
        <td>${record.offense_type}</td>
        <td>${record.sanction_consequences}</td>
        <td>${record.complaints_incident}</td>
        <td>${record.complaints_date}</td>
        <td>${record.complaints_time}</td>
        <td><span class="status-badge status-${record.status}">${record.status}</span></td>
        <td>
          <button class="btn-restore" onclick="restoreRecord('complaintRecords', ${record.complaints_id})">Restore</button>
          <button class="btn-delete-permanent" onclick="deletePermanently('complaintRecords', ${record.complaints_id})">Delete</button>
        </td>
      `;
      tbody.appendChild(row);
    });
  }

  function renderComplaintAppointmentsArchive() {
    const tbody = document.getElementById('complaintAppointmentsArchiveBody');
    tbody.innerHTML = '';

    archiveData.complaintAppointments.forEach(record => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td><input type="checkbox" class="archiveCheckbox" data-type="complaintAppointments" data-id="${record.comp_app_id}"></td>
        <td>${record.comp_app_id}</td>
        <td>${record.complainant_fname} ${record.complainant_lname}</td>
        <td>${record.respondent_fname} ${record.respondent_lname}</td>
        <td><span class="status-badge status-${record.comp_app_status}">${record.comp_app_status}</span></td>
        <td>${record.comp_app_date}</td>
        <td>${record.comp_app_time}</td>
        <td><span class="status-badge status-${record.status}">${record.status}</span></td>
        <td>
          <button class="btn-restore" onclick="restoreRecord('complaintAppointments', ${record.comp_app_id})">Restore</button>
          <button class="btn-delete-permanent" onclick="deletePermanently('complaintAppointments', ${record.comp_app_id})">Delete</button>
        </td>
      `;
      tbody.appendChild(row);
    });
  }

  function renderComplaintAnecdotalsArchive() {
    const tbody = document.getElementById('complaintAnecdotalsArchiveBody');
    tbody.innerHTML = '';

    archiveData.complaintAnecdotals.forEach(record => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td><input type="checkbox" class="archiveCheckbox" data-type="complaintAnecdotals" data-id="${record.comp_anec_id}"></td>
        <td>${record.comp_anec_id}</td>
        <td>${record.complainant_fname} ${record.complainant_lname}</td>
        <td>${record.respondent_fname} ${record.respondent_lname}</td>
        <td>${record.comp_anec_solution ? record.comp_anec_solution.substring(0, 50) + '...' : 'N/A'}</td>
        <td>${record.comp_anec_recommendation ? record.comp_anec_recommendation.substring(0, 50) + '...' : 'N/A'}</td>
        <td>${record.comp_anec_date}</td>
        <td>${record.comp_anec_time}</td>
        <td><span class="status-badge status-${record.status}">${record.status}</span></td>
        <td>
          <button class="btn-restore" onclick="restoreRecord('complaintAnecdotals', ${record.comp_anec_id})">Restore</button>
          <button class="btn-delete-permanent" onclick="deletePermanently('complaintAnecdotals', ${record.comp_anec_id})">Delete</button>
        </td>
      `;
      tbody.appendChild(row);
    });
  }

  // ==================== ARCHIVE ACTIONS ====================

  // Restore Selected
  document.getElementById('restoreSelectedBtn').addEventListener('click', function() {
    const selectedArchiveItems = getSelectedArchiveItems();
    if (selectedArchiveItems.length === 0) {
      alert('Please select at least one record to restore.');
      return;
    }

    if (confirm('Are you sure you want to restore the selected records?')) {
      restoreSelectedRecords(selectedArchiveItems);
    }
  });

  // Delete Permanently
  document.getElementById('deletePermanentlyBtn').addEventListener('click', function() {
    const selectedArchiveItems = getSelectedArchiveItems();
    if (selectedArchiveItems.length === 0) {
      alert('Please select at least one record to delete permanently.');
      return;
    }

    if (confirm('Are you sure you want to permanently delete the selected records? This action cannot be undone.')) {
      deleteSelectedPermanently(selectedArchiveItems);
    }
  });

  function getSelectedArchiveItems() {
    const checkboxes = document.querySelectorAll('.archiveCheckbox:checked');
    return Array.from(checkboxes).map(checkbox => ({
      type: checkbox.dataset.type,
      id: checkbox.dataset.id
    }));
  }

  async function restoreSelectedRecords(selectedItems) {
    try {
      const requests = selectedItems.map(item => {
        let url = '';
        switch(item.type) {
          case 'complaintRecords':
            url = `/prefect/complaints/restore/${item.id}`;
            break;
          case 'complaintAppointments':
            url = `/prefect/complaint-appointments/restore/${item.id}`;
            break;
          case 'complaintAnecdotals':
            url = `/prefect/complaint-anecdotals/restore/${item.id}`;
            break;
        }

        return fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        });
      });

      const responses = await Promise.all(requests);
      const results = await Promise.all(responses.map(r => r.json()));

      const allSuccess = results.every(result => result.success);
      
      if (allSuccess) {
        alert(`Successfully restored ${selectedItems.length} record(s).`);
        loadArchiveData(); // Reload archive data
        // You might want to reload the main page here to show restored records
        // location.reload();
      } else {
        alert('Some records could not be restored. Please try again.');
      }

    } catch (error) {
      console.error('Error restoring records:', error);
      alert('Error restoring records. Please try again.');
    }
  }

  async function deleteSelectedPermanently(selectedItems) {
    try {
      const requests = selectedItems.map(item => {
        let url = '';
        switch(item.type) {
          case 'complaintRecords':
            url = `/prefect/complaints/delete-permanent/${item.id}`;
            break;
          case 'complaintAppointments':
            url = `/prefect/complaint-appointments/delete-permanent/${item.id}`;
            break;
          case 'complaintAnecdotals':
            url = `/prefect/complaint-anecdotals/delete-permanent/${item.id}`;
            break;
        }

        return fetch(url, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        });
      });

      const responses = await Promise.all(requests);
      const results = await Promise.all(responses.map(r => r.json()));

      const allSuccess = results.every(result => result.success);
      
      if (allSuccess) {
        alert(`Successfully permanently deleted ${selectedItems.length} record(s).`);
        loadArchiveData(); // Reload archive data
      } else {
        alert('Some records could not be deleted. Please try again.');
      }

    } catch (error) {
      console.error('Error deleting records:', error);
      alert('Error deleting records. Please try again.');
    }
  }

  // Global functions for individual actions
  window.restoreRecord = async function(type, id) {
    if (confirm('Are you sure you want to restore this record?')) {
      try {
        let url = '';
        switch(type) {
          case 'complaintRecords':
            url = `/prefect/complaints/restore/${id}`;
            break;
          case 'complaintAppointments':
            url = `/prefect/complaint-appointments/restore/${id}`;
            break;
          case 'complaintAnecdotals':
            url = `/prefect/complaint-anecdotals/restore/${id}`;
            break;
        }

        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        });

        const result = await response.json();

        if (result.success) {
          alert('Record restored successfully!');
          loadArchiveData();
        } else {
          alert('Error restoring record: ' + result.message);
        }

      } catch (error) {
        console.error('Error restoring record:', error);
        alert('Error restoring record. Please try again.');
      }
    }
  };

  window.deletePermanently = async function(type, id) {
    if (confirm('Are you sure you want to permanently delete this record? This action cannot be undone.')) {
      try {
        let url = '';
        switch(type) {
          case 'complaintRecords':
            url = `/prefect/complaints/delete-permanent/${id}`;
            break;
          case 'complaintAppointments':
            url = `/prefect/complaint-appointments/delete-permanent/${id}`;
            break;
          case 'complaintAnecdotals':
            url = `/prefect/complaint-anecdotals/delete-permanent/${id}`;
            break;
        }

        const response = await fetch(url, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        });

        const result = await response.json();

        if (result.success) {
          alert('Record permanently deleted!');
          loadArchiveData();
        } else {
          alert('Error deleting record: ' + result.message);
        }

      } catch (error) {
        console.error('Error deleting record:', error);
        alert('Error deleting record. Please try again.');
      }
    }
  };

  function setupArchiveSearch() {
    const searchInput = document.getElementById('archiveSearchInput');
    const statusFilter = document.getElementById('archiveStatusFilter');

    function filterArchiveTables() {
      const searchTerm = searchInput.value.toLowerCase();
      const statusValue = statusFilter.value;

      // Filter each table
      filterTable('complaintRecordsArchiveBody', searchTerm, statusValue);
      filterTable('complaintAppointmentsArchiveBody', searchTerm, statusValue);
      filterTable('complaintAnecdotalsArchiveBody', searchTerm, statusValue);
    }

    function filterTable(tableBodyId, searchTerm, statusValue) {
      const tbody = document.getElementById(tableBodyId);
      const rows = tbody.getElementsByTagName('tr');

      for (let row of rows) {
        const rowText = row.textContent.toLowerCase();
        const statusCell = row.querySelector('.status-badge');
        const rowStatus = statusCell ? statusCell.textContent.toLowerCase() : '';

        const matchesSearch = !searchTerm || rowText.includes(searchTerm);
        const matchesStatus = !statusValue || rowStatus.includes(statusValue);

        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
      }
    }

    searchInput.addEventListener('input', filterArchiveTables);
    statusFilter.addEventListener('change', filterArchiveTables);
  }

  // ==================== APPOINTMENT & ANECDOTAL FUNCTIONALITY ====================

  // Set Appointment Button
  document.getElementById('setAppointmentBtn').addEventListener('click', function() {
    const selectedCheckboxes = document.querySelectorAll('#complaintRecordsTable .rowCheckbox:checked');

    if (!selectedCheckboxes.length) {
      alert('Please select at least one complaint from Complaint Records to schedule an appointment.');
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

  // Create Anecdotal Button
  document.getElementById('createAnecdotalBtn').addEventListener('click', function() {
    const selectedCheckboxes = document.querySelectorAll('#complaintRecordsTable .rowCheckbox:checked');

    if (!selectedCheckboxes.length) {
      alert('Please select at least one complaint from Complaint Records to create anecdotal record.');
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

  // ==================== EDIT MODAL FUNCTIONALITY ====================

  // Separate modals for each table
  const complaintModal = document.getElementById('editComplaintModal');
  const appointmentModal = document.getElementById('editAppointmentModal');
  const anecdotalModal = document.getElementById('editAnecdotalModal');

  // Edit Complaint Records
  document.querySelectorAll('.editComplaintBtn').forEach(btn => {
    btn.addEventListener('click', e => {
      const row = e.target.closest('tr');
      const complaintId = row.dataset.complaintId;

      // Populate form with current data
      document.getElementById('edit_complaint_id').value = complaintId;
      document.getElementById('edit_incident').value = row.dataset.incident;
      document.getElementById('edit_offense_type').value = row.dataset.offenseType;
      document.getElementById('edit_sanction').value = row.dataset.sanction;
      document.getElementById('edit_complaint_date').value = row.dataset.date;
      document.getElementById('edit_complaint_time').value = convertTo24Hour(row.dataset.time);

      // Set form action
      document.getElementById('editComplaintForm').action = `/prefect/complaints/update/${complaintId}`;

      // Show modal
      complaintModal.style.display = 'flex';
    });
  });

  // Edit Appointment Records
  document.querySelectorAll('.editAppointmentBtn').forEach(btn => {
    btn.addEventListener('click', e => {
      const row = e.target.closest('tr');
      const appId = row.dataset.appId;

      // Populate form with current data
      document.getElementById('edit_appointment_id').value = appId;
      document.getElementById('edit_app_status').value = row.dataset.status;
      document.getElementById('edit_app_date').value = row.dataset.date;
      document.getElementById('edit_app_time').value = convertTo24Hour(row.dataset.time);

      // Set form action
      document.getElementById('editAppointmentForm').action = `/prefect/complaint-appointments/update/${appId}`;

      // Show modal
      appointmentModal.style.display = 'flex';
    });
  });

  // Edit Anecdotal Records
  document.querySelectorAll('.editAnecdotalBtn').forEach(btn => {
    btn.addEventListener('click', e => {
      const row = e.target.closest('tr');
      const anecId = row.dataset.anecId;

      // Populate form with current data
      document.getElementById('edit_anecdotal_id').value = anecId;
      document.getElementById('edit_solution').value = row.dataset.solution;
      document.getElementById('edit_recommendation').value = row.dataset.recommendation;
      document.getElementById('edit_anec_date').value = row.dataset.date;
      document.getElementById('edit_anec_time').value = convertTo24Hour(row.dataset.time);

      // Set form action
      document.getElementById('editAnecdotalForm').action = `/prefect/complaint-anecdotals/update/${anecId}`;

      // Show modal
      anecdotalModal.style.display = 'flex';
    });
  });

  // Close modal functions
  document.getElementById('closeComplaintEditModal').addEventListener('click', () => complaintModal.style.display = 'none');
  document.getElementById('cancelComplaintEditBtn').addEventListener('click', () => complaintModal.style.display = 'none');

  document.getElementById('closeAppointmentEditModal').addEventListener('click', () => appointmentModal.style.display = 'none');
  document.getElementById('cancelAppointmentEditBtn').addEventListener('click', () => appointmentModal.style.display = 'none');

  document.getElementById('closeAnecdotalEditModal').addEventListener('click', () => anecdotalModal.style.display = 'none');
  document.getElementById('cancelAnecdotalEditBtn').addEventListener('click', () => anecdotalModal.style.display = 'none');

  // Close appointment and anecdotal modals
  document.getElementById('closeAppointmentModal').addEventListener('click', () => document.getElementById('appointmentModal').style.display = 'none');
  document.getElementById('closeAnecdotalModal').addEventListener('click', () => document.getElementById('createAnecdotalModal').style.display = 'none');
  document.getElementById('cancelAppointmentBtn').addEventListener('click', () => document.getElementById('appointmentModal').style.display = 'none');
  document.getElementById('cancelAnecdotalBtn').addEventListener('click', () => document.getElementById('createAnecdotalModal').style.display = 'none');

  // Form submission handlers
  document.getElementById('editComplaintForm').addEventListener('submit', handleFormSubmit);
  document.getElementById('editAppointmentForm').addEventListener('submit', handleFormSubmit);
  document.getElementById('editAnecdotalForm').addEventListener('submit', handleFormSubmit);

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

  // Utility Functions
  function convertTo24Hour(t) {
    if (!t.includes(' ')) return t;
    const [time, mod] = t.split(' ');
    let [h, m] = time.split(':'); h = +h;
    if (mod === 'PM' && h !== 12) h += 12;
    if (mod === 'AM' && h === 12) h = 0;
    return `${h.toString().padStart(2,'0')}:${m}`;
  }

  async function handleFormSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    try {
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      submitBtn.disabled = true;

      const response = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        alert('Record updated successfully!');
        // Close the appropriate modal based on form ID
        if (form.id === 'editComplaintForm') {
          complaintModal.style.display = 'none';
        } else if (form.id === 'editAppointmentForm') {
          appointmentModal.style.display = 'none';
        } else if (form.id === 'editAnecdotalForm') {
          anecdotalModal.style.display = 'none';
        }
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
      alert('Error updating record.');
    } finally {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  }

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