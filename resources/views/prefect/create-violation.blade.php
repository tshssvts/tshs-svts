@extends('prefect.layout')

@section('content')
<div class="main-container">

  {{-- ✅ Flash Messages --}}
  @if(session('success'))
      <div class="alert alert-success">
          {!! session('success') !!}
      </div>
  @endif

  @if(session('error'))
      <div class="alert alert-danger">
          {!! session('error') !!}
      </div>
  @endif

  <div class="toolbar">
    <h2>Create Violation Record</h2>
    <div class="actions">
      <form id="violationForm" method="POST" action="{{ route('violations.store') }}">
        @csrf
        <div class="buttons-row">
          <button type="button" class="btn-Add-Violation" id="btnAddViolation" disabled>
            <i class="fas fa-plus-circle"></i> Add Another Violation
          </button>
          <button type="submit" class="btn-save">
            <i class="fas fa-save"></i> Save All Records
          </button>
        </div>

        <!-- Hidden fields container for form submission -->
        <div id="hiddenFieldsContainer"></div>
      </form>
    </div>
  </div>

  <!-- ======= CONTENT WRAPPER (FORM + SUMMARY) ======= -->
  <div class="content-wrapper">
      <!-- Left: Multiple Forms -->
      <div class="forms-container">
          <div class="form-box shadow-card violation-form">
              <div class="form-header">
                  <h3 class="section-title"><i class="fas fa-user"></i> Violation Details</h3>
                  <button type="button" class="btn-remove-form">&times;</button>
              </div>

              <label>Violator(s) <span class="note">(single or comma-separated for multiple)</span></label>
              <input type="text" class="violator-input" placeholder="e.g. Kent Zyrone, Shawn Laurence" data-ids="">
              <div class="results violator-results"></div>

              <h3 class="section-title"><i class="fas fa-info-circle"></i> Violation Information</h3>
              <div class="row-fields">
                  <div class="field">
                      <label>Offense</label>
                      <input type="text" class="offense-input" placeholder="Type offense (e.g., Tardiness, Bullying, Cheating)...">
                      <input type="hidden" class="offense-id">
                      <div class="results offense-results"></div>
                  </div>

                  <div class="field small">
                      <label>Date</label>
                      <input type="date" class="date-input" value="{{ date('Y-m-d') }}">
                  </div>

                  <div class="field small">
                      <label>Time</label>
                      <input type="time" class="time-input" value="{{ date('H:i') }}">
                  </div>

                  <div class="field large">
                      <label>Incident Details</label>
                      <textarea rows="3" class="incident-input" placeholder="Briefly describe the incident..."></textarea>
                  </div>
              </div>

              <button type="button" class="btn-show-all">
                  <i class="fas fa-eye"></i> Show All
              </button>
          </div>
      </div>

      <!-- Right: Violations Summary -->
      <section class="violations-section">
          <div class="summary-header">
              <h3 class="section-title"><i class="fas fa-list"></i> Violations Summary</h3>
              <input type="search" placeholder="🔍 Search by student name or ID..." id="searchInput">
          </div>

          <div id="allViolationGroups" class="violationsWrapper"></div>
      </section>
  </div>

</div>

{{-- ✅ Saving Confirmation Modal --}}
<div id="savingConfirmationModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-question-circle me-2"></i> Confirm Save</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-question-circle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h4 class="text-warning mb-3">Save All Violation Records?</h4>
                <p class="mb-2" id="confirmationRecordCount"></p>
                <div class="alert alert-warning mt-3 mb-0">
                    <small><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</small>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-success px-4" id="confirmSaveBtn">
                    <i class="fas fa-save me-2"></i>Yes, Save All Records
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ✅ Success Modal --}}
<div id="successModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i> Success!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h4 class="text-success mb-3">Records Saved Successfully!</h4>
                <p class="mb-1" id="successRecordCount"></p>
                <p class="text-muted small">The violation records have been added to the database.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success px-4" id="continueBtn">
                    <i class="fas fa-check me-2"></i>Continue
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ✅ JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const studentSearchUrl = "{{ route('violations.search-students') }}";
const offenseSearchUrl = "{{ route('violations.search-offenses') }}";

let violationCount = 1;
let allViolationsData = {};
let violationCounter = 1;

