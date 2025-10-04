@extends('prefect.layout')

@section('content')
<div class="main-container">

  <!-- ✅ Toolbar -->
  <div class="toolbar">
    <h2>Violation Management</h2>
    <div class="actions">
      <input type="search" placeholder="🔍 Search by student name or ID..." id="searchInput">
      <a href="{{ route('violations.create') }}" class="btn-primary" id="createBtn">
        <i class="fas fa-plus"></i> Add Violation
      </a>
      <button class="btn-secondary" id="createAnecBtn">📝 Create Anecdotal</button>
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
      <button class="btn-cleared" id="moveToTrashBtn">Cleared</button>
      <button class="btn-danger" id="moveToTrashBtn">🗑️ Move Selected to Trash</button>
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
            <td><input type="checkbox" class="rowCheckbox"></td>
            <td>{{ $violation->violation_id }}</td>
            <td>{{ $violation->student->student_fname }} {{ $violation->student->student_lname }}</td>
            <td>{{ $violation->violation_incident }}</td>
            <td>{{ $violation->offense->offense_type }}</td>
            <td>{{ $violation->offense->sanction_consequences }}</td>
            <td>{{ $violation->violation_date }}</td>
            <td>{{ \Carbon\Carbon::parse($violation->violation_time)->format('h:i A') }}</td>
            <td>{{ $violation->status }}</td>
            <td><button class="btn-primary editViolationBtn">✏️ Edit</button></td>
          </tr>
          @empty
          <tr class="no-data-row">
            <td colspan="9" style="text-align:center;">No violations found</td>
          </tr>
          @endforelse
        </tbody>
      </table>

      <div class="pagination-wrapper">
        <div class="pagination-summary">
          @if($violations instanceof \Illuminate\Pagination\LengthAwarePaginator)
            Showing {{ $violations->firstItem() ?? 0 }} to {{ $violations->lastItem() ?? 0 }} of {{ $violations->total() ?? 0 }} record(s)
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
            <th>ID</th>
            <th>Student Name</th>
            <th>Status</th>
            <th>Date</th>
            <th>Time</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($vappointments as $appt)
          <tr
            data-app-id="{{ $appt->violation_app_id }}"
            data-status="{{ $appt->violation_app_status }}"
            data-date="{{ $appt->violation_app_date }}"
            data-time="{{ \Carbon\Carbon::parse($appt->violation_app_time)->format('h:i A') }}"
          >
            <td>{{ $appt->violation_app_id }}</td>
            <td>
              {{ $appt->violation->student->student_fname ?? 'N/A' }}
              {{ $appt->violation->student->student_lname ?? '' }}
            </td>
            <td>{{ $appt->violation_app_status }}</td>
            <td>{{ $appt->violation_app_date }}</td>
            <td>{{ \Carbon\Carbon::parse($appt->violation_app_time)->format('h:i A') }}</td>
            <td><button class="btn-primary editAppointmentBtn">✏️ Edit</button></td>
          </tr>
          @empty
          <tr><td colspan="6" style="text-align:center;">No appointments found</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- 📝 VIOLATION ANECDOTALS TABLE -->
    <div id="violationAnecdotalsTable" class="table-wrapper" style="display:none;">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Student Name</th>
            <th>Solution</th>
            <th>Recommendation</th>
            <th>Date</th>
            <th>Time</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($vanecdotals as $anec)
          <tr
            data-anec-id="{{ $anec->violation_anec_id }}"
            data-solution="{{ $anec->violation_anec_solution }}"
            data-recommendation="{{ $anec->violation_anec_recommendation }}"
            data-date="{{ $anec->violation_anec_date }}"
            data-time="{{ \Carbon\Carbon::parse($anec->violation_anec_time)->format('h:i A') }}"
          >
            <td>{{ $anec->violation_anec_id }}</td>
            <td>
              {{ $anec->violation->student->student_fname ?? 'N/A' }}
              {{ $anec->violation->student->student_lname ?? '' }}
            </td>
            <td>{{ $anec->violation_anec_solution }}</td>
            <td>{{ $anec->violation_anec_recommendation }}</td>
            <td>{{ $anec->violation_anec_date }}</td>
            <td>{{ \Carbon\Carbon::parse($anec->violation_anec_time)->format('h:i A') }}</td>
            <td><button class="btn-primary editAnecdotalBtn">✏️ Edit</button></td>
          </tr>
          @empty
          <tr><td colspan="7" style="text-align:center;">No anecdotal records found</td></tr>
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
</div>

<script>
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
  Object.keys(sections).forEach(key => {
    document.getElementById(key).addEventListener('click', e => {
      e.preventDefault();
      Object.values(sections).forEach(sec => sec.style.display = 'none');
      sections[key].style.display = 'block';
    });
  });
});
</script>
@endsection
