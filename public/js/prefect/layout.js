// Global variable to store user data
let userData = null;

// Profile Settings Modal Functions
let verificationTimer;
let resendTimer;
let resendTimeLeft = 60;

// Load user profile data
async function loadUserProfile() {
    try {
        const response = await fetch(ROUTES.profileInfo, {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": CSRF_TOKEN,
            },
        });

        if (response.ok) {
            userData = await response.json();
            updateProfileDisplay();
        } else {
            console.error("Failed to load profile data");
        }
    } catch (error) {
        console.error("Error loading profile:", error);
    }
}

// Update all profile displays with user data
function updateProfileDisplay() {
    if (!userData) return;

    // Update header
    const headerUserName = document.getElementById("header-user-name");
    if (headerUserName) {
        headerUserName.textContent = userData.name;
    }

    // Update profile images if available
    const profileImagePreview = document.getElementById(
        "profile-image-preview"
    );
    const headerProfileImage = document.getElementById("header-profile-image");

    if (
        userData.profile_image &&
        userData.profile_image !== "/images/user.jpg"
    ) {
        const timestamp = new Date().getTime();
        const imageUrl = userData.profile_image + "?t=" + timestamp;
        if (profileImagePreview) profileImagePreview.src = imageUrl;
        if (headerProfileImage) headerProfileImage.src = imageUrl;
    } else {
        // Use default image
        if (profileImagePreview) profileImagePreview.src = "/images/user.jpg";
        if (headerProfileImage) headerProfileImage.src = "/images/user.jpg";
    }

    // Update profile tab
    const profileName = document.getElementById("profile-name");
    const profileEmail = document.getElementById("profile-email");
    const profileGender = document.getElementById("profile-gender");
    const profileContact = document.getElementById("profile-contact");
    const profileStatus = document.getElementById("profile-status");

    if (profileName) profileName.textContent = userData.name;
    if (profileEmail) profileEmail.textContent = userData.email;
    if (profileGender) {
        profileGender.textContent = userData.gender
            ? userData.gender.charAt(0).toUpperCase() + userData.gender.slice(1)
            : "Not specified";
    }
    if (profileContact)
        profileContact.textContent = userData.contact || "Not specified";
    if (profileStatus) {
        profileStatus.textContent = userData.status
            ? userData.status.charAt(0).toUpperCase() + userData.status.slice(1)
            : "Active";
    }

    // Update password tab email
    const userEmail = document.getElementById("user-email");
    if (userEmail) {
        userEmail.textContent = userData.email;
    }
}

async function openProfileModal(tab = "profile-tab") {
    // Load fresh user data when opening modal
    await loadUserProfile();

    const modal = document.getElementById("profileSettingsModal");
    if (modal) {
        modal.style.display = "block";
    }
    closeProfileDropdown();
    openTab(tab);
    resetPasswordForm();
}

function closeProfileModal() {
    const modal = document.getElementById("profileSettingsModal");
    if (modal) {
        modal.style.display = "none";
    }
    resetPasswordForm();
    clearTimers();
}

function openTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll(".tab-content").forEach((tab) => {
        tab.classList.remove("active");
    });

    // Remove active class from all tab buttons
    document.querySelectorAll(".tab-btn").forEach((btn) => {
        btn.classList.remove("active");
    });

    // Show the selected tab content
    const targetTab = document.getElementById(tabName);
    if (targetTab) {
        targetTab.classList.add("active");
    }

    // Activate the clicked tab button
    if (event && event.currentTarget) {
        event.currentTarget.classList.add("active");
    }
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const icon = input.nextElementSibling.querySelector("i");
    if (!icon) return;

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

function clearTimers() {
    if (verificationTimer) clearInterval(verificationTimer);
    if (resendTimer) clearInterval(resendTimer);
    resendTimeLeft = 60;
}

