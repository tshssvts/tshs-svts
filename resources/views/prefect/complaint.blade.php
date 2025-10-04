@extends('prefect.layout')

@section('content')
<div class="main-container">

  <!-- ✅ Toolbar -->
  <div class="toolbar">
    <h2>Complaint Management</h2>
    <div class="actions">
      <input type="search" placeholder="🔍 Search by student name or ID..." id="searchInput">
      <a href="{{ route('complaints.create') }}" class="btn-primary" id="createBtn">
        <i class="fas fa-plus"></i> Add Complaint
      </a>
      <button class="btn-secondary" id="createAnecBtn">📝 Create Anecdotal</button>
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
      <button class="btn-appointment">Set Appointment</button>
      <button class="btn-cleared">Cleared</button>
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
            <td><input type="checkbox" class="rowCheckbox"></td>
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
            <td>{{ $anec->comp_anec_solution }}</td>
            <td>{{ $anec->comp_anec_recommendation }}</td>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
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
    openModal(`/prefect/complaints/update/${r.dataset.complaintId}`, {
      id: r.dataset.complaintId,
      details: r.dataset.incident,
      date: r.dataset.date,
      time: r.dataset.time
    });
  }));

  document.querySelectorAll('.editAppointmentBtn').forEach(btn => btn.addEventListener('click', e => {
    const r = e.target.closest('tr');
    openModal(`/prefect/complaint-appointments/update/${r.dataset.appId}`, {
      id: r.dataset.appId,
      details: r.dataset.status,
      date: r.dataset.date,
      time: r.dataset.time
    });
  }));

  document.querySelectorAll('.editAnecdotalBtn').forEach(btn => btn.addEventListener('click', e => {
    const r = e.target.closest('tr');
    openModal(`/prefect/complaint-anecdotals/update/${r.dataset.anecId}`, {
      id: r.dataset.anecId,
      details: `${r.dataset.solution} | ${r.dataset.recommendation}`,
      date: r.dataset.date,
      time: r.dataset.time
    });
  }));

  [close, cancel].forEach(b => b.addEventListener('click', () => modal.style.display = 'none'));

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
});
</script>
@endsection
