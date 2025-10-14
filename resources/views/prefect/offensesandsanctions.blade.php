@extends('prefect.layout')

@section('content')
<div class="main-container">

  <!-- Toolbar -->
  <div class="toolbar">
    <h2>Offense and Sanctions</h2>
    <div class="actions">
      <input type="search" placeholder="🔍 Search by offense type or description..." id="searchInput">
      <button class="btn-print" id="printBtn">🖨️ Print</button>
      <button class="btn-export" id="exportBtn">📤 Export</button>
    </div>
  </div>

  <!-- Offense & Sanction Table -->
  <div class="table-container">
    <table id="offenseTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Offense Type</th>
          <th>Offense Description</th>
          <th>Sanction(s)</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        @forelse ($offenses as $offense)
          <tr data-details="{{ $offense->offense_type }}|{{ $offense->offense_description }}|{{ $offense->sanctions }}">
            <td>{{ $loop->iteration }}</td>
            <td><span title="{{ $offense->offense_type }}">{{ $offense->offense_type }}</span></td>
            <td>{{ $offense->offense_description }}</td>
            <td>{{ $offense->sanctions }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align:center; padding:15px;">⚠️ No offenses found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
      {{-- {{ $offenses->links() }} --}}
    </div>
  </div>

  <!-- 📝 Details Modal -->
  <div class="modal" id="detailsModal">
    <div class="modal-content">
      <div class="modal-header">📄 Offense & Sanction Details</div>
      <div class="modal-body" id="detailsBody">
        <!-- Content will be filled dynamically via JS -->
      </div>
      <div class="modal-footer">
        <button class="btn-close">❌ Close</button>
      </div>
    </div>
  </div>

  <!-- 🗃️ Archive Modal -->
  <div class="modal" id="archiveModal">
    <div class="modal-content">
      <div class="modal-header">🗃️ Archived Offenses</div>
      <div class="modal-body">
        <div class="modal-actions">
          <label class="select-all-label">
            <input type="checkbox" id="selectAllArchived" class="select-all-checkbox">
            <span>Select All</span>
          </label>

          <div class="search-container">
            <input type="search" placeholder="🔍 Search archived..." id="archiveSearch" class="search-input">
          </div>
        </div>

        <div class="archive-table-container">
          <table class="archive-table">
            <thead>
              <tr>
                <th>✔</th>
                <th>ID</th>
                <th>Offense Type</th>
                <th>Description</th>
                <th>Sanctions</th>
                <th>Date Archived</th>
              </tr>
            </thead>
            <tbody id="archiveTableBody">
              <tr>
                <td><input type="checkbox" class="archivedCheckbox"></td>
                <td>O001</td>
                <td>Tardiness</td>
                <td>Late to class</td>
                <td>Warning, Parent Notification</td>
                <td>2025-09-22</td>
              </tr>
              <tr>
                <td><input type="checkbox" class="archivedCheckbox"></td>
                <td>O002</td>
                <td>Cutting Classes</td>
                <td>Unauthorized absence</td>
                <td>Conference with Adviser</td>
                <td>2025-09-23</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="modal-note">⚠️ Note: Deleting records will permanently remove them.</div>

        <div class="modal-footer">
          <button class="btn-secondary" id="restoreArchivedBtn">🔄 Restore</button>
          <button class="btn-danger" id="deleteArchivedBtn">🗑️ Delete</button>
          <button class="btn-close" id="closeArchive">❌ Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- 📝 Create Anecdotal Modal (if needed) -->
  <div class="modal" id="anecModal">
    <div class="modal-content">
      <div class="modal-header">📝 Create Anecdotal Record</div>
      <div class="modal-body">
        <p>Anecdotal record form would go here...</p>
      </div>
      <div class="modal-footer">
        <button class="btn-primary">Save</button>
        <button class="btn-close">❌ Close</button>
      </div>
    </div>
  </div>

  <!-- 🔔 Notification Modal -->
  <div class="modal" id="notificationModal">
    <div class="modal-content notification-modal-content">
      <div class="modal-header notification-modal-header">
        <div class="notification-header-content">
          <span id="notificationIcon">🔔</span>
          <span id="notificationTitle">Notification</span>
        </div>
      </div>
      <div class="modal-body notification-modal-body" id="notificationBody">
        <!-- Content filled dynamically via JS -->
      </div>
      <div class="modal-footer notification-modal-footer">
        <div class="notification-buttons-container">
          <button class="btn-primary" id="notificationYesBtn">Yes</button>
          <button class="btn-secondary" id="notificationNoBtn">No</button>
          <button class="btn-close" id="notificationCloseBtn">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Include html2pdf library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
// 🔍 Search filter for main table
document.getElementById('searchInput')?.addEventListener('input', function() {
  const filter = this.value.toLowerCase();
  const rows = document.querySelectorAll('#tableBody tr');
  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(filter) ? '' : 'none';
  });
});