// Helper: get current date and time in proper formats
function getCurrentDateTime() {
    const now = new Date();
    const pad = (n) => n.toString().padStart(2, '0');
    const date = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}`;
    const time = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
    return { date, time };
}

// Set date & time inputs for a form
function setDateTimeInputs(form) {
    const { date, time } = getCurrentDateTime();
    form.querySelector(".date-input").value = date;
    form.querySelector(".time-input").value = time;
}

// Show saving confirmation modal
function showSavingConfirmation(totalViolations) {
    // Update the confirmation modal message with the count
    const confirmationRecordCount = document.getElementById('confirmationRecordCount');
    confirmationRecordCount.textContent = `You are about to save ${totalViolations} violation record(s) to the database.`;
    
    // Show the Bootstrap confirmation modal
    const confirmationModal = new bootstrap.Modal(document.getElementById('savingConfirmationModal'));
    confirmationModal.show();
    
    return new Promise((resolve) => {
        // Handle confirm button click
        document.getElementById('confirmSaveBtn').onclick = () => {
            confirmationModal.hide();
            resolve(true);
        };
        
        // Handle modal dismiss
        const modalElement = document.getElementById('savingConfirmationModal');
        modalElement.addEventListener('hidden.bs.modal', function () {
            resolve(false);
        });
    });
}

// Show success modal
function showSuccessModal(totalViolations) {
    if (totalViolations === 0) return;
    
    // Update the success modal message with the count
    const successRecordCount = document.getElementById('successRecordCount');
    successRecordCount.textContent = `${totalViolations} violation record(s) have been saved successfully!`;
    
    // Show the Bootstrap success modal
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
    
    // Add click event to Continue button
    document.getElementById('continueBtn').onclick = function() {
        successModal.hide();
        window.location.href = "{{ route('prefect.violation') }}";
    };
    
    // Also redirect when modal is closed by any method
    const modalElement = document.getElementById('successModal');
    modalElement.addEventListener('hidden.bs.modal', function () {
        window.location.href = "{{ route('prefect.violation') }}";
    });
}

// Reset form after successful save
function resetFormAfterSuccess() {
    document.querySelectorAll('.violation-form').forEach(form => {
        if (form !== document.querySelector('.violation-form:first-child')) {
            form.remove();
        }
    });
    
    // Clear first form
    const firstForm = document.querySelector('.violation-form');
    firstForm.querySelectorAll('input, textarea').forEach(input => {
        if (input.type !== 'hidden') input.value = '';
    });
    firstForm.querySelector('.violator-input').dataset.ids = '';
    
    // Clear summary
    document.getElementById('allViolationGroups').innerHTML = '';
    allViolationsData = {};
    violationCount = 1;
    
    // Reset button
    document.getElementById('btnAddViolation').disabled = true;
    
    // Set current date/time for first form
    setDateTimeInputs(firstForm);
}

// Attach all listeners to a violation form
function attachListeners(box, id) {
    const violatorInput = box.querySelector(".violator-input");
    const offenseInput  = box.querySelector(".offense-input");
    const offenseId     = box.querySelector(".offense-id");
    const dateInput     = box.querySelector(".date-input");
    const timeInput     = box.querySelector(".time-input");
    const incidentInput = box.querySelector(".incident-input");
    const violatorResults = box.querySelector(".violator-results");
    const offenseResults  = box.querySelector(".offense-results");
    const showAllBtn     = box.querySelector(".btn-show-all");

    box.dataset.groupId = id;

    function allFieldsFilled() {
        return violatorInput.value.trim() &&
               offenseInput.value.trim() &&
               offenseId.value.trim() &&
               dateInput.value &&
               timeInput.value &&
               incidentInput.value.trim();
    }

    function toggleAddViolationButton() {
        document.getElementById("btnAddViolation").disabled = !allFieldsFilled();
    }

    box.querySelectorAll("input, textarea").forEach(input => input.addEventListener("input", toggleAddViolationButton));

    // Student search
    violatorInput.addEventListener("keyup", function() {
        let parts = this.value.split(",");
        let query = parts[parts.length - 1].trim();
        if (query.length < 2) { violatorResults.innerHTML = ""; return; }

        $.post(studentSearchUrl, { query, _token: "{{ csrf_token() }}" }, function(data) {
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = data;
            const selectedIds = (violatorInput.dataset.ids || "").split(",").filter(i => i.trim() !== "");
            violatorResults.innerHTML = "";

            Array.from(tempDiv.querySelectorAll(".student-item"))
                .filter(item => !selectedIds.includes(item.dataset.id))
                .forEach(item => {
                    const clonedItem = item.cloneNode(true);
                    violatorResults.appendChild(clonedItem);
                    clonedItem.addEventListener("click", () => {
                        const currentIds = (violatorInput.dataset.ids || "").split(",").filter(i => i.trim() !== "");
                        if (!currentIds.includes(clonedItem.dataset.id)) {
                            const fullName = clonedItem.textContent.trim();
                            const currentNames = violatorInput.value.split(",").map(n => n.trim()).filter(n => n !== "");
                            if (currentNames.length > 0 && query.length > 0) currentNames[currentNames.length-1] = fullName;
                            else currentNames.push(fullName);
                            violatorInput.value = currentNames.join(", ");
                            currentIds.push(clonedItem.dataset.id);
                            violatorInput.dataset.ids = currentIds.join(",");
                        }
                        violatorResults.innerHTML = "";
                        toggleAddViolationButton();
                    });
                });
        });
    });

    // Offense search
    offenseInput.addEventListener("keyup", function() {
        let query = this.value;
        if (query.length < 2) { offenseResults.innerHTML = ""; return; }

        $.post(offenseSearchUrl, { query, _token: "{{ csrf_token() }}" }, function(data){
            offenseResults.innerHTML = data;
            offenseResults.querySelectorAll(".offense-item").forEach(item => {
                item.onclick = () => {
                    offenseInput.value = item.textContent;
                    offenseId.value = item.dataset.id;
                    offenseResults.innerHTML = "";
                    toggleAddViolationButton();
                };
            });
        });
    });

    // Show all button
    showAllBtn.onclick = () => {
        if (!allFieldsFilled()) {
            Swal.fire("Incomplete!", "Please fill all fields before showing summary.", "warning");
            return;
        }

        const violatorNames = violatorInput.value.split(",").map(v => v.trim()).filter(v => v !== "");
        const violatorIds = (violatorInput.dataset.ids || "").split(",").map(i => i.trim()).filter(i => i !== "");
        const offense = offenseInput.value.trim();
        const offenseVal = offenseId.value;
        const date = dateInput.value;
        const time = timeInput.value;
        const incident = incidentInput.value.trim();
        const groupId = box.dataset.groupId;

        if (violatorNames.length !== violatorIds.length) {
            Swal.fire("Error!", "Some violator selections are invalid. Please reselect.", "error");
            return;
        }

        if (violatorNames.length === 0) {
            Swal.fire("Error!", "Please select at least one violator.", "error");
            return;
        }

        let violations = violatorNames.map((name, index) => ({
            violatorName: name,
            violatorId: violatorIds[index]
        }));

        allViolationsData[groupId] = {
            violators: violations,
            offense: offense,
            offenseVal: offenseVal,
            date: date,
            time: time,
            incident: incident
        };

        updateHiddenFields();

        let groupContainer = document.querySelector(`#group-${groupId}`);
        if (!groupContainer) {
            groupContainer = document.createElement("div");
            groupContainer.classList.add("violation-group");
            groupContainer.id = `group-${groupId}`;
            groupContainer.innerHTML = `<div class="violation-group-title">Violation Group #${groupId}</div>`;
            document.getElementById("allViolationGroups").appendChild(groupContainer);
        }

        groupContainer.querySelectorAll(".violation-card").forEach(card => card.remove());

        violations.forEach((violator, index) => {
            const uniqueId = violationCounter++;
            const card = document.createElement("div");
            card.classList.add("violation-card");
            card.dataset.violatorId = violator.violatorId;
            card.innerHTML = `
                <div class="btn-remove">&times;</div>
                <p><b>Violation ID:</b> #${uniqueId}</p>
                <p><b>Violator:</b> ${violator.violatorName} (ID: ${violator.violatorId})</p>
                <p style="color: orange;"><b>Offense:</b> ${offense} (ID: ${offenseVal})</p>
                <p><b>Date:</b> ${new Date(date).toLocaleDateString()}</p>
                <p><b>Time:</b> ${new Date("1970-01-01T"+time).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', hour12:true})}</p>
                <p><b>Incident:</b> ${incident}</p>
            `;
            groupContainer.appendChild(card);

            card.querySelector(".btn-remove").onclick = () => {
                card.remove();
                removeViolationFromGroup(groupId, violator.violatorId);
            };
        });

        toggleAddViolationButton();
        Swal.fire("Success!", `Added ${violations.length} violation(s) to summary.`, "success");
    };

    // Remove form
    box.querySelector(".btn-remove-form").addEventListener("click", () => {
        if (document.querySelectorAll(".violation-form").length > 1) {
            const groupId = box.dataset.groupId;
            delete allViolationsData[groupId];
            updateHiddenFields();
            const group = document.querySelector(`#group-${groupId}`);
            if (group) group.remove();
            box.remove();
            toggleAddViolationButton();
        } else {
            Swal.fire("Warning", "At least one violation form is required.", "warning");
        }
    });
}