// Send verification code
async function sendVerificationCode() {
    const sendBtn = document.getElementById("send-code-btn");
    const resendBtn = document.getElementById("resend-code-btn");

    if (!sendBtn) return;

    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    try {
        const response = await fetch(ROUTES.sendVerificationCode, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": CSRF_TOKEN,
                Accept: "application/json",
                "Content-Type": "application/json",
            },
        });

        const data = await response.json();

        if (response.ok) {
            showAlert("Verification code sent to your email!", "success");

            // Show verification code input and password fields
            const step1 = document.getElementById("verification-step-1");
            const step2 = document.getElementById("verification-step-2");
            const step3 = document.getElementById("verification-step-3");

            if (step1) step1.style.display = "none";
            if (step2) step2.style.display = "block";
            if (step3) step3.style.display = "block";

            // Start countdown for resend
            startResendCountdown();
        } else {
            showAlert(
                data.message || "Failed to send verification code",
                "error"
            );
            sendBtn.disabled = false;
            sendBtn.innerHTML =
                '<i class="fas fa-paper-plane"></i> Send Verification Code';
        }
    } catch (error) {
        console.error("Error sending verification code:", error);
        showAlert("An error occurred. Please try again.", "error");
        sendBtn.disabled = false;
        sendBtn.innerHTML =
            '<i class="fas fa-paper-plane"></i> Send Verification Code';
    }
}

function startResendCountdown() {
    const resendBtn = document.getElementById("resend-code-btn");
    const timerSpan = document.getElementById("resend-timer");

    if (!resendBtn || !timerSpan) return;

    resendBtn.disabled = true;
    resendTimeLeft = 60;

    resendTimer = setInterval(() => {
        resendTimeLeft--;
        timerSpan.textContent = resendTimeLeft;

        if (resendTimeLeft <= 0) {
            clearInterval(resendTimer);
            resendBtn.disabled = false;
            resendBtn.innerHTML = '<i class="fas fa-redo"></i> Resend Code';
        }
    }, 1000);
}

// Password strength indicator
function initializePasswordStrength() {
    const newPasswordInput = document.getElementById("new_password");
    if (newPasswordInput) {
        newPasswordInput.addEventListener("input", function () {
            const password = this.value;
            const strengthDiv = document.getElementById("password-strength");
            if (!strengthDiv) return;

            if (password.length === 0) {
                strengthDiv.textContent = "";
                return;
            }

            let strength = 0;

            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            const strengthText = [
                "Very Weak",
                "Weak",
                "Fair",
                "Good",
                "Strong",
                "Very Strong",
            ][strength];
            const strengthColor = [
                "#e74c3c",
                "#e74c3c",
                "#f39c12",
                "#f39c12",
                "#27ae60",
                "#27ae60",
            ][strength];

            strengthDiv.textContent = `Password Strength: ${strengthText}`;
            strengthDiv.style.color = strengthColor;
        });
    }
}

// Form submission
function initializeFormSubmission() {
    const form = document.getElementById("changePasswordForm");
    if (form) {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();
            await changePassword();
        });
    }
}

async function changePassword() {
    const form = document.getElementById("changePasswordForm");
    const submitBtn = document.getElementById("change-password-btn");

    if (!form || !submitBtn) return;

    // Clear previous errors
    document.querySelectorAll(".error-message").forEach((el) => {
        el.textContent = "";
    });

    // Remove existing alerts
    const existingAlert = document.querySelector(".alert");
    if (existingAlert) {
        existingAlert.remove();
    }

    // Validate verification code
    const verificationCodeInput = document.getElementById("verification_code");
    const verificationCode = verificationCodeInput
        ? verificationCodeInput.value
        : "";
    if (!verificationCode || verificationCode.length !== 6) {
        showAlert("Please enter a valid 6-digit verification code", "error");
        return;
    }

    // Validate passwords
    const newPasswordInput = document.getElementById("new_password");
    const confirmPasswordInput = document.getElementById(
        "new_password_confirmation"
    );
    const newPassword = newPasswordInput ? newPasswordInput.value : "";
    const confirmPassword = confirmPasswordInput
        ? confirmPasswordInput.value
        : "";

    if (newPassword.length < 8) {
        showAlert("Password must be at least 8 characters long", "error");
        return;
    }

    if (newPassword !== confirmPassword) {
        showAlert("Passwords do not match", "error");
        return;
    }

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Changing Password...';

    try {
        const formData = new FormData(form);
        const response = await fetch(ROUTES.changePassword, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": CSRF_TOKEN,
                Accept: "application/json",
            },
            body: formData,
        });

        const data = await response.json();

        if (response.ok) {
            showAlert("Password changed successfully!", "success");
            setTimeout(() => {
                closeProfileModal();
            }, 2000);
        } else {
            // Validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach((field) => {
                    const errorElement = document.getElementById(
                        field + "_error"
                    );
                    if (errorElement) {
                        errorElement.textContent = data.errors[field][0];
                    }
                });
            } else if (data.message) {
                showAlert(data.message, "error");
            }
        }
    } catch (error) {
        console.error("Error changing password:", error);
        showAlert("An error occurred. Please try again.", "error");
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-key"></i> Change Password';
    }
}