// ✅ Select All Main Table
document.getElementById('selectAll')?.addEventListener('change', function() {
  document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
});

// ✅ Move to Trash - Now shows confirmation modal
document.getElementById('moveToTrashBtn')?.addEventListener('click', () => {
  const selected = [...document.querySelectorAll('.rowCheckbox:checked')];
  if(!selected.length) {
    showNotification('⚠️ No Selection', 'Please select at least one offense record to move to trash.', 'warning', {
      yesText: 'OK',
      noText: null,
      onYes: () => {
        document.getElementById('notificationModal').style.display = 'none';
      }
    });
    return;
  }

  showNotification('🗑️ Move to Trash', `Are you sure you want to move ${selected.length} offense record(s) to trash?`, 'confirm', {
    yesText: 'Yes, Move',
    noText: 'Cancel',
    onYes: () => {
      // AJAX call to move to trash
      setTimeout(() => {
        showNotification('✅ Success', `${selected.length} offense record(s) moved to trash successfully.`, 'success', {
          yesText: 'OK',
          noText: null,
          onYes: () => {
            document.getElementById('notificationModal').style.display = 'none';
            // Optionally refresh the page or update the table
          }
        });
      }, 500);
    },
    onNo: () => {
      document.getElementById('notificationModal').style.display = 'none';
    }
  });
});

// 🔹 Row click for Details Modal
document.querySelectorAll('#tableBody tr').forEach(row => {
  row.addEventListener('click', e => {
    // Ignore clicks on checkboxes or Edit button
    if (e.target.type === 'checkbox' || e.target.classList.contains('editBtn')) return;

    // Split the dataset details: offense_type | offense_description | sanctions
    const [offenseType, offenseDescription, sanctions] = row.dataset.details.split('|');

    // Convert sanctions string into a bullet list
    const sanctionList = sanctions.split(',').map(s => `<li>${s.trim()}</li>`).join('');

    document.getElementById('detailsBody').innerHTML = `
      <p><strong>Offense Type:</strong> ${offenseType}</p>
      <p><strong>Description:</strong> ${offenseDescription}</p>
      <p><strong>Sanctions:</strong></p>
      <ul>${sanctionList}</ul>
    `;

    document.getElementById('detailsModal').style.display = 'flex';
  });
});

// 🔹 Close Modals
document.querySelectorAll('.btn-close').forEach(btn => {
  btn.addEventListener('click', () => btn.closest('.modal').style.display = 'none');
});

// 🔹 Set Schedule Button
document.getElementById('setScheduleBtn')?.addEventListener('click', () => {
  showNotification('📅 Set Schedule', 'Open schedule setup form or modal here.', 'info', {
    yesText: 'OK',
    noText: null,
    onYes: () => {
      document.getElementById('notificationModal').style.display = 'none';
    }
  });
});

// 🔹 Send SMS Button
document.getElementById('sendSmsBtn')?.addEventListener('click', () => {
  showNotification('📩 Send SMS', 'Are you sure you want to send an SMS about this offense?', 'confirm', {
    yesText: 'Send SMS',
    noText: 'Cancel',
    onYes: () => {
      // AJAX call to send SMS
      setTimeout(() => {
        showNotification('✅ Success', 'SMS sent successfully!', 'success', {
          yesText: 'OK',
          noText: null,
          onYes: () => {
            document.getElementById('notificationModal').style.display = 'none';
          }
        });
      }, 500);
    },
    onNo: () => {
      document.getElementById('notificationModal').style.display = 'none';
    }
  });
});

// 🔹 Edit Button
document.querySelectorAll('.editBtn').forEach(btn => {
  btn.addEventListener('click', e => {
    e.stopPropagation();
    const [offenseType, offenseDescription, sanctions] = btn.closest('tr').dataset.details.split('|');
    showNotification('✏️ Edit Offense', `Edit offense: ${offenseType}\nDescription: ${offenseDescription}\nSanctions: ${sanctions}`, 'info', {
      yesText: 'OK',
      noText: null,
      onYes: () => {
        document.getElementById('notificationModal').style.display = 'none';
        // TODO: Implement actual edit modal functionality
      }
    });
  });
});

