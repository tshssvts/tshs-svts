<?php

namespace App\Http\Controllers\Prefect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\OffensesWithSanction;
use App\Models\ViolationRecord;
use App\Models\ViolationAnecdotal; // Make sure to import this

class PViolationAnecdotalController extends Controller
{

     public function index()
    {
        // Get anecdotal records with related data
        $vanecdotals = ViolationAnecdotal::with(['violation.student'])
            ->whereIn('status', ['active', 'in_progress'])
            ->orderBy('violation_anec_date', 'desc')
            ->orderBy('violation_anec_time', 'desc')
            ->paginate(10);

        // Calculate summary counts
        $now = Carbon::now();

        $monthlyAnecdotals = ViolationAnecdotal::whereIn('status', ['active', 'in_progress'])
            ->whereYear('violation_anec_date', $now->year)
            ->whereMonth('violation_anec_date', $now->month)
            ->count();

        $weeklyAnecdotals = ViolationAnecdotal::whereIn('status', ['active', 'in_progress'])
            ->whereBetween('violation_anec_date', [
                $now->startOfWeek()->format('Y-m-d'),
                $now->endOfWeek()->format('Y-m-d')
            ])
            ->count();

        $dailyAnecdotals = ViolationAnecdotal::whereIn('status', ['active', 'in_progress'])
            ->whereDate('violation_anec_date', $now->format('Y-m-d'))
            ->count();

        return view('prefect.violationAnecdotal', compact(
            'vanecdotals',
            'monthlyAnecdotals',
            'weeklyAnecdotals',
            'dailyAnecdotals'
        ));
    }
    public function createVAnecdotal()
    {
        return view('prefect.create-VAnecdotal');
    }
public function searchViolations(Request $request)
{
    try {
        Log::info('=== SEARCH VIOLATIONS START ===');

        $query = $request->input('query');
        Log::info('Query received:', ['query' => $query]);

        if (empty($query)) {
            return response()->json([]);
        }

        $violations = ViolationRecord::with(['student', 'offense'])
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

        Log::info('Violations found:', ['count' => $violations->count()]);

        // Use a Set to track unique student names
        $uniqueStudents = [];
        $results = [];

        foreach ($violations as $violation) {
            if (!$violation->student) continue;

            $studentName = $violation->student->student_fname . ' ' . $violation->student->student_lname;
            $studentId = $violation->student->student_id;

            // Skip if we've already seen this student
            if (in_array($studentId, $uniqueStudents)) {
                continue;
            }

            $uniqueStudents[] = $studentId;

            $offenseType = $violation->offense ?
                $violation->offense->offense_type :
                'Unknown Offense';

            $results[] = [
                'violation_id' => $violation->violation_id,
                'student_id' => $studentId,
                'student_name' => $studentName,
                'offense_type' => $offenseType,
                'violation_date' => $violation->violation_date,
                'violation_incident' => $violation->violation_incident
            ];
        }

        Log::info('Final results to return:', ['results_count' => count($results)]);

        return response()->json($results);

    } catch (\Exception $e) {
        Log::error('Error in searchViolations: ' . $e->getMessage());
        Log::error('Error trace: ' . $e->getTraceAsString());
        return response()->json(['error' => 'Search failed: ' . $e->getMessage()], 500);
    }
}
}
