<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Violation Reports</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- Include html2pdf library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
    /* ===========================
   🎨 Prefect Dashboard CSS
   =========================== */

/* ===== Global Reset ===== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

body {
  display: flex;
  background: #f5f5f5;
  color: #222;
  height: 100vh;
  overflow: hidden;
}

/* ===========================
   SIDEBAR STYLES
   =========================== */
.sidebar {
      overflow-y: auto;    /* Makes content scrollable vertically */
  width: 230px;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  background: linear-gradient(135deg, #2b0000, #4b0000, #2c2c2c);
  color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 25px 15px;
  box-shadow: 2px 0 6px rgba(0, 0, 0, 0.3);
}

/* Optional: customize scrollbar (for WebKit browsers) */
.sidebar::-webkit-scrollbar {
  width: 8px;
}

.sidebar::-webkit-scrollbar-thumb {
  background-color: #550000;
  border-radius: 4px;
}

.sidebar::-webkit-scrollbar-track {
  background-color: #2b0000;
}

/* Logo */
.sidebar img {
  width: 100px;
  height: 100px;
  margin-bottom: 10px;
  border-radius: 50%;
  object-fit: cover;
}

/* Title */
.sidebar h2 {
      font-size: 1.5rem;
  color: white;
  margin-bottom: 25px;
  letter-spacing: 1px;
  text-transform: uppercase;

    /* ✨ Add a line below */
  border-bottom: 2px solid rgba(255, 255, 255, 0.4);
  padding-bottom: 8px;
  width: 100%;              /* adjust line width */
  text-align: center;      /* center the text and line */
}

/* Menu List */
.sidebar ul {
  list-style: none;
  width: 100%;
}

/* Menu Item */
.sidebar ul li {
  width: 100%;
  margin: 5px 0;
  border-radius: 8px;
  transition: all 0.3s ease;
  text-align: left;
}

/* Active Item */
.sidebar ul li.active,
.sidebar ul li:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateX(5px);
}

/* ====== Fixed Link Layout ====== */
.sidebar ul li a {
      font-size: 1.1rem;
  padding: 14px 18px;
  display: flex; /* Align icon + text */
  align-items: center;
  text-decoration: none;
  color: #fff;

  gap: 12px;
  line-height: 1.4;
  transition: 0.3s ease;
  font-weight: 700;


}

/* Text beside icon wraps properly */
.sidebar ul li a span {
  white-space: normal;
  word-break: break-word;
  flex: 1;
}

/* Icons */
.sidebar ul li i {
      font-size: 1.2rem;
  width: 20px;
  text-align: center;
  flex-shrink: 0; /* prevent icon from shrinking */
}

/* Hover Effects */
.sidebar ul li a:hover {
  color: #ffb3b3;
}

/* Logout */
.sidebar ul li:last-child {
  margin-top: auto;
  background: rgba(255, 0, 0, 0.2);
  border: 1px solid rgba(255, 0, 0, 0.3);
  cursor: pointer;
  justify-content: block;
    display: flex; /* Align icon + text */

}

.sidebar ul li:last-child:hover {
  background: rgba(255, 0, 0, 0.4);
}

/* ===========================
   MAIN CONTENT
   =========================== */
.main-content {
  margin-left: 230px;
  margin-top: 80px; /* equal to header height */
  width: calc(100% - 230px);
  height: calc(100vh - 80px);
  display: flex;
  flex-direction: column;
  background: #fafafa;
  overflow-y: auto;
  padding: 20px;
}

/* Scrollbar */
.main-content::-webkit-scrollbar {
  width: 8px;
}

.main-content::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 4px;
}

/* ===========================
   HEADER SECTION
   =========================== */
.main-header {
  position: fixed;
  top: 0;
  left: 230px; /* align beside sidebar */
  width: calc(100% - 230px); /* fill the rest of the screen */
  height: 80px; /* fixed height */
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #4b0000;
  color: #fff;
  padding: 0 25px; /* removed top/bottom padding to control height */
  border-bottom: 3px solid #2b0000;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  z-index: 100;
}

/* Header Left */
.header-left h2 {
  font-size: 1.3rem !important;
  font-weight: 600;
  letter-spacing: 0.5px;
  color: white;
  margin-top: 10px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* ✅ Clean & modern */
}

/* Header Right */
.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
  position: relative;
}

/* User Info */
.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  padding: 5px 10px;
  border-radius: 8px;
  transition: 0.3s ease;
}

.user-info:hover {
  background: rgba(255, 255, 255, 0.1);
}

/* User Image */
.user-info img {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  object-fit: cover;
}

/* Username */
.user-info span {
  font-weight: 500;
}

/* Dropdown Arrow */
.user-info i {
  font-size: 0.9rem;
}