// 🔹 Add Violation Button
document.getElementById('createBtn')?.addEventListener('click', () => {
  showNotification('➕ Add Violation', 'Open violation creation form.', 'info', {
    yesText: 'OK',
    noText: null,
    onYes: () => {
      document.getElementById('notificationModal').style.display = 'none';
    }
  });
});

// 🔹 Open Create Anecdotal Modal
document.getElementById('createAnecBtn')?.addEventListener('click', () => {
  document.getElementById('anecModal').style.display = 'flex';
});

// 🔹 Open Archive Modal
document.getElementById('archiveBtn')?.addEventListener('click', () => {
  document.getElementById('archiveModal').style.display = 'flex';
});

// 🔹 Close Archive Modal
document.getElementById('closeArchive')?.addEventListener('click', () => {
  document.getElementById('archiveModal').style.display = 'none';
});

// 🔹 Dropdown toggle
document.querySelectorAll('.dropdown-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    e.stopPropagation();
    btn.parentElement.classList.toggle('show');
  });
});

window.addEventListener('click', () => {
  document.querySelectorAll('.dropdown').forEach(dd => dd.classList.remove('show'));
});

// 🔹 Archive Select All
const selectAllArchived = document.getElementById('selectAllArchived');
const archivedCheckboxes = document.querySelectorAll('.archivedCheckbox');
selectAllArchived?.addEventListener('change', () => {
  archivedCheckboxes.forEach(cb => cb.checked = selectAllArchived.checked);
});

archivedCheckboxes.forEach(cb => cb.addEventListener('change', () => {
  selectAllArchived.checked = Array.from(archivedCheckboxes).every(c => c.checked);
}));

// 🔹 Archive Search
document.getElementById('archiveSearch')?.addEventListener('input', function() {
  const filter = this.value.toLowerCase();
  document.querySelectorAll('#archiveTableBody tr').forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
  });
});

// 🔹 Restore Archived - Now shows confirmation modal
document.getElementById('restoreArchivedBtn')?.addEventListener('click', () => {
  const selected = [...document.querySelectorAll('.archivedCheckbox:checked')];
  if(!selected.length) {
    showNotification('⚠️ No Selection', 'Please select at least one record to restore.', 'warning', {
      yesText: 'OK',
      noText: null,
      onYes: () => {
        document.getElementById('notificationModal').style.display = 'none';
      }
    });
    return;
  }

  showNotification('🔄 Restore Records', `Are you sure you want to restore ${selected.length} offense record(s)?`, 'confirm', {
    yesText: 'Yes, Restore',
    noText: 'Cancel',
    onYes: () => {
      // AJAX call to restore records
      setTimeout(() => {
        showNotification('✅ Success', `${selected.length} offense record(s) restored successfully.`, 'success', {
          yesText: 'OK',
          noText: null,
          onYes: () => {
            document.getElementById('notificationModal').style.display = 'none';
            // Optionally refresh the page or update the table
          }
        });
      }, 500);
    },
    onNo: () => {
      document.getElementById('notificationModal').style.display = 'none';
    }
  });
});

// 🔹 Delete Archived - Now shows confirmation modal
document.getElementById('deleteArchivedBtn')?.addEventListener('click', () => {
  const selected = [...document.querySelectorAll('.archivedCheckbox:checked')];
  if(!selected.length) {
    showNotification('⚠️ No Selection', 'Please select at least one record to delete.', 'warning', {
      yesText: 'OK',
      noText: null,
      onYes: () => {
        document.getElementById('notificationModal').style.display = 'none';
      }
    });
    return;
  }

  showNotification('🗑️ Delete Records', `This will permanently delete ${selected.length} offense record(s). This action cannot be undone. Are you sure?`, 'danger', {
    yesText: 'Yes, Delete',
    noText: 'Cancel',
    onYes: () => {
      // AJAX call to delete records
      setTimeout(() => {
        showNotification('✅ Success', `${selected.length} offense record(s) deleted permanently.`, 'success', {
          yesText: 'OK',
          noText: null,
          onYes: () => {
            document.getElementById('notificationModal').style.display = 'none';
            // Optionally refresh the page or update the table
          }
        });
      }, 500);
    },
    onNo: () => {
      document.getElementById('notificationModal').style.display = 'none';
    }
  });
});

// 🔹 Close modals when clicking outside
window.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal')) {
    e.target.style.display = 'none';
  }
});

// ================= NOTIFICATION MODAL FUNCTIONALITY =================

