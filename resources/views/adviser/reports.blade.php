<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Adviser Dashboard - Reports</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"/>
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
      background: linear-gradient(135deg, #001a33, #003366, #1c1c2c);
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
      background-color: #012448;
      border-radius: 4px;
    }

    .sidebar::-webkit-scrollbar-track {
      background-color: #011427;
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
      color: #66ccff; /* Sky blue hover */
    }

    /* Logout */
    .sidebar ul li:last-child {
      margin-top: auto;
      background: rgba(0, 102, 204, 0.2); /* soft blue background */
      border: 1px solid rgba(0, 102, 204, 0.3); /* light blue border */
      cursor: pointer;
      justify-content: block;
      display: flex; /* Align icon + text */
    }

    .sidebar ul li:last-child:hover {
      background: rgba(0, 102, 204, 0.4); /* brighter blue on hover */
      border: 1px solid rgba(0, 102, 204, 0.6);
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
      background: #003366;
      color: #fff;
      padding: 0 25px; /* removed top/bottom padding to control height */
      border-bottom: 3px solid #001366;
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

    .toolbar button.btn-danger {
      background: #c0392b;
      color: #fff;
    }

    .toolbar button.btn-danger:hover {
      background: #962d22;
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

    /* Logo */
    .sidebar img {
      width: 100px;
      height: auto;
      margin: 0 auto 0.5rem;
      display: block;
      image-rendering: -webkit-optimize-contrast;
      image-rendering: crisp-edges;
    }

    .modal-table-container {
      max-height: 400px;
      overflow-y: auto;
    }

    /* Inside Modal Notification Styles */
    .modal-notification {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      padding: 15px 25px;
      border-radius: 8px;
      color: white;
      z-index: 1001;
      display: none;
      box-shadow: 0 4px 20px rgba(0,0,0,0.25);
      min-width: 300px;
      text-align: center;
      font-size: 16px;
      font-weight: 500;
      backdrop-filter: blur(5px);
    }

    .modal-notification.success {
      background: #27ae60;
    }

    .modal-notification.error {
      background: #e74c3c;
    }

    .modal-notification.info {
      background: #3498db;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <img src="/images/Logo.png" alt="Logo">
    <h2>Adviser</h2>
    <ul>
        <li class="{{ request()->routeIs('adviser.dashboard') ? 'active' : '' }}">
            <a href="{{ route('adviser.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</a>
        </li>
        <li class="{{ request()->routeIs('parent.list') ? 'active' : '' }}">
            <a href="{{ route('parent.list') }}"><i class="fas fa-users"></i> Parents</a>
        </li>
        <li class="{{ request()->routeIs('student.list') ? 'active' : '' }}">
            <a href="{{ route('student.list') }}"><i class="fas fa-user-graduate"></i> Students</a>
        </li>
        <li class="{{ request()->routeIs('violation.record') ? 'active' : '' }}">
            <a href="{{ route('violation.record') }}"><i class="fas fa-book"></i> Violations</a>
        </li>
        <li class="{{ request()->routeIs('complaints.all') ? 'active' : '' }}">
            <a href="{{ route('complaints.all') }}"><i class="fas fa-comments"></i> Complaints</a>
        </li>
        <li class="{{ request()->routeIs('offense.sanction') ? 'active' : '' }}">
            <a href="{{ route('offense.sanction') }}"><i class="fas fa-exclamation-triangle"></i> Offense & Sanctions</a>
        </li>
        <li class="{{ request()->routeIs('adviser.reports') ? 'active' : '' }}">
            <a href="{{ route('adviser.reports') }}"><i class="fas fa-chart-line"></i> Reports</a>
        </li>
        <li>
            <a href="#" onclick="event.preventDefault(); logout();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>

<!-- ✅ Main content area -->
<main class="main-content">
  <!-- ======= HEADER ======= -->
  <header class="main-header">
    <div class="header-left">
      <h2>Student Violation Tracking System</h2>
    </div>

    <div class="header-right">
      <div class="user-info" onclick="toggleProfileDropdown()">
        <img src="/images/user.jpg" alt="User">
        <span>{{ Auth::guard('adviser')->user()->adviser_fname }} {{ Auth::guard('adviser')->user()->adviser_lname }}</span>
        <i class="fas fa-caret-down"></i>
      </div>
      <div class="profile-dropdown" id="profileDropdown">
        <!-- Profile options can be added here if needed -->
      </div>
    </div>
  </header>

  <!-- Reports Title -->
  <h3 class="reports-title">REPORTS</h3>

  <!-- Report Boxes Grid -->
  <div class="reports-grid">
    <div class="report-box" data-modal="modal1"><i class="fas fa-book-open"></i><h3>Anecdotal Records per Complaint Case</h3></div>
    <div class="report-box" data-modal="modal2"><i class="fas fa-book"></i><h3>Anecdotal Records per Violation Case</h3></div>
    <div class="report-box" data-modal="modal3"><i class="fas fa-calendar-check"></i><h3>Appointments Scheduled for Complaints</h3></div>
    <div class="report-box" data-modal="modal4"><i class="fas fa-calendar-alt"></i><h3>Appointments Scheduled for Violation Cases</h3></div>
    <div class="report-box" data-modal="modal5"><i class="fas fa-file-alt"></i><h3>Complaint Records with Complainant and Respondent</h3></div>
    <div class="report-box" data-modal="modal6"><i class="fas fa-clock"></i><h3>Complaints Filed within the Last 30 Days</h3></div>
    <div class="report-box" data-modal="modal7"><i class="fas fa-chart-bar"></i><h3>Common Offenses by Frequency</h3></div>
    <div class="report-box" data-modal="modal8"><i class="fas fa-exclamation-triangle"></i><h3>List of Violators with Repeat Offenses</h3></div>
    <div class="report-box" data-modal="modal9"><i class="fas fa-gavel"></i><h3>Offenses and Their Sanction Consequences</h3></div>
    <div class="report-box" data-modal="modal10"><i class="fas fa-phone-alt"></i><h3>Parent Contact Info for Students with Active Violations</h3></div>
    <div class="report-box" data-modal="modal11"><i class="fas fa-chart-line"></i><h3>Sanction Trends Across Time Periods</h3></div>
    <div class="report-box" data-modal="modal12"><i class="fas fa-user-graduate"></i><h3>Students and Their Parents</h3></div>
    <div class="report-box" data-modal="modal13"><i class="fas fa-user-shield"></i><h3>Students with Both Violation and Complaint Records</h3></div>
    <div class="report-box" data-modal="modal14"><i class="fas fa-user-friends"></i><h3>Students with the Most Violation Records</h3></div>
    <div class="report-box" data-modal="modal15"><i class="fas fa-exclamation-circle"></i><h3>Violation Records with Violator Information</h3></div>
  </div>
</main>

<!-- Modals -->
@for($i=1; $i<=15; $i++)
<div id="modal{{ $i }}" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    
    <!-- Inside Modal Notification -->
    <div id="notification-modal{{ $i }}" class="modal-notification" style="display: none;"></div>
    
    <div class="adviser-info" style="margin-bottom: 10px; font-weight: bold; text-align: left; font-size: 15px;">
      Adviser: <span id="adviser-name"></span> |
      Grade Level: <span id="adviser-gradelevel"></span> |
      Section: <span id="adviser-section"></span>
    </div>

    <h2 class="modal-title"></h2>
    <div class="toolbar">
      <input type="text" placeholder="Search..." oninput="liveSearch('modal{{ $i }}', this.value)">
      <button class="btn btn-warning" onclick="printAsPDF('modal{{ $i }}')"><i class="fa fa-print"></i> Print to PDF</button>
      <button class="btn btn-danger" onclick="exportCSV('modal{{ $i }}')"><i class="fa fa-file-export"></i> Export CSV</button>
    </div>

    <div class="modal-table-container">
      <table id="table-{{ $i }}" class="w-full border-collapse">
        <thead>
          @switch($i)
            @case(1)
            <tr>
                <th>Anecdotal ID</th>
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
                <th>Appointment ID</th>
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
                <th>Complaint ID</th>
                <th>Complainant Name</th>
                <th>Respondent Name</th>
                <th>Incident Description</th>
                <th>Complaint Date</th>
                <th>Complaint Time</th>
            </tr>
            @break
            @case(6)
            <tr>
                <th>Complaint ID</th>
                <th>Complainant Name</th>
                <th>Respondent Name</th>
                <th>Type of Offense</th>
                <th>Complaint Date</th>
                <th>Complaint Time</th>
            </tr>
            @break
            @case(7)
            <tr>
                <th>Offense ID</th>
                <th>Offense Type</th>
                <th>Description</th>
                <th>Total Occurrences</th>
            </tr>
            @break
            @case(8)
            <tr>
                <th>Student Name</th>
                <th>Total Violations</th>
                <th>First Violation Date</th>
                <th>Most Recent Violation Date</th>
            </tr>
            @break
            @case(9)
            <tr>
                <th>Offense Type</th>
                <th>Offense Description</th>
                <th>Sanction Consequences</th>
            </tr>
            @break
            @case(10)
            <tr>
                <th>Student Name</th>
                <th>Parent Name</th>
                <th>Parent Contact Info</th>
                <th>Violation Date</th>
                <th>Violation Time</th>
                <th>Violation Status</th>
            </tr>
            @break
            @case(11)
            <tr>
                <th>Offense Sanction ID</th>
                <th>Offense Type</th>
                <th>Sanction Consequences</th>
                <th>Month and Year</th>
                <th>Number of Sanctions Given</th>
            </tr>
            @break
            @case(12)
            <tr>
                <th>Student Name</th>
                <th>Parent Name</th>
                <th>Parent Contact Info</th>
            </tr>
            @break
            @case(13)
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Violation Count</th>
                <th>Complaint Involvement Count</th>
            </tr>
            @break
            @case(14)
            <tr>
                <th>Student Name</th>
                <th>Total Violations</th>
            </tr>
            @break
            @case(15)
            <tr>
                <th>Violation ID</th>
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
</div>
@endfor

<script>
  // Modal notification functions
  function showNotification(modalId, message, type = 'info') {
    const notification = document.getElementById(`notification-${modalId}`);
    
    notification.textContent = message;
    notification.className = `modal-notification ${type}`;
    notification.style.display = 'block';
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
      notification.style.display = 'none';
    }, 3000);
  }

  // Close modal functions
  function setupModalClose() {
    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
      if (e.target.classList.contains('modal')) {
        document.querySelectorAll('.modal').forEach(modal => {
          modal.style.display = 'none';
        });
      }
    });
  }

  // Initialize modal close functionality
  document.addEventListener('DOMContentLoaded', setupModalClose);

  // Get adviser data from backend
  const loggedAdviser = {
    name: "{{ auth()->guard('adviser')->user()->adviser_fname }} {{ auth()->guard('adviser')->user()->adviser_lname }}",
    gradelevel: "{{ auth()->guard('adviser')->user()->adviser_gradelevel }}",
    section: "{{ auth()->guard('adviser')->user()->adviser_section }}"
  };

  // Open report modal
  function openReportModal(reportId) {
    const modal = document.getElementById('modal' + reportId);

    // ✅ Fill adviser info in this modal
    modal.querySelector('#adviser-name').textContent = loggedAdviser.name;
    modal.querySelector('#adviser-gradelevel').textContent = loggedAdviser.gradelevel;
    modal.querySelector('#adviser-section').textContent = loggedAdviser.section;

    // Set modal title
    const title = document.querySelector(`.report-box[data-modal="modal${reportId}"] h3`).textContent;
    modal.querySelector('.modal-title').textContent = title;

    fetch(`/adviser/reports/data/${reportId}`)
      .then(res => res.ok ? res.json() : Promise.reject('Fetch failed'))
      .then(data => {
        const thead = modal.querySelector('thead');
        const tbody = modal.querySelector('tbody');
        thead.innerHTML = '';
        tbody.innerHTML = '';

        // Build table header manually
        let headers = [];
        switch (parseInt(reportId)) {
          case 1: headers = ['Anecdotal ID', 'Complainant Name','Respondent Name','Solution','Recommendation','Date Recorded','Time Recorded']; break;
          case 2: headers = ['Student Name','Solution','Recommendation','Date','Time']; break;
          case 3: headers = ['Appointment ID', 'Complainant Name','Respondent Name','Appointment Date','Appointment Status']; break;
          case 4: headers = ['Student Name','Appointment Date','Appointment Time','Appointment Status']; break;
          case 5: headers = ['Complaint ID', 'Complainant Name','Respondent Name','Incident Description','Complaint Date','Complaint Time']; break;
          case 6: headers = ['Complaint ID', 'Complainant Name','Respondent Name','Type of Offense','Complaint Date','Complaint Time']; break;
          case 7: headers = ['Offense ID', 'Offense Type','Description','Total Occurrences']; break;
          case 8: headers = ['Student Name','Total Violations','First Violation Date','Most Recent Violation Date']; break;
          case 9: headers = ['Offense Type','Offense Description','Sanction Consequences']; break;
          case 10: headers = ['Student Name','Parent Name','Parent Contact Info','Violation Date','Violation Time','Violation Status']; break;
          case 11: headers = ['Offense Sanction ID', 'Offense Type','Sanction Consequences','Month and Year','Number of Sanctions Given']; break;
          case 12: headers = ['Student Name','Parent Name','Parent Contact Info']; break;
          case 13: headers = ['First Name','Last Name','Violation Count','Complaint Involvement Count']; break;
          case 14: headers = ['Student Name','Total Violations']; break;
          case 15: headers = ['Violation ID', 'Student Name','Offense Type','Sanction','Incident Description','Violation Date','Violation Time']; break;
        }

        // Insert header row
        thead.innerHTML = `<tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr>`;

        // Build table body manually (match controller column names)
        if (!data.length) {
          tbody.innerHTML = `<tr><td colspan="${headers.length}" style="text-align:center;">No records found.</td></tr>`;
          modal.style.display = 'block';
          return;
        }

        data.forEach(row => {
          let tr = document.createElement('tr');
          switch (parseInt(reportId)) {
            case 1:
              tr.innerHTML = `
                              <td>${row.anecdotal_id || ''}</td>
                              <td>${row.complainant_name}</td>
                              <td>${row.respondent_name}</td>
                              <td>${row.solution}</td>
                              <td>${row.recommendation}</td>
                              <td>${row.date_recorded}</td>
                              <td>${row.time_recorded}</td>`;
              break;
            case 2:
              tr.innerHTML = `<td>${row.student_name}</td>
                              <td>${row.solution}</td>
                              <td>${row.recommendation}</td>
                              <td>${row.date}</td>
                              <td>${row.time}</td>`;
              break;
            case 3:
              tr.innerHTML = `
                              <td>${row.appointment_id || ''}</td>
                              <td>${row.complainant_name}</td>
                              <td>${row.respondent_name}</td>
                              <td>${row.appointment_date}</td>
                              <td>${row.appointment_status}</td>`;
              break;
            case 4:
              tr.innerHTML = `<td>${row.student_name}</td>
                              <td>${row.appointment_date}</td>
                              <td>${row.appointment_time}</td>
                              <td>${row.appointment_status}</td>`;
              break;
            case 5:
              tr.innerHTML = `
                              <td>${row.complaint_id || ''}</td>
                              <td>${row.complainant_name}</td>
                              <td>${row.respondent_name}</td>
                              <td>${row.incident_description}</td>
                              <td>${row.complaint_date}</td>
                              <td>${row.complaint_time}</td>`;
              break;
            case 6:
              tr.innerHTML = `
                              <td>${row.complaint_id || ''}</td>
                              <td>${row.complainant_name}</td>
                              <td>${row.respondent_name}</td>
                              <td>${row.offense_type}</td>
                              <td>${row.complaint_date}</td>
                              <td>${row.complaint_time}</td>`;
              break;
            case 7:
              tr.innerHTML = `
                              <td>${row.offense_id || ''}</td>
                              <td>${row.offense_type}</td>
                              <td>${row.offense_description}</td>
                              <td>${row.total_occurrences}</td>`;
              break;
            case 8:
              tr.innerHTML = `<td>${row.student_name}</td>
                              <td>${row.total_violations}</td>
                              <td>${row.first_violation_date}</td>
                              <td>${row.most_recent_violation_date}</td>`;
              break;
            case 9:
              tr.innerHTML = `<td>${row.offense_type}</td>
                              <td>${row.offense_description}</td>
                              <td>${row.sanction_consequences}</td>`;
              break;
            case 10:
              tr.innerHTML = `<td>${row.student_name}</td>
                              <td>${row.parent_name}</td>
                              <td>${row.parent_contactinfo}</td>
                              <td>${row.violation_date}</td>
                              <td>${row.violation_time}</td>
                              <td>${row.violation_status}</td>`;
              break;
            case 11:
            tr.innerHTML = `
                            <td>${row.offense_sanction_id || ''}</td>
                            <td>${row.offense_type}</td>
                            <td>${row.sanction_consequences}</td>
                            <td>${row.month_and_year}</td>
                            <td>${row.number_of_sanctions_given}</td>`;
            break;
            case 12:
              tr.innerHTML = `<td>${row.student_name}</td>
                              <td>${row.parent_name}</td>
                              <td>${row.parent_contactinfo}</td>`;
              break;
            case 13:
              tr.innerHTML = `<td>${row.first_name}</td>
                              <td>${row.last_name}</td>
                              <td>${row.violation_count}</td>
                              <td>${row.complaint_involvement_count}</td>`;
              break;
            case 14:
              tr.innerHTML = `<td>${row.student_name}</td>
                              <td>${row.total_violations}</td>`;
              break;
            case 15:
              tr.innerHTML = `
                              <td>${row.violation_id || ''}</td>
                              <td>${row.student_name}</td>
                              <td>${row.offense_type}</td>
                              <td>${row.sanction}</td>
                              <td>${row.incident_description}</td>
                              <td>${row.violation_date}</td>
                              <td>${row.violation_time}</td>`;
              break;
          }
          tbody.appendChild(tr);
        });

        modal.style.display = 'block';
      })
      .catch(err => {
      console.error(err);
      showNotification('modal' + reportId, 'Failed to load report data. Please try again.', 'error');
      modal.style.display = 'block'; // still open so user sees something
    });
  }

  // Attach click listeners
  document.querySelectorAll('.report-box').forEach(box => {
    box.addEventListener('click', () => {
      openReportModal(box.dataset.modal.replace('modal',''));
    });
  });

  // Close modal
  document.querySelectorAll('.modal .close').forEach(btn => {
    btn.addEventListener('click', e => {
      e.target.closest('.modal').style.display = 'none';
    });
  });
  window.onclick = e => {
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
  }

  // Search
  function liveSearch(modalId, query){
    const tbody = document.querySelector('#'+modalId+' tbody');
    query = query.toLowerCase();
    tbody.querySelectorAll('tr').forEach(tr=>{
      tr.style.display = Array.from(tr.cells).some(td=>td.textContent.toLowerCase().includes(query)) ? '' : 'none';
    });
  }

  // Print as PDF - Automatically download as PDF when clicking Print button
  function printAsPDF(modalId) {
    const modal = document.getElementById(modalId);
    const reportId = modalId.replace('modal', '');
    const table = document.querySelector(`#${modalId} table`);
    
    if (!table) {
      showNotification(modalId, 'No data available to export', 'error');
      return;
    }

    const currentDate = new Date().toLocaleDateString('en-PH', {
      year: 'numeric', month: 'long', day: 'numeric'
    });

    const currentTime = new Date().toLocaleTimeString('en-PH', {
      hour: '2-digit', minute: '2-digit'
    });

    const reportTitle = modal.querySelector('.modal-title').textContent;
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

        <!-- Adviser Information -->
        <div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px 20px; margin-bottom: 25px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
              <h3 style="margin: 0; color: #000000; font-size: 18px; font-weight: 600;">${reportTitle}</h3>
              <p style="margin: 5px 0 0 0; color: #000000; font-size: 14px;">
                Total Records: <strong style="color: #000000;">${rowCount}</strong> | 
                Adviser: <strong style="color: #000000;">${loggedAdviser.name}</strong> | 
                Grade Level: <strong style="color: #000000;">${loggedAdviser.gradelevel}</strong> | 
                Section: <strong style="color: #000000;">${loggedAdviser.section}</strong>
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
        <div style="text-align: center; margin-top: 20px; padding: 15px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
          <div style="font-size: 14px; color: #000000; font-weight: 500;">
            📅 Report Generated on: <strong>${currentDate}</strong> at <strong>${currentTime}</strong>
          </div>
        </div>

        <!-- Footer Section -->
        <div style="margin-top: 40px; border-top: 2px solid #e2e8f0; padding-top: 20px;">
          <div style="display: flex; justify-content: center; align-items: flex-start;">
            <div style="text-align: center;">
              <div style="font-size: 12px; color: #000000; margin-bottom: 5px;">Prepared By:</div>
              <div style="border-bottom: 1px solid #cbd5e0; width: 200px; padding: 25px 0 5px 0; margin: 0 auto;"></div>
              <div style="font-size: 12px; color: #000000; margin-top: 5px;">Class Adviser</div>
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

    // Generate and download PDF
    html2pdf().set(options).from(element).save().then(() => {
      showNotification(modalId, 'PDF downloaded successfully!', 'success');
    }).catch(error => {
      console.error('PDF generation error:', error);
      showNotification(modalId, 'PDF generation failed. Please try again.', 'error');
    });
  }

  // Export CSV
  function exportCSV(modalId){
    const table = document.querySelector('#'+modalId+' table');
    const rows = Array.from(table.querySelectorAll('tr')).filter(r=>r.style.display!=='none');
    const csv = rows.map((row,i)=>{
      const cells = Array.from(row.querySelectorAll(i===0?'th':'td'));
      return cells.map(c=>`"${(c.textContent||'').replace(/"/g,'""')}"`).join(',');
    }).join('\n');
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = modalId+'.csv';
    document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(a.href);
    showNotification(modalId, 'CSV exported successfully!', 'success');
  }

  // Logout function
  function logout() {
    const confirmLogout = confirm("Are you sure you want to logout?");
    if (!confirmLogout) return;

    fetch("{{ route('adviser.logout') }}", {
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

  // Profile dropdown toggle
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