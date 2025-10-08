<?php

namespace App\Http\Controllers\Prefect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\ComplaintsAnecdotal;
use App\Models\Complaints;
use App\Models\Student;

class PComplaintAnecdotalController extends Controller
{
 public function index()
    {
        // Get complaint anecdotal records with related data
        $canecdotals = ComplaintsAnecdotal::with(['complaint.complainant', 'complaint.respondent'])
            ->whereIn('status', ['active', 'in_progress'])
            ->orderBy('comp_anec_date', 'desc')
            ->orderBy('comp_anec_time', 'desc')
            ->paginate(10);

        // Calculate summary counts
        $now = Carbon::now();

        $monthlyAnecdotals = ComplaintsAnecdotal::whereIn('status', ['active', 'in_progress'])
            ->whereYear('comp_anec_date', $now->year)
            ->whereMonth('comp_anec_date', $now->month)
            ->count();

        $weeklyAnecdotals = ComplaintsAnecdotal::whereIn('status', ['active', 'in_progress'])
            ->whereBetween('comp_anec_date', [
                $now->copy()->startOfWeek()->format('Y-m-d'),
                $now->copy()->endOfWeek()->format('Y-m-d')
            ])
            ->count();

        $dailyAnecdotals = ComplaintsAnecdotal::whereIn('status', ['active', 'in_progress'])
            ->whereDate('comp_anec_date', $now->format('Y-m-d'))
            ->count();

        return view('prefect.complaintAnecdotal', compact(
            'canecdotals',
            'monthlyAnecdotals',
            'weeklyAnecdotals',
            'dailyAnecdotals'
        ));
    }

    public function create()
    {
        // Get active complaints to associate with anecdotal records
        $complaints = Complaints::with(['complainant', 'respondent'])
            ->where('status', 'active')
            ->orderBy('complaints_date', 'desc')
            ->get();

        return view('prefect.complaintsAnecdotal_create', compact('complaints'));
    }

    /**
     * Display the form for creating complaint anecdotal records.
     */
    public function createCAnecdotal()
    {
        return view('prefect.create-CAnecdotal');
    }

    /**
     * Store complaint anecdotal records in batch.
     */
    public function store(Request $request)
    {
        Log::info('=== COMPLAINT ANECDOTAL STORE STARTED ===');
        Log::info('Full Request Data:', $request->all());

        $validated = $request->validate([
            'anecdotal' => 'required|array|min:1',
            'anecdotal.*.complaint_id' => 'required|integer|exists:tbl_complaints,complaint_id',
            'anecdotal.*.comp_anec_solution' => 'required|string|max:1000',
            'anecdotal.*.comp_anec_recommendation' => 'required|string|max:1000',
            'anecdotal.*.comp_anec_date' => 'required|date',
            'anecdotal.*.comp_anec_time' => 'required',
            'anecdotal.*.status' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['anecdotal'] as $index => $data) {
                $record = ComplaintsAnecdotal::create([
                    'complaint_id' => $data['complaint_id'],
                    'comp_anec_solution' => $data['comp_anec_solution'],
                    'comp_anec_recommendation' => $data['comp_anec_recommendation'],
                    'comp_anec_date' => $data['comp_anec_date'],
                    'comp_anec_time' => $data['comp_anec_time'],
                    'status' => $data['status'] ?? 'active',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                Log::info("✅ Anecdotal #{$index} created:", $record->toArray());
            }

            DB::commit();
            Log::info('=== COMPLAINT ANECDOTAL STORE COMPLETED SUCCESSFULLY ===');

            return redirect()->back()->with('success', '✅ Complaint Anecdotal Records saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ COMPLAINT ANECDOTAL STORE FAILED:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', '❌ Failed to save Complaint Anecdotal Records. Please try again.');
        }
    }

    /**
     * Search complaints by student or offense name.
     */
    public function searchComplaints(Request $request)
    {
        try {
            Log::info('=== SEARCH COMPLAINTS START ===');

            $query = $request->input('query');
            Log::info('Query received:', ['query' => $query]);

            if (empty($query)) {
                return response()->json([]);
            }

            $complaints = Complaints::with(['student', 'offense'])
                ->where(function($q) use ($query) {
                    $q->whereHas('student', function($studentQuery) use ($query) {
                        $studentQuery->where('student_fname', 'LIKE', "%{$query}%")
                                     ->orWhere('student_lname', 'LIKE', "%{$query}%")
                                     ->orWhereRaw("CONCAT(student_fname, ' ', student_lname) LIKE ?", ["%{$query}%"]);
                    })
                    ->orWhereHas('offense', function($offenseQuery) use ($query) {
                        $offenseQuery->where('offense_type', 'LIKE', "%{$query}%");
                    });
                })
                ->where('status', 'active')
                ->limit(10)
                ->get();

            Log::info('Complaints found:', ['count' => $complaints->count()]);

            $uniqueStudents = [];
            $results = [];

            foreach ($complaints as $complaint) {
                if (!$complaint->student) continue;

                $student = $complaint->student;
                $studentId = $student->student_id;
                $studentName = "{$student->student_fname} {$student->student_lname}";

                if (in_array($studentId, $uniqueStudents)) continue;
                $uniqueStudents[] = $studentId;

                $offenseType = $complaint->offense
                    ? $complaint->offense->offense_type
                    : 'Unknown Offense';

                $results[] = [
                    'complaint_id' => $complaint->complaint_id,
                    'student_id' => $studentId,
                    'student_name' => $studentName,
                    'offense_type' => $offenseType,
                    'complaint_date' => $complaint->complaint_date ?? null,
                    'complaint_incident' => $complaint->complaint_incident ?? null
                ];
            }

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error('Error in searchComplaints: ' . $e->getMessage());
            return response()->json(['error' => 'Search failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Search respondents (students)
     */
    public function searchRespondents(Request $request)
    {
        try {
            Log::info('=== SEARCH RESPONDENTS START ===');

            $query = $request->input('query');
            Log::info('Query:', ['query' => $query]);

            if (empty($query)) {
                return response()->json([]);
            }

            $respondents = Student::where('student_fname', 'like', "%{$query}%")
                ->orWhere('student_lname', 'like', "%{$query}%")
                ->orWhereRaw("CONCAT(student_fname, ' ', student_lname) LIKE ?", ["%{$query}%"])
                ->limit(10)
                ->get(['student_id', 'student_fname', 'student_lname']);

            Log::info('Respondents found:', ['count' => $respondents->count()]);

            return response()->json($respondents);

        } catch (\Exception $e) {
            Log::error('Error in searchRespondents: ' . $e->getMessage());
            return response()->json(['error' => 'Search failed. Try again.'], 500);
        }
    }
}