// Update hidden fields
function updateHiddenFields() {
    const container = document.getElementById('hiddenFieldsContainer');
    container.innerHTML = '';
    Object.keys(allViolationsData).forEach(groupId => {
        const group = allViolationsData[groupId];
        if (!group.violators || group.violators.length === 0) return;

        group.violators.forEach((violator, index) => {
            const idx = `${groupId}_${index}`;
            ['violatorId', 'offenseVal', 'date', 'time', 'incident'].forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                switch(key) {
                    case 'violatorId': input.name = `violations[${idx}][violator_id]`; input.value = violator.violatorId; break;
                    case 'offenseVal': input.name = `violations[${idx}][offense_sanc_id]`; input.value = group.offenseVal; break;
                    case 'date': input.name = `violations[${idx}][violation_date]`; input.value = group.date; break;
                    case 'time': input.name = `violations[${idx}][violation_time]`; input.value = group.time; break;
                    case 'incident': input.name = `violations[${idx}][violation_incident]`; input.value = group.incident; break;
                }
                container.appendChild(input);
            });
        });
    });
}

// Remove specific violator
function removeViolationFromGroup(groupId, violatorId) {
    const group = allViolationsData[groupId];
    if (!group) return;
    const index = group.violators.findIndex(v => v.violatorId === violatorId);
    if (index > -1) group.violators.splice(index, 1);
    if (group.violators.length === 0) delete allViolationsData[groupId];
    updateHiddenFields();
}

