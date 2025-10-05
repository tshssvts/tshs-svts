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

    <!-- Toolbar -->
    <div class="toolbar">
        <h2>Create Complaint Anecdotal</h2>
        <div class="actions">
            <input type="search" placeholder="🔍 Search anecdotal..." id="searchInput">

            <div class="buttons-row">
                <button type="button" class="btn-Add-Anecdotal" id="btnAddAnecdotal">
                    <i class="fas fa-plus-circle"></i> Add Another Anecdotal
                </button>
                <button type="submit" class="btn-save" form="anecdotalForm">
                    <i class="fas fa-save"></i> Save All
                </button>
            </div>
        </div>
    </div>

    <!-- Anecdotal Container -->
    <form id="anecdotalForm" method="POST" action="{{ route('complaint-anecdotal.store') }}">
        @csrf
        <div class="students-wrapper" id="anecdotalWrapper">
            <!-- Anecdotal forms will be dynamically added here -->
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let anecdotalCount = 0;
    const complaintSearchUrl = "{{ route('complaint-anecdotal.search-complaints') }}";

    // Initialize with one anecdotal form
    document.addEventListener('DOMContentLoaded', function() {
        addAnecdotalForm();
    });

    // Add new anecdotal form
    document.getElementById('btnAddAnecdotal').addEventListener('click', function() {
        addAnecdotalForm();
        updateLayout();
    });

    function addAnecdotalForm() {
        anecdotalCount++;

        const anecdotalWrapper = document.getElementById('anecdotalWrapper');
        const newAnecdotal = document.createElement('div');
        newAnecdotal.className = 'student-container';
        newAnecdotal.innerHTML = `
            <div class="student-header">
                <span class="student-title">Complaint Anecdotal #${anecdotalCount}</span>
                <button type="button" class="remove-student" onclick="removeAnecdotal(this)">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="complaint_search_${anecdotalCount}">Complaint Record * <span class="note">(comma-separated for multiple)</span></label>
                    <input type="text" id="complaint_search_${anecdotalCount}" class="form-control complaint-search-input" placeholder="e.g. Shawn Laurence, Kent Zyrone, Jayvee Charles" data-ids="" autocomplete="off">
                    <input type="hidden" id="complaints_id_${anecdotalCount}" name="anecdotal[${anecdotalCount-1}][complaints_id]" class="complaint-id-input" required>
                    <div class="search-results complaint-results" id="complaint_results_${anecdotalCount}"></div>
                </div>

                <div class="form-group full-width">
                    <label for="comp_anec_solution_${anecdotalCount}">Solution Implemented *</label>
                    <textarea id="comp_anec_solution_${anecdotalCount}" name="anecdotal[${anecdotalCount-1}][comp_anec_solution]" class="form-control" rows="4" placeholder="Describe the solution or intervention implemented..." required></textarea>
                </div>

                <div class="form-group full-width">
                    <label for="comp_anec_recommendation_${anecdotalCount}">Recommendations *</label>
                    <textarea id="comp_anec_recommendation_${anecdotalCount}" name="anecdotal[${anecdotalCount-1}][comp_anec_recommendation]" class="form-control" rows="4" placeholder="Provide recommendations for future prevention..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="comp_anec_date_${anecdotalCount}">Anecdotal Date *</label>
                    <input type="date" id="comp_anec_date_${anecdotalCount}" name="anecdotal[${anecdotalCount-1}][comp_anec_date]" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label for="comp_anec_time_${anecdotalCount}">Anecdotal Time *</label>
                    <input type="time" id="comp_anec_time_${anecdotalCount}" name="anecdotal[${anecdotalCount-1}][comp_anec_time]" class="form-control" value="{{ date('H:i') }}" required>
                </div>

                <div class="form-group">
                    <label for="status_${anecdotalCount}">Status</label>
                    <select id="status_${anecdotalCount}" name="anecdotal[${anecdotalCount-1}][status]" class="form-control">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="resolved">Resolved</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>
        `;

        anecdotalWrapper.appendChild(newAnecdotal);

        // Attach search functionality to the new form
        attachSearchListeners(newAnecdotal, anecdotalCount);
    }

    // Attach search functionality to complaint field
    function attachSearchListeners(container, anecdotalIndex) {
        const complaintSearch = container.querySelector('.complaint-search-input');
        const complaintIdInput = container.querySelector('.complaint-id-input');
        const complaintResults = container.querySelector('.complaint-results');

        // Complaint search functionality - Multiple selection
        complaintSearch.addEventListener('input', function() {
            let parts = this.value.split(",");
            let query = parts[parts.length - 1].trim();

            console.log('Complaint search query:', query);

            if (query.length < 2) {
                complaintResults.innerHTML = '';
                return;
            }

            complaintResults.innerHTML = '<div class="no-results">Searching...</div>';

            $.ajax({
                url: complaintSearchUrl,
                method: 'POST',
                data: {
                    query: query,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    console.log('Complaint search SUCCESS:', data);
                    complaintResults.innerHTML = '';

                    if (!data || data.length === 0) {
                        complaintResults.innerHTML = '<div class="no-results">No complaint records found.</div>';
                        return;
                    }

                    const selectedIds = (complaintSearch.dataset.ids || "").split(",").filter(id => id.trim() !== "");

                    data.forEach(complaint => {
                        if (selectedIds.includes(complaint.complaints_id.toString())) return;

                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.textContent = `${complaint.student_name} - ${complaint.complaint_type} (${complaint.complaint_date})`;
                        item.dataset.id = complaint.complaints_id;
                        item.dataset.studentName = complaint.student_name;

                        item.addEventListener('click', function() {
                            const currentIds = (complaintSearch.dataset.ids || "").split(",").filter(id => id.trim() !== "");
                            const currentNames = complaintSearch.value.split(",").map(n => n.trim()).filter(n => n !== "");

                            if (!currentIds.includes(complaint.complaints_id.toString())) {
                                const lastIndex = currentNames.length - 1;
                                if (currentNames[lastIndex].toLowerCase().includes(query.toLowerCase())) {
                                    currentNames[lastIndex] = complaint.student_name;
                                } else {
                                    currentNames.push(complaint.student_name);
                                }

                                complaintSearch.value = currentNames.join(", ");
                                currentIds.push(complaint.complaints_id.toString());
                                complaintSearch.dataset.ids = currentIds.join(",");

                                if (currentIds.length === 1) {
                                    complaintIdInput.value = complaint.complaints_id;
                                }
                            }

                            complaintResults.innerHTML = '';
                            complaintIdInput.style.borderColor = '#ddd';
                            complaintSearch.style.borderColor = '#ddd';
                        });

                        complaintResults.appendChild(item);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('❌ Complaint search ERROR:', error);
                    complaintResults.innerHTML = '<div class="no-results">Search failed. Try again.</div>';
                }
            });
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!complaintSearch.contains(e.target) && !complaintResults.contains(e.target)) {
                complaintResults.innerHTML = '';
            }
        });
    }

    // Remove anecdotal form
    function removeAnecdotal(button) {
        const containers = document.querySelectorAll('.student-container');
        if (containers.length > 1) {
            button.closest('.student-container').remove();
            updateAnecdotalNumbers();
            updateLayout();
        } else {
            alert('You need at least one anecdotal form.');
        }
    }

    function updateAnecdotalNumbers() {
        const containers = document.querySelectorAll('.student-container');
        containers.forEach((container, index) => {
            const title = container.querySelector('.student-title');
            title.textContent = `Complaint Anecdotal #${index + 1}`;

            const inputs = container.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                const id = input.getAttribute('id');
                if (id) input.setAttribute('id', id.replace(/\d+$/, index + 1));
            });

            const labels = container.querySelectorAll('label');
            labels.forEach(label => {
                const forAttr = label.getAttribute('for');
                if (forAttr) label.setAttribute('for', forAttr.replace(/\d+$/, index + 1));
            });
        });
        anecdotalCount = containers.length;
    }

    function updateLayout() {
        const containers = document.querySelectorAll('.student-container');
        const wrapper = document.getElementById('anecdotalWrapper');

        containers.forEach(container => {
            container.style.flex = '1 1 400px';
            container.style.maxWidth = '600px';
        });

        wrapper.style.justifyContent = containers.length === 1 ? 'center' : 'flex-start';
    }

    // Validation
    document.getElementById('anecdotalForm').addEventListener('submit', function(e) {
        const containers = document.querySelectorAll('.student-container');
        let isValid = true;

        containers.forEach((container, index) => {
            const complaintId = container.querySelector(`input[name="anecdotal[${index}][complaints_id]"]`);
            const searchInput = container.querySelector('.complaint-search-input');
            const solution = container.querySelector(`textarea[name="anecdotal[${index}][comp_anec_solution]"]`);
            const recommendation = container.querySelector(`textarea[name="anecdotal[${index}][comp_anec_recommendation]"]`);
            const date = container.querySelector(`input[name="anecdotal[${index}][comp_anec_date]"]`);
            const time = container.querySelector(`input[name="anecdotal[${index}][comp_anec_time]"]`);

            if (!complaintId.value || !solution.value || !recommendation.value || !date.value || !time.value) {
                isValid = false;
                if (!complaintId.value) {
                    complaintId.style.borderColor = '#e74c3c';
                    searchInput.style.borderColor = '#e74c3c';
                }
                if (!solution.value) solution.style.borderColor = '#e74c3c';
                if (!recommendation.value) recommendation.style.borderColor = '#e74c3c';
                if (!date.value) date.style.borderColor = '#e74c3c';
                if (!time.value) time.style.borderColor = '#e74c3c';
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields (marked with *) before submitting.');
        }
    });

    // Remove error border when typing
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('form-control')) {
            e.target.style.borderColor = '#ddd';
            if (e.target.classList.contains('complaint-search-input')) {
                e.target.closest('.form-group').querySelector('.complaint-id-input').style.borderColor = '#ddd';
            }
        }
    });

    // Search filtering
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const containers = document.querySelectorAll('.student-container');

        containers.forEach(container => {
            const searchVal = container.querySelector('.complaint-search-input').value.toLowerCase();
            const sol = container.querySelector('textarea[name*="[comp_anec_solution]"]').value.toLowerCase();
            const rec = container.querySelector('textarea[name*="[comp_anec_recommendation]"]').value.toLowerCase();

            container.style.display = (searchVal.includes(term) || sol.includes(term) || rec.includes(term)) ? 'block' : 'none';
        });
    });
</script>

@endsection