/* Profile Dropdown */
.profile-dropdown {
  display: none;
  position: absolute;
  top: 55px;
  right: 0;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  min-width: 150px;
  z-index: 200;
}

.profile-dropdown a {
  display: block;
  padding: 10px 15px;
  color: #333;
  text-decoration: none;
  transition: background 0.3s;
}

.profile-dropdown a:hover {
  background: #eee;
}

/* Toggle Dropdown (JS Controlled) */
.profile-dropdown.active {
  display: block;
}

/* ===========================
   RESPONSIVE DESIGN
   =========================== */
@media (max-width: 768px) {
  .sidebar {
    width: 200px;
  }

  .main-content {
    margin-left: 200px;
    margin-top: 70px; /* equal to header height */
    width: calc(100% - 200px);
    height: calc(100vh - 70px);
  }

  .sidebar ul li a {
    font-size: 0.9rem;
  }
}

@media (max-width: 600px) {
  .sidebar {
    position: relative;
    width: 100%;
    height: auto;
    flex-direction: row;
    justify-content: space-around;
    padding: 10px;
  }

  .sidebar h2,
  .sidebar img {
    display: none;
  }

  .sidebar ul {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
  }

  .main-content {
    margin-left: 0;
    width: 100%;
  }

  .main-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
}

/* (styles unchanged from your working version) */
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif;font-weight:bold;transition:all .2s ease-in-out;}
body {
  display: flex;
  background: #f9f9f9;
  color: #111;
}

/* ===========================
   REPORTS TITLE - WITH BROWN LINE
   =========================== */
.reports-title {
    font-size: 2rem;
    color: #4b0000;
    font-weight: 600;
    text-align: center;
    margin: 20px 0 30px 0;
    padding: 0 20px 15px 20px; /* Added bottom padding */
    border-bottom: 3px solid #8B4513; /* Brown line */
}
/* ===========================
   REPORT BOXES GRID
   =========================== */
.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  width: 100%;
}

.report-box {
  background: #fff;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 6px rgba(0,0,0,.1);
  transition: transform .2s, box-shadow .2s;
  cursor: pointer;
  min-height: 120px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
}

.report-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0,0,0,.2);
}

.report-box i {
  font-size: 28px;
  margin-bottom: 12px;
  color: inherit;
}

.report-box h3 {
  margin: 0;
  font-size: 16px;
  line-height: 1.4;
}

/* ===========================
   MODAL STYLES
   =========================== */
.modal {
  display: none;
  position: fixed;
  z-index: 100;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background: rgba(0,0,0,.5);
}

.modal-content {
  background: #fff;
  margin: 50px auto;
  padding: 20px;
  border-radius: 10px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  position: relative;
}

.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}

.close:hover {
  color: black;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

th, td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: left;
}

th {
  background: #2980b9;
  color: #fff;
  position: sticky;
  top: 0;
}

tr:nth-child(even) {
  background: #f2f2f2;
}

.toolbar {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
  flex-wrap: wrap;
  align-items: center;
}

.toolbar input {
  padding: 6px 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  margin-right: 5px;
  flex: 1;
  min-width: 200px;
}

.toolbar button {
  padding: 8px 12px;
  border-radius: 5px;
  border: none;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: all 0.3s ease;
}

.toolbar button.btn-warning {
  background: #f39c12;
  color: #fff;
}

.toolbar button.btn-warning:hover {
  background: #d68910;
}

.toolbar button.btn-success {
  background: #27ae60;
  color: #fff;
}

.toolbar button.btn-success:hover {
  background: #219653;
}

@media screen and (max-width:768px) {
  .reports-grid {
    grid-template-columns: 1fr;
    padding: 15px;
  }

  .toolbar {
    flex-direction: column;
  }

  .toolbar input {
    width: 100%;
    margin-bottom: 5px;
  }
}

