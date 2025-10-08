<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Prefect</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="{{ asset('css/prefect(1)/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/prefect/cards.css') }}">
  <link rel="stylesheet" href="{{ asset('css/prefect(1)/createViolation.css') }}">
  <link rel="stylesheet" href="{{ asset('css/prefect(1)/toolbar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/prefect(1)/modal.css') }}">
  <link rel="stylesheet" href="{{ asset('css/prefect(1)/createParent.css') }}">
  <link rel="stylesheet" href="{{ asset('css/prefect(1)/createStudent.css') }}">
  <link rel="stylesheet" href="{{ asset('css/prefect(1)/createComplaint.css') }}">
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
  </style>
</head>
<body>

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
        {{-- </li>
               <li class="{{ request()->routeIs('prefect.violationAnecdotal') ? 'active' : '' }}">
            <a href="{{ route('prefect.violationAnecdotal') }}"><i class="fas fa-book"></i> Violations Anecdotal</a>
        </li> --}}
        <li class="{{ request()->routeIs('prefect.complaints') ? 'active' : '' }}">
            <a href="{{ route('prefect.complaints') }}"><i class="fas fa-comments"></i> Complaints</a>
        </li>
        {{-- <li class="{{ request()->routeIs('prefect.complaintsAnecdotal') ? 'active' : '' }}">
            <a href="{{ route('prefect.complaintsAnecdotal') }}"><i class="fas fa-comments"></i> Complaints Anecdotal</a>
        </li> --}}
        <li class="{{ request()->routeIs('offenses.sanctions') ? 'active' : '' }}">
            <a href="{{ route('offenses.sanctions') }}"><i class="fas fa-exclamation-triangle"></i> Offense & Sanctions</a>
        </li>
        <li class="{{ request()->routeIs('report.generate') ? 'active' : '' }}">
            <a href="{{ route('report.generate') }}"><i class="fas fa-chart-line"></i> Reports</a>
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


<!-- Main content area -->
<main class="main-content">
    <header class="main-header">
        <div class="header-left">
            <h2>Student Violation Tracking System</h2>
        </div>
        <div class="header-right">
            <div class="user-info" onclick="toggleProfileDropdown()">
                <img id="header-profile-image" src="/images/user.jpg" alt="User"
                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #3498db;">
                <span id="header-user-name">Loading...</span>
                <i class="fas fa-caret-down"></i>
            </div>
            <div class="profile-dropdown" id="profileDropdown">
                <a href="#" onclick="openProfileModal()">
                    <i class="fas fa-user-cog"></i> Profile Settings
                </a>
                <a href="#" onclick="openProfileModal('password-tab')">
                    <i class="fas fa-lock"></i> Change Password
                </a>
                <a href="#" onclick="event.preventDefault(); logout();">
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
        sendVerificationCode: '{{ route("prefect.send-verification-code") }}',
        changePassword: '{{ route("prefect.change-password") }}',
        profileInfo: '{{ route("prefect.profile-info") }}',
        uploadProfileImage: '{{ route("prefect.upload-profile-image") }}',
        removeProfileImage: '{{ route("prefect.remove-profile-image") }}',
        logout: '{{ route("prefect.logout") }}',
        login: '{{ route("login") }}'
    };
    const CSRF_TOKEN = '{{ csrf_token() }}';
</script>

<script src="{{ asset('js/prefect/layout.js') }}"></script>
</body>
</html>