function showAlert(message, type) {
    // Remove existing alerts
    const existingAlert = document.querySelector(".alert");
    if (existingAlert) {
        existingAlert.remove();
    }

    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `<i class="fas fa-${
        type === "success" ? "check-circle" : "exclamation-circle"
    }"></i> ${message}`;

    const modalBody = document.querySelector(".modal-body");
    if (modalBody) {
        modalBody.insertBefore(alertDiv, modalBody.firstChild);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

function resetPasswordForm() {
    const form = document.getElementById("changePasswordForm");
    if (form) {
        form.reset();
    }

    // Clear error messages
    document.querySelectorAll(".error-message").forEach((el) => {
        el.textContent = "";
    });

    const strengthDiv = document.getElementById("password-strength");
    if (strengthDiv) {
        strengthDiv.textContent = "";
    }

    // Reset steps
    const step1 = document.getElementById("verification-step-1");
    const step2 = document.getElementById("verification-step-2");
    const step3 = document.getElementById("verification-step-3");

    if (step1) step1.style.display = "block";
    if (step2) step2.style.display = "none";
    if (step3) step3.style.display = "none";

    // Reset buttons
    const sendBtn = document.getElementById("send-code-btn");
    if (sendBtn) {
        sendBtn.disabled = false;
        sendBtn.innerHTML =
            '<i class="fas fa-paper-plane"></i> Send Verification Code';
    }

    const resendBtn = document.getElementById("resend-code-btn");
    if (resendBtn) {
        resendBtn.disabled = true;
        resendBtn.innerHTML =
            '<i class="fas fa-redo"></i> Resend Code (<span id="resend-timer">60</span>s)';
    }

    clearTimers();
}

// Profile dropdown functions
function toggleProfileDropdown() {
    const dropdown = document.getElementById("profileDropdown");
    if (dropdown) {
        dropdown.style.display =
            dropdown.style.display === "block" ? "none" : "block";
    }
}

function closeProfileDropdown() {
    const dropdown = document.getElementById("profileDropdown");
    if (dropdown) {
        dropdown.style.display = "none";
    }
}

// Enhanced verification code input handling
function initializeVerificationCodeInput() {
    const verificationInput = document.getElementById("verification_code");
    if (verificationInput) {
        verificationInput.addEventListener("input", function (e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, "");

            // Auto-advance to password field if code is complete
            if (this.value.length === 6) {
                const newPasswordInput =
                    document.getElementById("new_password");
                if (newPasswordInput) {
                    newPasswordInput.focus();
                }
            }
        });
    }
}

// Auto-validate password confirmation
function initializePasswordConfirmation() {
    const confirmInput = document.getElementById("new_password_confirmation");
    if (confirmInput) {
        confirmInput.addEventListener("input", function () {
            const newPasswordInput = document.getElementById("new_password");
            const newPassword = newPasswordInput ? newPasswordInput.value : "";
            const confirmPassword = this.value;
            const errorElement = document.getElementById(
                "new_password_confirmation_error"
            );

            if (errorElement) {
                if (confirmPassword && newPassword !== confirmPassword) {
                    errorElement.textContent = "Passwords do not match";
                } else {
                    errorElement.textContent = "";
                }
            }
        });
    }
}

// Profile Picture Functions - FIXED VERSION
function initializeProfileImageUpload() {
    const profileImageInput = document.getElementById("profile-image-input");
    const profileImagePreview = document.getElementById(
        "profile-image-preview"
    );
    const headerProfileImage = document.getElementById("header-profile-image");
    const profileImageContainer = document.querySelector(
        ".profile-image-container"
    );

    console.log("Initializing profile image upload...");
    console.log("Profile image input:", profileImageInput);
    console.log("Profile image container:", profileImageContainer);

    if (profileImageInput) {
        profileImageInput.addEventListener("change", function (e) {
            console.log("File input changed");
            const file = e.target.files[0];
            if (file) {
                console.log("File selected:", file.name, file.type, file.size);

                // Validate file type
                if (!file.type.match("image.*")) {
                    showProfileImageError(
                        "Please select a valid image file (JPEG, PNG, GIF)"
                    );
                    return;
                }

                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showProfileImageError("Image size must be less than 2MB");
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function (e) {
                    console.log("File read successfully");
                    if (profileImagePreview) {
                        profileImagePreview.src = e.target.result;
                        console.log("Preview image updated");
                    }
                    if (headerProfileImage) {
                        headerProfileImage.src = e.target.result;
                        console.log("Header image updated");
                    }
                    hideProfileImageError();

                    // Upload image automatically
                    uploadProfileImage(file);
                };
                reader.onerror = function (error) {
                    console.error("Error reading file:", error);
                    showProfileImageError("Error reading image file");
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        console.error("Profile image input not found!");
    }

    // Add hover effects
    if (profileImageContainer) {
        profileImageContainer.addEventListener("mouseenter", function () {
            const overlay = this.querySelector(".profile-image-overlay");
            if (overlay) overlay.style.display = "flex";
        });

        profileImageContainer.addEventListener("mouseleave", function () {
            const overlay = this.querySelector(".profile-image-overlay");
            if (overlay) overlay.style.display = "none";
        });

        // Click to upload
        profileImageContainer.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log("Profile image container clicked");
            document.getElementById("profile-image-input").click();
        });
    } else {
        console.error("Profile image container not found!");
    }
}

function uploadProfileImage(file) {
    console.log("Starting upload for file:", file.name);

    const formData = new FormData();
    formData.append("profile_image", file);
    formData.append("_token", CSRF_TOKEN);

    // Show loading state
    const uploadBtn = document.querySelector(".btn-send-code");
    const originalText = uploadBtn ? uploadBtn.innerHTML : "";

    if (uploadBtn) {
        uploadBtn.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        uploadBtn.disabled = true;
    }

    fetch(ROUTES.uploadProfileImage, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": CSRF_TOKEN,
        },
        body: formData,
    })
        .then((response) => {
            console.log("Upload response status:", response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            console.log("Upload response data:", data);
            if (data.success) {
                showAlert("Profile picture updated successfully!", "success");

                // Update both preview and header image with cache-busting
                const profileImagePreview = document.getElementById(
                    "profile-image-preview"
                );
                const headerProfileImage = document.getElementById(
                    "header-profile-image"
                );

                if (data.image_url) {
                    const timestamp = new Date().getTime();
                    const imageUrl = data.image_url + "?t=" + timestamp;

                    if (profileImagePreview) profileImagePreview.src = imageUrl;
                    if (headerProfileImage) headerProfileImage.src = imageUrl;

                    console.log("Profile images updated with URL:", imageUrl);
                }

                // Reload user data to get updated profile info
                loadUserProfile();
            } else {
                showAlert(
                    data.message || "Failed to upload profile picture",
                    "error"
                );
                // Reset to default image on failure
                resetToDefaultImage();
            }
        })
        .catch((error) => {
            console.error("Upload error:", error);
            showAlert("An error occurred while uploading the image", "error");
            resetToDefaultImage();
        })
        .finally(() => {
            // Reset button state
            if (uploadBtn) {
                uploadBtn.innerHTML =
                    '<i class="fas fa-upload"></i> Upload Photo';
                uploadBtn.disabled = false;
            }
        });
}

function removeProfileImage() {
    if (!confirm("Are you sure you want to remove your profile picture?")) {
        return;
    }

    fetch(ROUTES.removeProfileImage, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": CSRF_TOKEN,
            Accept: "application/json",
            "Content-Type": "application/json",
        },
        body: JSON.stringify({}),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showAlert("Profile picture removed successfully!", "success");
                resetToDefaultImage();
                // Reload user data
                loadUserProfile();
            } else {
                showAlert(
                    data.message || "Failed to remove profile picture",
                    "error"
                );
            }
        })
        .catch((error) => {
            console.error("Remove error:", error);
            showAlert("An error occurred while removing the image", "error");
        });
}