/* Colored report boxes */
.report-box:nth-child(1) { background: #ff6b6b; color: #fff; }   /* red */
.report-box:nth-child(2) { background: #4ecdc4; color: #fff; }   /* teal */
.report-box:nth-child(3) { background: #45b7d1; color: #fff; }   /* blue */
.report-box:nth-child(4) { background: #feca57; color: #111; }   /* yellow */
.report-box:nth-child(5) { background: #5f27cd; color: #fff; }   /* purple */
.report-box:nth-child(6) { background: #10ac84; color: #fff; }   /* green */
.report-box:nth-child(7) { background: #ff9f43; color: #111; }   /* orange */
.report-box:nth-child(8) { background: #1dd1a1; color: #fff; }   /* mint */
.report-box:nth-child(9) { background: #576574; color: #fff; }   /* gray */
.report-box:nth-child(10){ background: #341f97; color: #fff; }   /* dark purple */
.report-box:nth-child(11){ background: #54a0ff; color: #fff; }   /* sky blue */
.report-box:nth-child(12){ background: #00d2d3; color: #fff; }   /* aqua */
.report-box:nth-child(13){ background: #ee5253; color: #fff; }   /* coral red */
.report-box:nth-child(14){ background: #2e86de; color: #fff; }   /* cobalt */
.report-box:nth-child(15){ background: #222f3e; color: #fff; }   /* dark */
.report-box:nth-child(16){ background: #ffb142; color: #111; }   /* amber */
.report-box:nth-child(17){ background: #009432; color: #fff; }   /* emerald */
.report-box:nth-child(18){ background: #e58e26; color: #111; }   /* golden */
.report-box:nth-child(19){ background: #c8d6e5; color: #111; }   /* light gray */

/* Logo */
.sidebar img {
  width: 100px;
  height: auto;
  margin: 0 auto 0.5rem;
  display: block;
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
}

/* Loading spinner */
.loading {
  display: none;
  text-align: center;
  padding: 20px;
  color: #666;
}

.loading i {
  font-size: 24px;
  margin-bottom: 10px;
}

/* Notification - Updated to be modal and centered */
.notification {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  padding: 20px 30px;
  border-radius: 8px;
  color: white;
  z-index: 1000;
  display: none;
  box-shadow: 0 4px 20px rgba(0,0,0,0.25);
  min-width: 300px;
  text-align: center;
  font-size: 16px;
  font-weight: 500;
  backdrop-filter: blur(5px);
}

.notification.success {
  background: #27ae60;
}

.notification.error {
  background: #e74c3c;
}

.notification.info {
  background: #3498db;
}

/* Overlay for notification modal */
.notification-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  z-index: 999;
  display: none;
}
</style>
</head>
<body>

<!-- Notification -->
<div id="notification" class="notification"></div>
<div id="notificationOverlay" class="notification-overlay"></div>

<!-- Sidebar -->
<div class="sidebar">
  <img src="/images/Logo.png" alt="Logo">
  <h2>Prefect</h2>
  <ul>
    <li class="{{ request()->routeIs('prefect.dashboard') ? 'active' : '' }}">
      <a href="{{ route('prefect.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</a>
    </li>

    <li class="{{ request()->routeIs('prefect.adviser') ? 'active' : '' }}">
      <a href="{{ route('prefect.adviser') }}"><i class="fas fa-users"></i> Advisers</a>
    </li>

    <li class="{{ request()->routeIs('parent.lists') ? 'active' : '' }}">
      <a href="{{ route('parent.lists') }}"><i class="fas fa-users"></i> Parents</a>
    </li>

    <li class="{{ request()->routeIs('student.management') ? 'active' : '' }}">
      <a href="{{ route('student.management') }}"><i class="fas fa-user-graduate"></i> Students</a>
    </li>

    <li class="{{ request()->routeIs('prefect.violation') ? 'active' : '' }}">
      <a href="{{ route('prefect.violation') }}"><i class="fas fa-book"></i> Violations</a>
    </li>

    <li class="{{ request()->routeIs('prefect.complaints') ? 'active' : '' }}">
      <a href="{{ route('prefect.complaints') }}"><i class="fas fa-comments"></i> Complaints</a>
    </li>

    <li class="{{ request()->routeIs('offenses.sanctions') ? 'active' : '' }}">
      <a href="{{ route('offenses.sanctions') }}"><i class="fas fa-exclamation-triangle"></i> Offense & Sanctions</a>
    </li>

    <li class="{{ request()->routeIs('report.generate') ? 'active' : '' }}">
      <a href="{{ route('report.generate') }}"><i class="fas fa-chart-line"></i> Reports</a>
    </li>


  </ul>
</div>

<!-- ✅ Main content area (for child pages) -->
<main class="main-content">
  <!-- ======= HEADER ======= -->
  <header class="main-header">
    <div class="header-left">
      <h2>Student Violation Tracking System</h2>
    </div>

    <div class="header-right">
      <div class="user-info" onclick="toggleProfileDropdown()">
        <img src="/images/user.jpg" alt="User">
        <span>{{ Auth::user()->name }}</span>
        <i class="fas fa-caret-down"></i>
      </div>
      <div class="profile-dropdown" id="profileDropdown">
        {{-- <a href="{{ route('profile.settings') }}">Profile</a> --}}
      </div>
    </div>
  </header>

  @yield('content')

  <!-- Simple Reports Title (no container, no line) -->
  <h3 class="reports-title">REPORTS</h3>

  <!-- Report Boxes Grid (EXACTLY AS BEFORE) -->
  <div class="reports-grid">
    <div class="report-box" data-modal="modal1"><i class="fas fa-book-open"></i><h3>Anecdotal Records per Complaint Case</h3></div>
    <div class="report-box" data-modal="modal2"><i class="fas fa-book"></i><h3>Anecdotal Records per Violation Case</h3></div>
    <div class="report-box" data-modal="modal3"><i class="fas fa-calendar-check"></i><h3>Appointments Scheduled for Complaints</h3></div>
    <div class="report-box" data-modal="modal4"><i class="fas fa-calendar-alt"></i><h3>Appointments Scheduled for Violation Cases</h3></div>
    <div class="report-box" data-modal="modal5"><i class="fas fa-user-tie"></i><h3>Complaint Records by Adviser</h3></div>
    <div class="report-box" data-modal="modal6"><i class="fas fa-file-alt"></i><h3>Complaint Records with Complainant and Respondent</h3></div>
    <div class="report-box" data-modal="modal7"><i class="fas fa-clock"></i><h3>Complaints Filed within the Last 30 Days</h3></div>
    <div class="report-box" data-modal="modal8"><i class="fas fa-chart-bar"></i><h3>Common Offenses by Frequency</h3></div>
    <div class="report-box" data-modal="modal9"><i class="fas fa-exclamation-triangle"></i><h3>List of Violators with Repeat Offenses</h3></div>
    <div class="report-box" data-modal="modal10"><i class="fas fa-gavel"></i><h3>Offenses and Their Sanction Consequences</h3></div>
    <div class="report-box" data-modal="modal11"><i class="fas fa-phone-alt"></i><h3>Parent Contact Information for Students with Active Violations</h3></div>
    <div class="report-box" data-modal="modal12"><i class="fas fa-chart-line"></i><h3>Sanction Trends Across Time Periods</h3></div>
    <div class="report-box" data-modal="modal13"><i class="fas fa-chalkboard-teacher"></i><h3>Students and Their Class Advisers</h3></div>
    <div class="report-box" data-modal="modal14"><i class="fas fa-user-graduate"></i><h3>Students and Their Parents</h3></div>
    <div class="report-box" data-modal="modal15"><i class="fas fa-user-shield"></i><h3>Students with Both Violation and Complaint Records</h3></div>
    <div class="report-box" data-modal="modal16"><i class="fas fa-user-friends"></i><h3>Students with the Most Violation Records</h3></div>
    <div class="report-box" data-modal="modal17"><i class="fas fa-layer-group"></i><h3>Summary of Violations per Grade Level</h3></div>
    <div class="report-box" data-modal="modal18"><i class="fas fa-users"></i><h3>Violation Records and Assigned Adviser</h3></div>
    <div class="report-box" data-modal="modal19"><i class="fas fa-exclamation-circle"></i><h3>Violation Records with Violator Information</h3></div>
  </div>

  <!-- Modals -->
  @for($i=1; $i<=19; $i++)
  <div id="modal{{ $i }}" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>

      <div class="toolbar">
        <input type="text" placeholder="Search..." oninput="liveSearch('modal{{ $i }}', this.value)">
        <button class="btn btn-success" onclick="exportCSV('modal{{ $i }}')"><i class="fas fa-file-csv"></i> Export CSV</button>
        <button class="btn btn-warning" onclick="printAsPDF('modal{{ $i }}')"><i class="fas fa-print"></i> Print</button>
      </div>

      <div id="loading-{{ $i }}" class="loading">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading report data...</p>
      </div>

      <h2 class="text-xl font-semibold mb-3 text-center">
        @switch($i)
          @case(1) Anecdotal Records per Complaint Case @break
          @case(2) Anecdotal Records per Violation Case @break
          @case(3) Appointments Scheduled for Complaints @break
          @case(4) Appointments Scheduled for Violation Cases @break
          @case(5) Complaint Records by Adviser @break
          @case(6) Complaint Records with Complainant and Respondent @break
          @case(7) Complaints Filed within the Last 30 Days @break
          @case(8) Common Offenses by Frequency @break
          @case(9) List of Violators with Repeat Offenses @break
          @case(10) Offenses and Their Sanction Consequences @break
          @case(11) Parent Contact Information for Students with Active Violations @break
          @case(12) Sanction Trends Across Time Periods @break
          @case(13) Students and Their Class Advisers @break
          @case(14) Students and Their Parents @break
          @case(15) Students with Both Violation and Complaint Records @break
          @case(16) Students with the Most Violation Records @break
          @case(17) Summary of Violations per Grade Level @break
          @case(18) Violation Records and Assigned Adviser @break
          @case(19) Violation Records with Violator Information @break
        @endswitch
      </h2>

      <table id="table-{{ $i }}" class="w-full border-collapse">
        <thead>
          @switch($i)
            @case(1)
            <tr>
              <th>Complainant Name</th>
              <th>Respondent Name</th>
              <th>Solution</th>
              <th>Recommendation</th>
              <th>Date Recorded</th>
              <th>Time Recorded</th>
            </tr>
            @break

            @case(2)
            <tr>
              <th>Student Name</th>
              <th>Solution</th>
              <th>Recommendation</th>
              <th>Date</th>
              <th>Time</th>
            </tr>
            @break

            @case(3)
            <tr>
              <th>Complainant Name</th>
              <th>Respondent Name</th>
              <th>Appointment Date</th>
              <th>Appointment Status</th>
            </tr>
            @break

            @case(4)
            <tr>
              <th>Student Name</th>
              <th>Appointment Date</th>
              <th>Appointment Time</th>
              <th>Appointment Status</th>
            </tr>
            @break

            @case(5)
            <tr>
              <th>Adviser Name</th>
              <th>Complainant Name</th>
              <th>Respondent Name</th>
              <th>Type of Offense</th>
              <th>Complaint Date</th>
              <th>Complaint Time</th>
            </tr>
            @break

            @case(6)
            <tr>
              <th>Complainant Name</th>
              <th>Respondent Name</th>
              <th>Incident Description</th>
              <th>Complaint Date</th>
              <th>Complaint Time</th>
            </tr>
            @break

            @case(7)
            <tr>
              <th>Complainant Name</th>
              <th>Respondent Name</th>
              <th>Offense Type</th>
              <th>Complaint Date</th>
              <th>Complaint Time</th>
            </tr>
            @break

            @case(8)
            <tr>
              <th>Offense Type</th>
              <th>Description</th>
              <th>Total Occurrences</th>
            </tr>
            @break

            @case(9)
            <tr>
              <th>Student Name</th>
              <th>Section</th>
              <th>Grade Level</th>
              <th>Total Violations</th>
              <th>First Violation Date</th>
              <th>Most Recent Violation Date</th>
            </tr>
            @break

            @case(10)
            <tr>
              <th>Offense Type</th>
              <th>Offense Description</th>
              <th>Sanction Consequences</th>
            </tr>
            @break

            @case(11)
            <tr>
              <th>Student Name</th>
              <th>Parent Name</th>
              <th>Parent Contact Info</th>
              <th>Violation Date</th>
              <th>Violation Time</th>
              <th>Violation Status</th>
            </tr>
            @break

            @case(12)
            <tr>
              <th>Offense Type</th>
              <th>Sanction Consequences</th>
              <th>Month and Year</th>
              <th>Number of Sanctions Given</th>
            </tr>
            @break

            @case(13)
            <tr>
              <th>Student Name</th>
              <th>Adviser Name</th>
              <th>Section</th>
              <th>Grade Level</th>
            </tr>
            @break

            @case(14)
            <tr>
              <th>Student Name</th>
              <th>Parent Name</th>
              <th>Parent Contact Info</th>
            </tr>
            @break

            @case(15)
            <tr>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Violation Count</th>
              <th>Complaint Involvement Count</th>
            </tr>
            @break

            @case(16)
            <tr>
              <th>Student Name</th>
              <th>Adviser Section</th>
              <th>Grade Level</th>
              <th>Total Violations</th>
            </tr>
            @break

            @case(17)
            <tr>
              <th>Grade Level</th>
              <th>Offense Type</th>
              <th>Number of Violations</th>
            </tr>
            @break

            @case(18)
            <tr>
              <th>Student Name</th>
              <th>Adviser Name</th>
              <th>Type of Offense</th>
              <th>Violation Date</th>
              <th>Violation Time</th>
              <th>Incident Description</th>
            </tr>
            @break

            @case(19)
            <tr>
              <th>Student Name</th>
              <th>Offense Type</th>
              <th>Sanction</th>
              <th>Incident Description</th>
              <th>Violation Date</th>
              <th>Violation Time</th>
            </tr>
            @break
          @endswitch
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
  @endfor
</main>

<script>
/* dropdown */
document.querySelectorAll('.dropdown-btn').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const container = btn.nextElementSibling;
    document.querySelectorAll('.dropdown-btn').forEach(b=>{ if(b!==btn){ b.classList.remove('active'); b.nextElementSibling.style.display='none';}});
    btn.classList.toggle('active');
    container.style.display = container.style.display === 'block' ? 'none' : 'block';
  });
});

/* Show notification - Updated to be modal and centered */
function showNotification(message, type = 'info') {
  const notification = document.getElementById('notification');
  const overlay = document.getElementById('notificationOverlay');

  notification.textContent = message;
  notification.className = `notification ${type}`;
  notification.style.display = 'block';
  overlay.style.display = 'block';

  // Auto-hide after 3 seconds
  setTimeout(() => {
    notification.style.display = 'none';
    overlay.style.display = 'none';
  }, 3000);
}

/* open modal + fetch */
async function openReportModal(reportId) {
  const modal = document.getElementById(`modal${reportId}`);
  const loading = document.getElementById(`loading-${reportId}`);
  const tableBody = modal.querySelector("tbody");

  modal.style.display = "block";
  loading.style.display = "block";
  tableBody.innerHTML = "";

  try {
    const res = await fetch(`/prefect/reports/data/${reportId}`);
    const data = await res.json();
    console.log("Fetched data:", data);

    if (reportId === 1) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.student_name || 'N/A'}</td>
            <td>${row.offense_type || 'N/A'}</td>
            <td>${row.sanction || 'N/A'}</td>
            <td>${row.incident_description || 'N/A'}</td>
            <td>${row.violation_date || 'N/A'}</td>
            <td>${row.violation_time || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 2) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.student_name || 'N/A'}</td>
            <td>${row.parent_name || 'N/A'}</td>
            <td>${row.parent_contact_info || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 3) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.complainant_name || 'N/A'}</td>
            <td>${row.respondent_name || 'N/A'}</td>
            <td>${row.incident_description || 'N/A'}</td>
            <td>${row.complaint_date || 'N/A'}</td>
            <td>${row.complaint_time || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 4) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.offense_type || 'N/A'}</td>
            <td>${row.offense_description || 'N/A'}</td>
            <td>${row.sanction_consequences || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 5) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.student_name || 'N/A'}</td>
            <td>${row.adviser_name || 'N/A'}</td>
            <td>${row.type_of_offense || 'N/A'}</td>
            <td>${row.violation_date || 'N/A'}</td>
            <td>${row.violation_time || 'N/A'}</td>
            <td>${row.incident_description || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 6) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.student_name || 'N/A'}</td>
            <td>${row.adviser_name || 'N/A'}</td>
            <td>${row.section || 'N/A'}</td>
            <td>${row.grade_level || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 7) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.student_name || 'N/A'}</td>
            <td>${row.solution || 'N/A'}</td>
            <td>${row.recommendation || 'N/A'}</td>
            <td>${row.date || 'N/A'}</td>
            <td>${row.time || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 8) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.student_name || 'N/A'}</td>
            <td>${row.appointment_date || 'N/A'}</td>
            <td>${row.appointment_time || 'N/A'}</td>
            <td>${row.appointment_status || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 9) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.complainant_name || 'N/A'}</td>
            <td>${row.respondent_name || 'N/A'}</td>
            <td>${row.solution || 'N/A'}</td>
            <td>${row.recommendation || 'N/A'}</td>
            <td>${row.date_recorded || 'N/A'}</td>
            <td>${row.time_recorded || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 10) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.complainant_name || 'N/A'}</td>
            <td>${row.respondent_name || 'N/A'}</td>
            <td>${row.appointment_date || 'N/A'}</td>
            <td>${row.appointment_status || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 11) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.student_name || 'N/A'}</td>
            <td>${row.adviser_section || 'N/A'}</td>
            <td>${row.grade_level || 'N/A'}</td>
            <td>${row.total_violations || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 12) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.offense_type || 'N/A'}</td>
            <td>${row.description || 'N/A'}</td>
            <td>${row.total_occurrences || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 13) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.adviser_name || 'N/A'}</td>
            <td>${row.complainant_name || 'N/A'}</td>
            <td>${row.respondent_name || 'N/A'}</td>
            <td>${row.offense_type || 'N/A'}</td>
            <td>${row.complaint_date || 'N/A'}</td>
            <td>${row.complaint_time || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 14) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.student_name || 'N/A'}</td>
            <td>${row.section || 'N/A'}</td>
            <td>${row.grade_level || 'N/A'}</td>
            <td>${row.total_violations || 'N/A'}</td>
            <td>${row.first_violation_date || 'N/A'}</td>
            <td>${row.most_recent_violation_date || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 15) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.grade_level || 'N/A'}</td>
            <td>${row.offense_type || 'N/A'}</td>
            <td>${row.number_of_violations || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 16) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.student_name || 'N/A'}</td>
            <td>${row.parent_name || 'N/A'}</td>
            <td>${row.parent_contact_info || 'N/A'}</td>
            <td>${row.violation_date || 'N/A'}</td>
            <td>${row.violation_time || 'N/A'}</td>
            <td>${row.violation_status || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 17) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.complainant_name || 'N/A'}</td>
            <td>${row.respondent || 'N/A'}</td>
            <td>${row.offense_type || 'N/A'}</td>
            <td>${row.complaint_date || 'N/A'}</td>
            <td>${row.complaint_time || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 18) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.first_name || 'N/A'}</td>
            <td>${row.last_name || 'N/A'}</td>
            <td>${row.violation_count || 'N/A'}</td>
            <td>${row.complaint_involvement_count || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else if (reportId === 19) {
      data.forEach(row => {
        tableBody.innerHTML += `
          <tr>
            <td>${row.offense_type || 'N/A'}</td>
            <td>${row.sanction_consequences || 'N/A'}</td>
            <td>${row.month_and_year || 'N/A'}</td>
            <td>${row.number_of_sanctions_given || 'N/A'}</td>
          </tr>
        `;
      });
    }
    else {
      // fallback for unlisted reports
      data.forEach(row => {
        const values = Object.values(row);
        tableBody.innerHTML += `<tr>${values.map(v => `<td>${v ?? 'N/A'}</td>`).join('')}</tr>`;
      });
    }

  } catch (error) {
    console.error("Error fetching data:", error);
    showNotification(`Error loading report data: ${error.message}`, 'error');
    tableBody.innerHTML = `<tr><td colspan="10" style="text-align:center;color:red;">Error loading data. Please try again.</td></tr>`;
  } finally {
    loading.style.display = "none";
  }
}