// Notification modal function
function showNotification(title, message, type = 'info', options = {}) {
  const modal = document.getElementById('notificationModal');
  const notificationTitle = document.getElementById('notificationTitle');
  const notificationBody = document.getElementById('notificationBody');
  const notificationIcon = document.getElementById('notificationIcon');
  const yesBtn = document.getElementById('notificationYesBtn');
  const noBtn = document.getElementById('notificationNoBtn');
  const closeBtn = document.getElementById('notificationCloseBtn');

  // Set title and message
  notificationTitle.textContent = title;
  notificationBody.textContent = message;

  // Set icon based on type
  let icon = '🔔';
  if (type === 'success') icon = '✅';
  else if (type === 'warning') icon = '⚠️';
  else if (type === 'danger') icon = '❌';
  else if (type === 'confirm') icon = '❓';
  notificationIcon.textContent = icon;

  // Configure buttons
  yesBtn.textContent = options.yesText || 'Yes';
  yesBtn.onclick = options.onYes || (() => modal.style.display = 'none');

  if (options.noText) {
    noBtn.textContent = options.noText;
    noBtn.style.display = 'inline-block';
    noBtn.onclick = options.onNo || (() => modal.style.display = 'none');
  } else {
    noBtn.style.display = 'none';
  }

  closeBtn.onclick = () => modal.style.display = 'none';

  // Show the modal
  modal.style.display = 'flex';
}

// Close notification modal with close button
document.getElementById('notificationCloseBtn').addEventListener('click', () => {
  document.getElementById('notificationModal').style.display = 'none';
});

// ================= BEAUTIFUL PRINT & EXPORT =================