function resetToDefaultImage() {
    const defaultImage = "/images/user.jpg";
    const profileImagePreview = document.getElementById(
        "profile-image-preview"
    );
    const headerProfileImage = document.getElementById("header-profile-image");

    if (profileImagePreview) profileImagePreview.src = defaultImage;
    if (headerProfileImage) headerProfileImage.src = defaultImage;
}

function showProfileImageError(message) {
    const errorElement = document.getElementById("profile-image-error");
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = "block";
    }
}

function hideProfileImageError() {
    const errorElement = document.getElementById("profile-image-error");
    if (errorElement) {
        errorElement.textContent = "";
        errorElement.style.display = "none";
    }
}

// Logout Modal Functions
function logout() {
    // Show the logout confirmation modal
    const logoutModal = document.getElementById('logoutModal');
    if (logoutModal) {
        logoutModal.style.display = 'block';
    }
    closeProfileDropdown();
}

function closeLogoutModal() {
    const logoutModal = document.getElementById('logoutModal');
    if (logoutModal) {
        logoutModal.style.display = 'none';
    }
}

function confirmLogout() {
    // Create a form to submit the logout request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = ROUTES.logout;
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = CSRF_TOKEN;
    form.appendChild(csrfInput);
    
    // Add to document and submit
    document.body.appendChild(form);
    form.submit();
}

