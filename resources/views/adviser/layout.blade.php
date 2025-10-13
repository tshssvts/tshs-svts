<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Adviser Dashboard</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/adviser(1)/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adviser/cards.css') }}">
     <link rel="stylesheet" href="{{ asset('css/adviser(1)/createViolation.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adviser(1)/toolbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adviser(1)/modal.css') }}">
     <link rel="stylesheet" href="{{ asset('css/adviser(1)/createParent.css') }}">
  <link rel="stylesheet" href="{{ asset('css/adviser(1)/createStudent.css') }}">
  <link rel="stylesheet" href="{{ asset('css/adviser(1)/createComplaint.css') }}">
  <link rel="stylesheet" href="{{ asset('css/adviser(1)/notificationsmodal.css') }}">
  
  <style>
    /* Add these styles for alerts and image upload */
    .alert {
      padding: 12px 15px;
      margin: 15px 0;
      border-radius: 5px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
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

    .profile-image-container {
      position: relative;
      display: inline-block;
      cursor: pointer;
    }

    .profile-image-container:hover .profile-image-overlay {
      display: flex !important;
    }

    .profile-image-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      border-radius: 50%;
      background: rgba(0,0,0,0.5);
      display: none;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    /* Profile dropdown styles */
    .profile-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background: white;
      border-radius: 5px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      min-width: 180px;
      z-index: 1000;
      display: none;
      overflow: hidden;
    }

    .profile-dropdown.show {
      display: block;
    }

    .profile-dropdown a {
      display: flex;
      align-items: center;
      padding: 12px 15px;
      color: #333;
      text-decoration: none;
      transition: background 0.2s;
      border-bottom: 1px solid #f0f0f0;
    }

    .profile-dropdown a:last-child {
      border-bottom: none;
    }

    .profile-dropdown a:hover {
      background: #f5f5f5;
    }

    .profile-dropdown i {
      margin-right: 10px;
      width: 16px;
      text-align: center;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      padding: 8px 12px;
      border-radius: 5px;
      transition: background 0.2s;
    }

    .user-info:hover {
      background: rgba(255,255,255,0.1);
    }

    .user-info img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
    }

    .user-info span {
      color: white;
      font-weight: 500;
    }

    .user-info i {
      color: white;
      transition: transform 0.3s;
    }

    .user-info.active i {
      transform: rotate(180deg);
    }

    /* Notification Bell Styles */
    .notification-bell {
      position: relative;
      margin-right: 20px;
      cursor: pointer;
    }

    .notification-bell i {
      font-size: 22px;
      color: #FFD700; /* Yellow color */
      transition: transform 0.3s ease;
    }

    .notification-bell:hover i {
      transform: scale(1.1);
    }

    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: #ff4757;
      color: white;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      font-size: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }

    /* Notification Dropdown Styles - ADDED */
    .notification-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.15);
      width: 350px;
      max-height: 400px;
      overflow-y: auto;
      z-index: 1001;
      display: none;
    }

    .notification-dropdown.show {
      display: block;
    }

    .notification-header {
      padding: 15px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .notification-header h3 {
      margin: 0;
      font-size: 16px;
      color: #333;
    }

    .notification-header .mark-all-read {
      background: none;
      border: none;
      color: #3498db;
      cursor: pointer;
      font-size: 13px;
    }

    .notification-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .notification-item {
      padding: 12px 15px;
      border-bottom: 1px solid #f5f5f5;
      cursor: pointer;
      transition: background 0.2s;
    }

    .notification-item:hover {
      background: #f9f9f9;
    }

    .notification-item.unread {
      background: #f0f7ff;
    }

    .notification-item:last-child {
      border-bottom: none;
    }

    .notification-title {
      font-weight: 600;
      font-size: 14px;
      color: #333;
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .notification-title i {
      color: #3498db;
    }

    .notification-message {
      font-size: 13px;
      color: #666;
      margin-bottom: 5px;
      line-height: 1.4;
    }

    .notification-time {
      font-size: 11px;
      color: #999;
    }

    .notification-empty {
      padding: 20px;
      text-align: center;
      color: #999;
      font-size: 14px;
    }

    .notification-footer {
      padding: 10px 15px;
      border-top: 1px solid #eee;
      text-align: center;
    }

    .notification-footer a {
      color: #3498db;
      text-decoration: none;
      font-size: 13px;
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
    </ul>
</div>

<!-- Profile Settings Modal -->
<div id="profileSettingsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-cog"></i> Profile Settings</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div class="modal-tabs">
                <button class="tab-btn active" onclick="openTab('profile-tab')">
                    <i class="fas fa-user"></i> My Profile
                </button>
                <button class="tab-btn" onclick="openTab('password-tab')">
                    <i class="fas fa-lock"></i> Change Password
                </button>
            </div>

            <!-- Profile Tab -->
            <div id="profile-tab" class="tab-content active">
                <!-- Profile Picture Section -->
                <div class="profile-picture-section" style="text-align: center; margin-bottom: 20px;">
                    <div class="profile-image-container" style="position: relative; display: inline-block;">
                        <img id="profile-image-preview" src="/images/user.jpg" alt="Profile"
                            style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #3498db;">
                        <div class="profile-image-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 50%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center;">
                            <i class="fas fa-camera" style="color: white; font-size: 24px;"></i>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <input type="file" id="profile-image-input" accept="image/*" style="display: none;">
                        <button type="button" onclick="document.getElementById('profile-image-input').click()"
                                class="btn-send-code" style="margin: 5px;">
                            <i class="fas fa-upload"></i> Upload Photo
                        </button>
                        <button type="button" onclick="removeProfileImage()"
                                class="btn-cancel" style="margin: 5px; padding: 8px 15px;">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                    <div id="profile-image-error" class="error-message" style="text-align: center;"></div>
                </div>

                <div class="profile-info">
                    <div class="info-item">
                        <span class="info-label">Name:</span>
                        <span class="info-value" id="profile-name">Loading...</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value" id="profile-email">Loading...</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Gender:</span>
                        <span class="info-value" id="profile-gender">Loading...</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Contact:</span>
                        <span class="info-value" id="profile-contact">Loading...</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value" id="profile-status">Loading...</span>
                    </div>
                </div>
            </div>

            <!-- Change Password Tab -->
            <div id="password-tab" class="tab-content">
                <form id="changePasswordForm">
                    @csrf

                    <!-- Step 1: Request Verification -->
                    <div id="verification-step-1">
                        <div class="verification-section">
                            <h4 style="margin: 0 0 10px 0; color: #2c3e50;">
                                <i class="fas fa-shield-alt"></i> Email Verification Required
                            </h4>
                            <p style="margin: 0 0 15px 0; font-size: 13px; color: #5a6c7d;">
                                For security purposes, we need to verify your identity before changing your password.
                                A verification code will be sent to your email address.
                            </p>
                            <button type="button" class="btn-send-code" onclick="sendVerificationCode()" id="send-code-btn">
                                <i class="fas fa-paper-plane"></i> Send Verification Code
                            </button>
                            <div class="countdown" id="countdown"></div>
                        </div>
                    </div>

                    <!-- Step 2: Enter Verification Code -->
                    <div id="verification-step-2" style="display: none;">
                        <div class="verification-section">
                            <h4 style="margin: 0 0 10px 0; color: #2c3e50;">
                                <i class="fas fa-envelope"></i> Enter Verification Code
                            </h4>
                            <p style="margin: 0 0 15px 0; font-size: 13px; color: #5a6c7d;">
                                Please check your email <strong id="user-email">Loading...</strong>
                                and enter the 6-digit verification code below.
                            </p>
                            <div class="verification-code">
                                <input type="text" id="verification_code" name="verification_code"
                                       maxlength="6" placeholder="000000" required>
                            </div>
                            <div class="countdown" id="code-countdown"></div>
                            <button type="button" class="btn-send-code" onclick="sendVerificationCode()" id="resend-code-btn" style="margin-top: 10px;" disabled>
                                <i class="fas fa-redo"></i> Resend Code (<span id="resend-timer">60</span>s)
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Set New Password -->
                    <div id="verification-step-3" style="display: none;">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="password-input-container">
                                <input type="password" id="new_password" name="new_password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <span class="error-message" id="new_password_error"></span>
                            <div class="success-message" id="password-strength"></div>
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <div class="password-input-container">
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('new_password_confirmation')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <span class="error-message" id="new_password_confirmation_error"></span>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="closeProfileModal()">Cancel</button>
                            <button type="submit" class="btn-submit" id="change-password-btn">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

 <!-- ✅ Main content area (for child pages) -->
  <main class="main-content">
     <!-- ======= HEADER ======= -->
  <header class="main-header">
    <div class="header-left">
      <h2>Student Violation Tracking System</h2>
    </div>
    <div class="header-right">
      <!-- Yellow Notification Bell -->
      <div class="notification-bell" onclick="toggleNotifications(event)">
        <i class="fas fa-bell"></i>
        <div class="notification-badge">3</div>
      </div>
      
      <!-- Notification Dropdown - ADDED -->
      <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-header">
          <h3>Notifications</h3>
          <button class="mark-all-read" onclick="markAllAsRead()">Mark all as read</button>
        </div>
        <ul class="notification-list" id="notificationList">
          <!-- Notifications will be populated here -->
        </ul>
        <div class="notification-footer">
          <a href="#" onclick="viewAllNotifications()">View All Notifications</a>
        </div>
      </div>
      
      <div class="user-info" onclick="toggleProfileDropdown()">
        <img src="/images/user.jpg" alt="Adviser">
        <span>Adviser</span>
        <i class="fas fa-caret-down"></i>
      </div>
      <div class="profile-dropdown" id="profileDropdown">
        <a href="#" onclick="showProfile(); return false;">
          <i class="fas fa-user"></i> Profile
        </a>
        <a href="#" onclick="showChangePassword(); return false;">
          <i class="fas fa-key"></i> Change Password
        </a>
        <a href="#" onclick="logout(); return false;">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>
  </header>

    @yield('content')
  </main>

<script>
 // Global variables for routes and CSRF token
    const ROUTES = {
        sendVerificationCode: '{{ route("adviser.send-verification-code") }}',
        changePassword: '{{ route("adviser.change-password") }}',
        profileInfo: '{{ route("adviser.profile-info") }}',
        uploadProfileImage: '{{ route("adviser.upload-profile-image") }}',
        removeProfileImage: '{{ route("adviser.remove-profile-image") }}',
        logout: '{{ route("adviser.logout") }}',
        login: '{{ route("login") }}'
    };
    const CSRF_TOKEN = '{{ csrf_token() }}';

    // Sample notifications data - ADDED
    let notifications = [
        {
            id: 1,
            title: "New Violation Reported",
            message: "John Doe has been reported for a minor offense",
            type: "violation",
            time: "5 minutes ago",
            unread: true,
            route: "violation.record"
        },
        {
            id: 2,
            title: "Parent Complaint",
            message: "You have received a new complaint from a parent",
            type: "complaint",
            time: "1 hour ago",
            unread: true,
            route: "complaints.all"
        },
        {
            id: 3,
            title: "Student Registration",
            message: "A new student has been assigned to your class",
            type: "student",
            time: "2 hours ago",
            unread: true,
            route: "student.list"
        },
        {
            id: 4,
            title: "Report Generated",
            message: "Monthly violation report is ready for review",
            type: "report",
            time: "1 day ago",
            unread: false,
            route: "adviser.reports"
        },
        {
            id: 5,
            title: "Parent Meeting Scheduled",
            message: "You have a parent meeting scheduled for tomorrow",
            type: "parent",
            time: "2 days ago",
            unread: false,
            route: "parent.list"
        }
    ];

    // Initialize notifications - ADDED
    document.addEventListener('DOMContentLoaded', function() {
        loadNotifications();
        updateNotificationBadge();
    });

    // Load notifications into the dropdown - ADDED
    function loadNotifications() {
        const notificationList = document.getElementById('notificationList');
        notificationList.innerHTML = '';
        
        if (notifications.length === 0) {
            notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
            return;
        }
        
        notifications.forEach(notification => {
            const listItem = document.createElement('li');
            listItem.className = `notification-item ${notification.unread ? 'unread' : ''}`;
            listItem.setAttribute('data-id', notification.id);
            listItem.setAttribute('data-route', notification.route);
            
            // Determine icon based on notification type
            let icon = 'fas fa-bell';
            switch(notification.type) {
                case 'violation':
                    icon = 'fas fa-exclamation-triangle';
                    break;
                case 'complaint':
                    icon = 'fas fa-comments';
                    break;
                case 'student':
                    icon = 'fas fa-user-graduate';
                    break;
                case 'report':
                    icon = 'fas fa-chart-line';
                    break;
                case 'parent':
                    icon = 'fas fa-users';
                    break;
            }
            
            listItem.innerHTML = `
                <div class="notification-title">
                    <i class="${icon}"></i>
                    ${notification.title}
                </div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${notification.time}</div>
            `;
            
            // Add click event to navigate to the related module
            listItem.addEventListener('click', function() {
                markAsRead(notification.id);
                navigateToModule(notification.route);
            });
            
            notificationList.appendChild(listItem);
        });
    }

    // Toggle notifications dropdown - MODIFIED
    function toggleNotifications(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('notificationDropdown');
        const profileDropdown = document.getElementById('profileDropdown');
        
        dropdown.classList.toggle('show');
        profileDropdown.classList.remove('show');
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function closeDropdown(e) {
            if (!dropdown.contains(e.target) && !e.target.closest('.notification-bell')) {
                dropdown.classList.remove('show');
                document.removeEventListener('click', closeDropdown);
            }
        });
    }

    // Mark a notification as read - ADDED
    function markAsRead(notificationId) {
        const notification = notifications.find(n => n.id === notificationId);
        if (notification && notification.unread) {
            notification.unread = false;
            updateNotificationBadge();
            
            // Update the UI
            const notificationItem = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('unread');
            }
        }
    }

    // Mark all notifications as read - MODIFIED
    function markAllAsRead() {
        // Remove all notifications from the array
        notifications = [];
        
        // Update the UI
        loadNotifications();
        updateNotificationBadge();
        
        // Show a message that all notifications are cleared
        const notificationList = document.getElementById('notificationList');
        notificationList.innerHTML = '<div class="notification-empty">No notifications</div>';
    }

    // Update the notification badge count - ADDED
    function updateNotificationBadge() {
        const unreadCount = notifications.filter(n => n.unread).length;
        const badge = document.querySelector('.notification-badge');
        
        if (unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    // Navigate to the appropriate module based on the notification - ADDED
    function navigateToModule(routeName) {
        // Close the notification dropdown
        document.getElementById('notificationDropdown').classList.remove('show');
        
        // Navigate to the route
        window.location.href = getRouteUrl(routeName);
    }

    // Helper function to get route URL - ADDED
    function getRouteUrl(routeName) {
        const routeMap = {
            'adviser.dashboard': '{{ route("adviser.dashboard") }}',
            'parent.list': '{{ route("parent.list") }}',
            'student.list': '{{ route("student.list") }}',
            'violation.record': '{{ route("violation.record") }}',
            'complaints.all': '{{ route("complaints.all") }}',
            'offense.sanction': '{{ route("offense.sanction") }}',
            'adviser.reports': '{{ route("adviser.reports") }}'
        };
        
        return routeMap[routeName] || '#';
    }

    // View all notifications (placeholder function) - ADDED
    function viewAllNotifications() {
        alert("This would open a full notifications page in a real implementation");
        document.getElementById('notificationDropdown').classList.remove('show');
    }

    // Toggle profile dropdown
    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const userInfo = document.querySelector('.user-info');
        
        dropdown.classList.toggle('show');
        notificationDropdown.classList.remove('show');
        userInfo.classList.toggle('active');
    }

    // Close dropdown when clicking outside - MODIFIED
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('profileDropdown');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const userInfo = document.querySelector('.user-info');
        const notificationBell = document.querySelector('.notification-bell');
        
        if (!userInfo.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.remove('show');
            userInfo.classList.remove('active');
        }
        
        if (!notificationBell.contains(event.target) && !notificationDropdown.contains(event.target)) {
            notificationDropdown.classList.remove('show');
        }
    });

    // Show profile modal (without functionality)
    function showProfile() {
        alert("Profile functionality would open here");
        toggleProfileDropdown(); // Close dropdown after selection
    }

    // Show change password modal (without functionality)
    function showChangePassword() {
        alert("Change Password functionality would open here");
        toggleProfileDropdown(); // Close dropdown after selection
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
        
        // Close dropdown after selection
        toggleProfileDropdown();
    }

    // Tab switching function (for modal if needed later)
    function openTab(tabId) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(tab => {
            tab.classList.remove('active');
        });

        // Remove active class from all tab buttons
        const tabButtons = document.querySelectorAll('.tab-btn');
        tabButtons.forEach(button => {
            button.classList.remove('active');
        });

        // Show the selected tab content
        document.getElementById(tabId).classList.add('active');

        // Activate the clicked tab button
        event.currentTarget.classList.add('active');
    }

    // Close profile modal
    function closeProfileModal() {
        document.getElementById('profileSettingsModal').style.display = 'none';
    }

    // Close modal when clicking on X
    document.querySelector('.close').addEventListener('click', closeProfileModal);

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('profileSettingsModal');
        if (event.target === modal) {
            closeProfileModal();
        }
    });
</script>
<script src="{{ asset('js/prefect/layout.js') }}"></script>

</body>
</html>