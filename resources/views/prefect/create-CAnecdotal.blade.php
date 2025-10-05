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
    const respondentSearchUrl = "{{ route('complaint-anecdotal.search-respondents') }}";
    const complaintSearchUrl = "{{ route('complaint-anecdotal.search-complaints') }}";

    let anecdotalCount = 0;

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
                    <label for="complaint_search_${anecdotalCount}">Complaint Record * <span class="note">(search student or offense)</span></label>
                    <input type="text" id="complaint_search_${anecdotalCount}" class="form-control complaint-search-input" placeholder="e.g. Shawn Laurence" data-ids="" autocomplete="off">
                    <input type="hidden" id="complaint_id_${anecdotalCount}" name="anecdotal[${anecdotalCount-1}][complaint_id]" class="complaint-id-input" required>
                    <div class="search-results complaint-results" id="complaint_results_${anecdotalCount}"></div>
                </div>

                <div class="form-group">
                    <label for="respondent_search_${anecdotalCount}">Respondent * <span class="note">(comma-separated for multiple)</span></label>
                    <input type="text" id="respondent_search_${anecdotalCount}" class="form-control respondent-search-input" placeholder="e.g. John Doe" data-ids="" autocomplete="off">
                    <input type="hidden" id="respondents_id_${anecdotalCount}" name="anecdotal[${anecdotalCount-1}][respondents_id]" class="respondent-id-input" required>
                    <div class="search-results respondent-results" id="respondent_results_${anecdotalCount}"></div>
                </div>

                <div class="form-group full-width">
                    <label for="comp_anec_solution_${anecdotalCount}">Solution Implemented *</label>
                    <textarea id="comp_anec_solution_${anecdotalCount}" name="anecdotal[${anecdotalCount-1}][comp_anec_solution]" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-group full-width">
                    <label for="comp_anec_recommendation_${anecdotalCount}">Recommendations *</label>
                    <textarea id="comp_anec_recommendation_${anecdotalCount}" name="anecdotal[${anecdotalCount-1}][comp_anec_recommendation]" class="form-control" rows="4" required></textarea>
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

        attachComplaintSearchListeners(newAnecdotal);
        attachRespondentListeners(newAnecdotal);
    }

    // ✅ Complaint Search Logic (Based on working violation logic)
    function attachComplaintSearchListeners(container) {
        const searchInput = container.querySelector('.complaint-search-input');
        const hiddenInput = container.querySelector('.complaint-id-input');
        const resultsBox = container.querySelector('.complaint-results');

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            if (query.length < 2) {
                resultsBox.innerHTML = '';
                return;
            }

            resultsBox.innerHTML = '<div class="no-results">Searching...</div>';

            fetch(`${complaintSearchUrl}?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Complaint search data:', data);
                    resultsBox.innerHTML = '';

                    if (!data || data.length === 0) {
                        resultsBox.innerHTML = '<div class="no-results">No records found.</div>';
                        return;
                    }

                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'search-result-item';
                        div.textContent = `${item.student_name} - ${item.offense_type} (${item.complaint_date ?? 'No date'})`;
                        div.addEventListener('click', () => {
                            searchInput.value = item.student_name;
                            hiddenInput.value = item.complaint_id;
                            resultsBox.innerHTML = '';
                            searchInput.style.borderColor = '#ddd';
                        });
                        resultsBox.appendChild(div);
                    });
                })
                .catch(err => {
                    console.error('Search error:', err);
                    resultsBox.innerHTML = '<div class="no-results">Search failed. Try again.</div>';
                });
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.innerHTML = '';
            }
        });
    }

    // ✅ Respondent Search Logic (Unchanged)
    function attachRespondentListeners(container) {
        const respondentSearch = container.querySelector('.respondent-search-input');
        const respondentIdInput = container.querySelector('.respondent-id-input');
        const respondentResults = container.querySelector('.respondent-results');

        respondentSearch.addEventListener('input', function() {
            let query = this.value.trim();

            if (query.length < 2) {
                respondentResults.innerHTML = '';
                return;
            }

            respondentResults.innerHTML = '<div class="no-results">Searching...</div>';

            $.ajax({
                url: respondentSearchUrl,
                method: 'POST',
                data: { query, _token: '{{ csrf_token() }}' },
                success: function(data) {
                    respondentResults.innerHTML = '';
                    if (!data || data.length === 0) {
                        respondentResults.innerHTML = '<div class="no-results">No respondents found.</div>';
                        return;
                    }

                    data.forEach(res => {
                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.textContent = `${res.student_fname} ${res.student_lname}`;
                        item.addEventListener('click', () => {
                            respondentSearch.value = `${res.student_fname} ${res.student_lname}`;
                            respondentIdInput.value = res.student_id;
                            respondentResults.innerHTML = '';
                            respondentSearch.style.borderColor = '#ddd';
                        });
                        respondentResults.appendChild(item);
                    });
                },
                error: function() {
                    respondentResults.innerHTML = '<div class="no-results">Search failed. Try again.</div>';
                }
            });
        });

        document.addEventListener('click', function(e) {
            if (!respondentSearch.contains(e.target) && !respondentResults.contains(e.target)) {
                respondentResults.innerHTML = '';
            }
        });
    }

    // ✅ Remove anecdotal
    function removeAnecdotal(btn) {
        const all = document.querySelectorAll('.student-container');
        if (all.length > 1) {
            btn.closest('.student-container').remove();
            updateAnecdotalNumbers();
            updateLayout();
        } else {
            alert('You need at least one anecdotal form.');
        }
    }

    function updateAnecdotalNumbers() {
        const containers = document.querySelectorAll('.student-container');
        containers.forEach((c, i) => {
            c.querySelector('.student-title').textContent = `Complaint Anecdotal #${i + 1}`;
        });
        anecdotalCount = containers.length;
    }

    function updateLayout() {
        const wrapper = document.getElementById('anecdotalWrapper');
        const containers = document.querySelectorAll('.student-container');
        wrapper.style.justifyContent = containers.length === 1 ? 'center' : 'flex-start';
    }
</script>

@endsection