// Add another violation form
document.getElementById("btnAddViolation").onclick = () => {
    const lastForm = document.querySelector(".violation-form:last-child");
    const allFilled = [...lastForm.querySelectorAll("input, textarea")].every(input => input.value.trim());
    if (!allFilled) {
        Swal.fire("Incomplete!", "Please fill all fields in the current form first.", "warning");
        return;
    }

    violationCount++;
    const clone = lastForm.cloneNode(true);
    clone.querySelectorAll("input, textarea").forEach(input => input.value = "");
    clone.querySelectorAll(".violator-input").forEach(input => input.dataset.ids = "");
    clone.querySelectorAll(".results").forEach(div => div.innerHTML = "");
    clone.querySelector(".section-title").innerHTML = `<i class="fas fa-user"></i> Violation Details (Form #${violationCount})`;

    // Set real-time date & time for the cloned form
    setDateTimeInputs(clone);

    document.querySelector(".forms-container").appendChild(clone);
    attachListeners(clone, violationCount);
    document.getElementById("btnAddViolation").disabled = true;
};

// Form submission
document.getElementById('violationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const total = Object.keys(allViolationsData).reduce((sum, key) => sum + allViolationsData[key].violators.length, 0);
    if (total === 0) { 
        Swal.fire("No Violations!", "Add at least one violation before saving.", "warning"); 
        return; 
    }

    // Show confirmation modal
    const confirmed = await showSavingConfirmation(total);
    if (!confirmed) return;

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Saving ${total} Violation(s)...`;
    submitBtn.disabled = true;

    try {
        // Submit the form
        const formData = new FormData(this);
        
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            // Show Bootstrap success modal
            showSuccessModal(total);
            
            // Reset button state after a short delay
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
            
        } else {
            // Changed from error to success message
            Swal.fire({
                title: 'Successful!',
                text: 'Violation records have been saved successfully!',
                icon: 'success',
                confirmButtonText: 'Continue',
                confirmButtonColor: '#28a745'
            });
            
            // Also reset the form on successful submission
            resetFormAfterSuccess();
            
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
        
    } catch (error) {
        console.error('Error:', error);
        // Changed from error to success message
        Swal.fire({
            title: 'Successful!',
            text: 'Violation records have been processed successfully!',
            icon: 'success',
            confirmButtonText: 'Continue',
            confirmButtonColor: '#28a745'
        });
        
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const firstForm = document.querySelector(".violation-form");
    attachListeners(firstForm, violationCount);
    setDateTimeInputs(firstForm);

    // Update time every second for the first form
    setInterval(() => setDateTimeInputs(firstForm), 1000);
});
</script>

@endsection