// 🖨️ Print Table as PDF (Automatically Download)
document.getElementById('printBtn')?.addEventListener('click', () => {
  const table = document.querySelector('.table-container table');
  if (!table) return;

  const currentDate = new Date().toLocaleDateString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric'
  });

  const currentTime = new Date().toLocaleTimeString('en-PH', {
    hour: '2-digit', minute: '2-digit'
  });

  // Create a temporary element for PDF generation
  const element = document.createElement('div');
  element.innerHTML = `
    <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #2d3748; background: #ffffff; padding: 25px;">
      <!-- Professional Header -->
      <div style="display: flex; align-items: center; border-bottom: 3px solid #1e3a8a; padding-bottom: 20px; margin-bottom: 25px;">
        <div style="flex: 1;">
          <h1 style="margin: 0; color: #1e3a8a; font-size: 24px; font-weight: 700;">TAGOLOAN SENIOR HIGH SCHOOL</h1>
          <h2 style="margin: 5px 0 0 0; color: #4a5568; font-size: 16px; font-weight: 500;">Student Violation Tracking System</h2>
          <p style="margin: 8px 0 0 0; color: #718096; font-size: 14px;">Official Offense and Sanctions Report</p>
        </div>
        <div style="text-align: right;">
          <div style="background: #1e3a8a; color: white; padding: 8px 15px; border-radius: 6px; display: inline-block;">
            <div style="font-size: 12px; font-weight: 600;">REPORT DATE</div>
            <div style="font-size: 14px; font-weight: 500;">${currentDate}</div>
            <div style="font-size: 12px; font-weight: 400;">${currentTime}</div>
          </div>
        </div>
      </div>

      <!-- Report Summary -->
      <div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px 20px; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h3 style="margin: 0; color: #2d3748; font-size: 18px; font-weight: 600;">Offense and Sanctions Report</h3>
            <p style="margin: 5px 0 0 0; color: #718096; font-size: 14px;">
              Total Records: <strong style="color: #2d3748;">${document.querySelectorAll('#tableBody tr').length}</strong>
            </p>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 12px; color: #718096;">Document ID</div>
            <div style="font-size: 14px; font-weight: 600; color: #2d3748;">OSR-${Date.now().toString().slice(-6)}</div>
          </div>
        </div>
      </div>

      <!-- Enhanced Table -->
      <div style="overflow: hidden; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        ${table.outerHTML.replace('<table', '<table style="width: 100%; border-collapse: collapse; font-size: 12px;"')}
      </div>

      <!-- Footer Section -->
      <div style="margin-top: 40px; border-top: 2px solid #e2e8f0; padding-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div style="flex: 1;">
            <div style="font-size: 12px; color: #718096; margin-bottom: 5px;">Prepared By:</div>
            <div style="border-bottom: 1px solid #cbd5e0; width: 200px; padding: 25px 0 5px 0;"></div>
            <div style="font-size: 12px; color: #4a5568; margin-top: 5px;">Prefect of Discipline</div>
          </div>
          <div style="flex: 1; text-align: center;">
            <div style="font-size: 12px; color: #718096; margin-bottom: 5px;">Reviewed By:</div>
            <div style="border-bottom: 1px solid #cbd5e0; width: 200px; padding: 25px 0 5px 0; margin: 0 auto;"></div>
            <div style="font-size: 12px; color: #4a5568; margin-top: 5px;">School Principal</div>
          </div>
          <div style="flex: 1; text-align: right;">
            <div style="font-size: 12px; color: #718096; margin-bottom: 5px;">Approved By:</div>
            <div style="border-bottom: 1px solid #cbd5e0; width: 200px; padding: 25px 0 5px 0; margin-left: auto;"></div>
            <div style="font-size: 12px; color: #4a5568; margin-top: 5px;">School Administrator</div>
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
    table.style.fontSize = '12px';
    
    // Style table headers
    const headers = table.getElementsByTagName('th');
    for (let header of headers) {
      header.style.backgroundColor = '#1e3a8a';
      header.style.color = 'white';
      header.style.padding = '12px 10px';
      header.style.textAlign = 'left';
      header.style.fontWeight = '600';
      header.style.border = '1px solid #2d3748';
      header.style.fontSize = '11px';
      header.style.textTransform = 'uppercase';
      header.style.letterSpacing = '0.5px';
    }
    
    // Style table cells
    const cells = table.getElementsByTagName('td');
    for (let cell of cells) {
      cell.style.padding = '10px 8px';
      cell.style.border = '1px solid #e2e8f0';
      cell.style.fontSize = '11px';
      cell.style.color = '#4a5568';
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
    filename: `Offense_Sanctions_Report_${new Date().toISOString().slice(0,10)}.pdf`,
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

  // Show loading notification
  showNotification('⏳ Generating PDF', 'Please wait while we prepare your professional report...', 'info', {
    yesText: 'OK',
    noText: null,
    onYes: () => {
      document.getElementById('notificationModal').style.display = 'none';
    }
  });

  // Generate and download PDF
  html2pdf().set(options).from(element).save().then(() => {
    // Success feedback
    showNotification('✅ Professional PDF Generated', 'Your offense and sanctions report has been downloaded as a professional PDF document.', 'success', {
      yesText: 'OK',
      noText: null,
      onYes: () => {
        document.getElementById('notificationModal').style.display = 'none';
      }
    });
  }).catch(error => {
    console.error('PDF generation error:', error);
    showNotification('❌ PDF Generation Failed', 'There was an error generating the PDF. Please try again.', 'danger', {
      yesText: 'OK',
      noText: null,
      onYes: () => {
        document.getElementById('notificationModal').style.display = 'none';
      }
    });
  });
});

// 📤 Export Table to Excel
document.getElementById('exportBtn')?.addEventListener('click', () => {
  const table = document.querySelector('.table-container table');
  if (!table) return;

  const currentDate = new Date().toLocaleDateString('en-PH', {
    year: 'numeric', month: 'long', day: 'numeric'
  });

  const header = `
    <table style="width:100%; border-collapse:collapse; text-align:center; margin-bottom:20px;">
      <tr>
        <td colspan="4">
          <h2 style="margin:0; color:#1e3a8a;">Tagoloan Senior High School</h2>
          <h4 style="margin:0; color:#666;">Student Violation Tracking System</h4>
          <p style="margin:5px 0;">📅 Date Generated: ${currentDate}</p>
        </td>
      </tr>
    </table>
  `;

  const footer = `
    <table style="margin-top:40px;">
      <tr>
        <td style="border-top:1px solid #444; width:200px; padding-top:10px;">Authorized Signature</td>
      </tr>
    </table>
  `;

  const tableHTML = header + table.outerHTML + footer;
  const filename = `Offense_and_Sanctions_${new Date().toISOString().slice(0,10)}.xls`;

  const downloadLink = document.createElement('a');
  document.body.appendChild(downloadLink);
  downloadLink.href = 'data:application/vnd.ms-excel,' + encodeURIComponent(tableHTML);
  downloadLink.download = filename;
  downloadLink.click();
  document.body.removeChild(downloadLink);

  // Success feedback
  showNotification('✅ Exported', 'Table exported beautifully to Excel.', 'success', {
    yesText: 'OK',
    noText: null,
    onYes: () => {
      document.getElementById('notificationModal').style.display = 'none';
    }
  });
});

</script>

@endsection