/* attach event to boxes (report tiles) */
document.querySelectorAll('.report-box').forEach(box=>{
  box.addEventListener('click', ()=> openReportModal(box.dataset.modal.replace('modal','')));
});

/* close modals */
document.addEventListener('click', e=>{
  if(e.target.classList.contains('close')) e.target.closest('.modal').style.display='none';
  if(e.target.classList.contains('modal')) e.target.style.display='none';
});

/* search (text input passes 'modalX' so we convert to numeric id) */
function liveSearch(modalId, query){
  const id = modalId.replace('modal','');
  const table = document.getElementById('table-'+id);
  if(!table) return;
  query = query.toLowerCase();
  Array.from(table.querySelectorAll('tbody tr')).forEach(tr=>{
    tr.style.display = Array.from(tr.querySelectorAll('td')).some(td => td.textContent.toLowerCase().includes(query)) ? '' : 'none';
  });
}

/* Print as PDF - Automatically download as PDF when clicking Print button */
function printAsPDF(modalId) {
  const modal = document.getElementById(modalId);
  const reportId = modalId.replace('modal', '');
  const table = document.getElementById(`table-${reportId}`);

  if (!table) {
    showNotification('No data available to export', 'error');
    return;
  }

  const currentDate = new Date().toLocaleDateString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric'
  });

  const currentTime = new Date().toLocaleTimeString('en-PH', {
    hour: '2-digit', minute: '2-digit'
  });

  const reportTitle = modal.querySelector('h2').textContent;
  const rowCount = table.querySelectorAll('tbody tr').length;

  // Create a temporary element for PDF generation
  const element = document.createElement('div');
  element.innerHTML = `
    <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #000000; background: #ffffff; padding: 25px;">
      <!-- Professional Header with Logo on Right -->
      <div style="display: flex; align-items: center; border-bottom: 3px solid #1e3a8a; padding-bottom: 20px; margin-bottom: 25px;">
        <div style="flex: 1;">
          <h1 style="margin: 0; color: #000000; font-size: 24px; font-weight: 700;">TAGOLOAN SENIOR HIGH SCHOOL</h1>
          <h2 style="margin: 5px 0 0 0; color: #000000; font-size: 16px; font-weight: 500;">Student Violation Tracking System</h2>
          <p style="margin: 8px 0 0 0; color: #000000; font-size: 14px;">Official Report Document</p>
        </div>
        <div style="text-align: right;">
          <img src="/images/Logo.png" alt="School Logo" style="width: 70px; height: 70px; object-fit: contain;">
        </div>
      </div>

      <!-- Report Summary -->
      <div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px 20px; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h3 style="margin: 0; color: #000000; font-size: 18px; font-weight: 600;">${reportTitle}</h3>
            <p style="margin: 5px 0 0 0; color: #000000; font-size: 14px;">
              Total Records: <strong style="color: #000000;">${rowCount}</strong>
            </p>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 12px; color: #000000;">Document ID</div>
            <div style="font-size: 14px; font-weight: 600; color: #000000;">REP-${Date.now().toString().slice(-6)}</div>
          </div>
        </div>
      </div>

      <!-- Enhanced Table -->
      <div style="overflow: hidden; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        ${table.outerHTML}
      </div>

      <!-- Date Section Below Table -->
      <div style="margin-top: 30px; text-align: right;">
        <div style="font-size: 14px; color: #000000;">
          Generated on: <strong>${currentDate}</strong> at <strong>${currentTime}</strong>
        </div>
      </div>

      <!-- Footer Section - Removed "Reviewed By: School Principal" -->
      <div style="margin-top: 40px; border-top: 2px solid #e2e8f0; padding-top: 20px;">
        <div style="display: flex; justify-content: flex-start; align-items: flex-start;">
          <div style="flex: 1;">
            <div style="font-size: 12px; color: #000000; margin-bottom: 5px;">Prepared By:</div>
            <div style="border-bottom: 1px solid #cbd5e0; width: 200px; padding: 25px 0 5px 0;"></div>
            <div style="font-size: 12px; color: #000000; margin-top: 5px;">Prefect of Discipline</div>
          </div>
        </div>

        <!-- Confidential Notice -->
        <div style="text-align: center; margin-top: 30px; padding: 15px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px;">
          <div style="font-size: 11px; color: #c53030; font-weight: 600;">
            🔒 CONFIDENTIAL DOCUMENT - For Authorized Personnel Only
          </div>
          <div style="font-size: 10px; color: #e53e3e; margin-top: 5px;">
            This document contains sensitive student information. Unauthorized distribution is prohibited.
          </div>
        </div>
      </div>
    </div>
  `;

  // Enhanced table styling for PDF
  const tables = element.getElementsByTagName('table');
  for (let table of tables) {
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';
    table.style.fontSize = '11px';

    // Style table headers
    const headers = table.getElementsByTagName('th');
    for (let header of headers) {
      header.style.backgroundColor = '#1e3a8a';
      header.style.color = 'white';
      header.style.padding = '10px 8px';
      header.style.textAlign = 'left';
      header.style.fontWeight = '600';
      header.style.border = '1px solid #2d3748';
      header.style.fontSize = '10px';
      header.style.textTransform = 'uppercase';
      header.style.letterSpacing = '0.5px';
    }

    // Style table cells
    const cells = table.getElementsByTagName('td');
    for (let cell of cells) {
      cell.style.padding = '8px 6px';
      cell.style.border = '1px solid #e2e8f0';
      cell.style.fontSize = '10px';
      cell.style.color = '#000000';
    }

    // Style table rows
    const rows = table.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
      if (i % 2 === 0) {
        rows[i].style.backgroundColor = '#ffffff';
      } else {
        rows[i].style.backgroundColor = '#f7fafc';
      }
    }
  }

  // PDF options
  const options = {
    margin: [15, 15, 15, 15],
    filename: `${reportTitle.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toISOString().slice(0,10)}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: {
      scale: 2,
      useCORS: true,
      logging: false
    },
    jsPDF: {
      unit: 'mm',
      format: 'a4',
      orientation: 'portrait',
      compress: true
    }
  };

  showNotification('Generating PDF...', 'info');

  // Generate and download PDF
  html2pdf().set(options).from(element).save().then(() => {
    showNotification('PDF exported successfully!', 'success');
  }).catch(error => {
    console.error('PDF generation error:', error);
    showNotification('PDF generation failed. Please try again.', 'error');
  });
}

/* export CSV expects modal string 'modalX' */
function exportCSV(modalId){
  const id = modalId.replace('modal','');
  const table = document.getElementById('table-'+id);
  if(!table) return;
  const rows = Array.from(table.querySelectorAll('tr')).filter(r=>r.style.display!=='none');
  const csv = rows.map((row,i)=>{
    const cells = Array.from(row.querySelectorAll(i===0?'th':'td'));
    return cells.map(c=>`"${(c.textContent||'').replace(/"/g,'""')}"`).join(',');
  }).join('\n');
  const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a'); a.href = url; a.download = `report-${id}.csv`;
  document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
  showNotification('CSV exported successfully!', 'success');
}

function logout() {
  const confirmLogout = confirm("Are you sure you want to logout?");
  if (!confirmLogout) return;

  fetch("{{ route('prefect.logout') }}", {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    }
  })
  .then(response => {
    if(response.ok) {
      // Redirect to login after successful logout
      window.location.href = "{{ route('login') }}";
    } else {
      console.error('Logout failed:', response.statusText);
    }
  })
  .catch(error => console.error('Logout failed:', error));
}

function toggleProfileDropdown() {
  const dropdown = document.getElementById('profileDropdown');
  dropdown.classList.toggle('active');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
  const dropdown = document.getElementById('profileDropdown');
  const userInfo = document.querySelector('.user-info');

  if (!userInfo.contains(event.target) && !dropdown.contains(event.target)) {
    dropdown.classList.remove('active');
  }
});
</script>
</body>
</html>