// Initialize all event listeners
function initializeEventListeners() {
    console.log("Initializing event listeners...");

    // Existing dropdown functionality
    document.querySelectorAll(".dropdown-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
            const container = btn.nextElementSibling;
            document.querySelectorAll(".dropdown-container").forEach((el) => {
                if (el !== container) el.style.display = "none";
            });
            container.style.display =
                container.style.display === "block" ? "none" : "block";
        });
    });

    // Add event listener for tab switching
    document.querySelectorAll(".tab-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const onclickAttr = this.getAttribute("onclick");
            if (onclickAttr) {
                const match = onclickAttr.match(/'([^']+)'/);
                if (match && match[1]) {
                    openTab(match[1]);
                }
            }
        });
    });

    // Close modals when clicking outside
    window.onclick = function (event) {
        const modal = document.getElementById("profileSettingsModal");
        if (event.target === modal) {
            closeProfileModal();
        }

        const logoutModal = document.getElementById('logoutModal');
        if (event.target === logoutModal) {
            closeLogoutModal();
        }

        // Close dropdown when clicking outside
        const profileDropdown = document.getElementById("profileDropdown");
        const userInfo = document.querySelector(".user-info");
        if (
            userInfo &&
            profileDropdown &&
            !userInfo.contains(event.target) &&
            !profileDropdown.contains(event.target)
        ) {
            profileDropdown.style.display = "none";
        }
    };

    // Close modals with escape key
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeProfileModal();
            closeLogoutModal();
        }
    });

    // Close modals when clicking X
    const closeBtn = document.querySelector(".close");
    if (closeBtn) {
        closeBtn.addEventListener("click", closeProfileModal);
    }

    // Close logout modal when clicking its close button
    const logoutCloseBtn = document.querySelector("#logoutModal .close");
    if (logoutCloseBtn) {
        logoutCloseBtn.addEventListener("click", closeLogoutModal);
    }

    // Initialize form handlers
    initializeFormSubmission();
    initializePasswordStrength();
    initializeVerificationCodeInput();
    initializePasswordConfirmation();

    // Initialize profile image upload - THIS IS CRITICAL
    initializeProfileImageUpload();
}

// Load user profile when page loads
document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM loaded, initializing application...");
    loadUserProfile();
    initializeEventListeners();
});

// Debug function to check modal state
function debugModalState() {
    console.log("Modal State:");
    console.log(
        "Step 1:",
        document.getElementById("verification-step-1")?.style.display
    );
    console.log(
        "Step 2:",
        document.getElementById("verification-step-2")?.style.display
    );
    console.log(
        "Step 3:",
        document.getElementById("verification-step-3")?.style.display
    );
    console.log("User Data:", userData);
}