<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // Assuming Prefect is authenticated
use App\Models\OffensesWithSanction;
use App\Models\ViolationRecord;
use App\Models\ViolationAppointment;
use App\Models\ViolationAnecdotal;


class AViolationController extends Controller
{


public function violationrecord()
{
    $adviserId = Auth::guard('adviser')->id();

    // Get student IDs under this adviser
    $studentIds = Student::where('adviser_id', $adviserId)->pluck('student_id');

    $vappointments = ViolationAppointment::with(['violation.student'])
        ->whereHas('violation.student', function($query) use ($adviserId) {
            $query->where('adviser_id', $adviserId);
        })
        ->get();

    $vanecdotals = ViolationAnecdotal::with(['violation.student'])
        ->whereHas('violation.student', function($query) use ($adviserId) {
            $query->where('adviser_id', $adviserId);
        })
        ->get();

    // Get the actual dates from your violation records for students under this adviser
    $mostRecentViolationDate = DB::table('tbl_violation_record')
        ->whereIn('violator_id', $studentIds) // Changed to violator_id
        ->max('violation_date');

    $earliestViolationDate = DB::table('tbl_violation_record')
        ->whereIn('violator_id', $studentIds) // Changed to violator_id
        ->min('violation_date');

    // Use the most recent violation date for calculations, or today if no records exist
    $referenceDate = $mostRecentViolationDate ? Carbon::parse($mostRecentViolationDate) : Carbon::today();

    // Calculate date ranges based on the actual violation dates
    $today = $referenceDate->copy();
    $startOfWeek = $referenceDate->copy()->startOfWeek();
    $endOfWeek = $referenceDate->copy()->endOfWeek();
    $startOfMonth = $referenceDate->copy()->startOfMonth();
    $endOfMonth = $referenceDate->copy()->endOfMonth();

    // ✅ Summary Counts - filtered by adviser's students
    $dailyViolations = DB::table('tbl_violation_record')
        ->whereIn('violator_id', $studentIds) // Changed to violator_id
        ->whereDate('violation_date', $today)
        ->count();

    $weeklyViolations = DB::table('tbl_violation_record')
        ->whereIn('violator_id', $studentIds) // Changed to violator_id
        ->whereBetween('violation_date', [$startOfWeek, $endOfWeek])
        ->count();

    $monthlyViolations = DB::table('tbl_violation_record')
        ->whereIn('violator_id', $studentIds) // Changed to violator_id
        ->whereBetween('violation_date', [$startOfMonth, $endOfMonth])
        ->count();

    // ✅ Fetch Main Violation Records - only for adviser's students
    $violations = ViolationRecord::with(['student', 'offense'])
        ->whereIn('violator_id', $studentIds) // Changed to violator_id
        ->orderBy('violation_date', 'desc')
        ->paginate(30);

    // ✅ Fetch Violation Appointments - only for adviser's students
    $appointments = DB::table('tbl_violation_appointment')
        ->join('tbl_violation_record', 'tbl_violation_appointment.violation_id', '=', 'tbl_violation_record.violation_id')
        ->whereIn('tbl_violation_record.violator_id', $studentIds) // Changed to violator_id
        ->select(
            'tbl_violation_appointment.*',
            'tbl_violation_record.violation_incident'
        )
        ->orderBy('tbl_violation_appointment.violation_app_date', 'desc')
        ->paginate(30);

    // ✅ Fetch Violation Anecdotals - only for adviser's students
    $anecdotals = DB::table('tbl_violation_anecdotal')
        ->join('tbl_violation_record', 'tbl_violation_anecdotal.violation_id', '=', 'tbl_violation_record.violation_id')
        ->whereIn('tbl_violation_record.violator_id', $studentIds) // Changed to violator_id
        ->select(
            'tbl_violation_anecdotal.*',
            'tbl_violation_record.violation_incident'
        )
        ->orderBy('tbl_violation_anecdotal.violation_anec_date', 'desc')
        ->paginate(30);

    // ✅ Fetch Offenses (if needed for dropdowns)
    $offenses = OffensesWithSanction::all();

    return view('adviser.violationrecord', compact(
        'violations',
        'appointments',
        'anecdotals',
        'vanecdotals',
        'vappointments',
        'offenses',
        'mostRecentViolationDate',
        'earliestViolationDate',
        'referenceDate',
        'today',
        'startOfWeek',
        'endOfWeek',
        'startOfMonth',
        'endOfMonth',
        'dailyViolations',
        'weeklyViolations',
        'monthlyViolations'
    ));
}


public function store(Request $request)
{
    Log::info('Store method called with data:', $request->all());

    try {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'violations' => 'required|array',
            'violations.*.violator_id' => 'required|exists:tbl_student,student_id',
            'violations.*.offense_sanc_id' => 'required|exists:tbl_offenses_with_sanction,offense_sanc_id',
            'violations.*.violation_incident' => 'required|string|max:255',
            'violations.*.violation_date' => 'required|date',
            'violations.*.violation_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', '❌ Please correct the validation errors.');
        }

        $createdCount = 0;
        $prefectId = auth()->user()->prefect_id ?? 1; // Adjust based on your auth structure

        Log::info("Using prefect_id: " . $prefectId);
        Log::info("Number of violations to create: " . count($request->violations));

        foreach ($request->violations as $index => $v) {
            Log::info("Creating violation {$index}:", $v);

            ViolationRecord::create([
                'violator_id' => $v['violator_id'],
                'prefect_id' => $prefectId,
                'offense_sanc_id' => $v['offense_sanc_id'],
                'violation_incident' => $v['violation_incident'],
                'violation_date' => $v['violation_date'],
                'violation_time' => $v['violation_time'],
                'status' => 'active'
            ]);

            $createdCount++;
        }

        Log::info("Successfully created {$createdCount} violations");

        return redirect()->route('violation.record')
            ->with('success', "✅ {$createdCount} violation(s) stored successfully!");

    } catch (\Exception $e) {
        Log::error('Error saving violations: ' . $e->getMessage());
        Log::error($e->getTraceAsString());

        return redirect()->back()
            ->withInput()
            ->with('error', '❌ Error saving violations: ' . $e->getMessage());
    }
}




public function storeMultipleAppointments(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'violation_ids' => 'required|array',
        'violation_ids.*' => 'exists:tbl_violation_record,violation_id',
        'schedule_date' => 'required|date|after_or_equal:today',
        'schedule_time' => 'required|date_format:H:i',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        $createdAppointments = [];
        $violationIds = $request->violation_ids;

        foreach ($violationIds as $violationId) {
            // Check if violation exists and is active
            $violation = ViolationRecord::where('violation_id', $violationId)
                ->where('status', 'active')
                ->first();

            if (!$violation) {
                continue; // Skip if violation doesn't exist or is not active
            }

            // Check if appointment already exists for this violation
            $existingAppointment = ViolationAppointment::where('violation_id', $violationId)
                ->whereIn('violation_app_status', ['Pending', 'Scheduled'])
                ->first();

            if ($existingAppointment) {
                continue; // Skip if active appointment already exists
            }

            // Create new appointment - MATCHING YOUR DATABASE SCHEMA
            $appointment = ViolationAppointment::create([
                'violation_id' => $violationId,
                'violation_app_date' => $request->schedule_date,
                'violation_app_time' => $request->schedule_time,
                'violation_app_status' => 'Pending',
                'status' => 'active', // Add this field as per your schema
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $createdAppointments[] = $appointment;
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => count($createdAppointments) . ' appointment(s) created successfully',
            'data' => $createdAppointments
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Error creating appointments: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Store multiple violation anecdotal records - UPDATED TO MATCH DATABASE
 */
public function storeMultipleAnecdotals(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'violation_ids' => 'required|array',
        'violation_ids.*' => 'exists:tbl_violation_record,violation_id',
        'anecdotal_date' => 'required|date',
        'anecdotal_time' => 'required|date_format:H:i',
        'violation_anec_solution' => 'required|string|min:10|max:1000',
        'violation_anec_recommendation' => 'required|string|min:10|max:1000'
    ]);

    if ($validator->fails()) {
        // 🔍 Log validation errors to laravel.log
        Log::error('Anecdotal validation errors:', $validator->errors()->toArray());

        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        $createdAnecdotals = [];
        $violationIds = $request->violation_ids;

        foreach ($violationIds as $violationId) {
            // Check if violation exists and is active - LOAD ALL NECESSARY RELATIONSHIPS
            $violation = ViolationRecord::with([
                'student.parent', // For student and parent information
                'student.adviser', // For teacher information
                'offense',
                'prefect' // For prefect of discipline information
            ])
            ->where('violation_id', $violationId)
            ->where('status', 'active')
            ->first();

            if (!$violation) {
                Log::warning("Violation not found or not active: $violationId");
                continue;
            }

            // Check if anecdotal already exists for this violation
            $existingAnecdotal = ViolationAnecdotal::where('violation_id', $violationId)
                ->where('status', 'active')
                ->first();

            if ($existingAnecdotal) {
                Log::warning("Active anecdotal already exists for violation: $violationId");
                continue;
            }

            // Create new anecdotal record - MATCHING YOUR DATABASE SCHEMA
            $anecdotal = ViolationAnecdotal::create([
                'violation_id' => $violationId,
                'violation_anec_date' => $request->anecdotal_date,
                'violation_anec_time' => $request->anecdotal_time,
                'violation_anec_solution' => $request->violation_anec_solution,
                'violation_anec_recommendation' => $request->violation_anec_recommendation,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Load all relationships for the response - INCLUDING NESTED RELATIONSHIPS
            $anecdotal->load([
                'violation.student.parent',
                'violation.student.adviser',
                'violation.offense',
                'violation.prefect'
            ]);

            $createdAnecdotals[] = $anecdotal;

            Log::info("Created anecdotal record for violation $violationId", [
                'anecdotal_id' => $anecdotal->violation_anec_id,
                'violation_id' => $violationId,
                'student_name' => $violation->student->student_fname . ' ' . $violation->student->student_lname,
                'parent_name' => $violation->student->parent ? $violation->student->parent->parent_fname . ' ' . $violation->student->parent->parent_lname : 'N/A',
                'teacher_name' => $violation->student->adviser ? $violation->student->adviser->adviser_fname . ' ' . $violation->student->adviser->adviser_lname : 'N/A'
            ]);
        }

        DB::commit();

        if (count($createdAnecdotals) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No anecdotal records were created. Please check if violations exist and are active.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => count($createdAnecdotals) . ' anecdotal record(s) created successfully',
            'data' => $createdAnecdotals
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error creating anecdotal records: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}



public function update(Request $request, $violationId)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'violator_id'        => 'required|exists:tbl_student,student_id',
        'offense_sanc_id'    => 'required|exists:tbl_offenses_with_sanction,offense_sanc_id',
        'violation_incident' => 'required|string|max:255',
        'violation_date'     => 'required|date',
        'violation_time'     => 'required|date_format:H:i',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
                         ->withErrors($validator)
                         ->withInput()
                         ->with('error', '❌ Please correct the errors and try again.');
    }

    try {
        $violation = ViolationRecord::findOrFail($violationId);

        $violation->violator_id        = $request->input('violator_id'); // <--- corrected
        $violation->offense_sanc_id    = $request->input('offense_sanc_id');
        $violation->violation_incident = $request->input('violation_incident');
        $violation->violation_date     = $request->input('violation_date');
        $violation->violation_time     = $request->input('violation_time');

        $violation->save();

        return redirect()->route('violations.index')
                         ->with('success', '✅ Violation updated successfully!');
    } catch (\Exception $e) {
        return redirect()->back()
                         ->with('error', '❌ Error updating violation: ' . $e->getMessage())
                         ->withInput();
    }
}


    public function archive(Request $request)
    {
        $request->validate([
            'violation_ids' => 'required|array',
            'violation_ids.*' => 'exists:tbl_violation_record,violation_id',
            'status' => 'required|in:inactive,cleared'
        ]);

        try {
            ViolationRecord::whereIn('violation_id', $request->violation_ids)
                   ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => count($request->violation_ids) . ' violation(s) archived as ' . $request->status . ' successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error archiving violations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get archived violations
     */
    public function getArchived()
    {
        try {
            $archivedViolations = DB::table('tbl_violation_record')
                ->join('tbl_student', 'tbl_violation_record.violator_id', '=', 'tbl_student.student_id')
                ->join('tbl_offenses_with_sanction', 'tbl_violation_record.offense_sanc_id', '=', 'tbl_offenses_with_sanction.offense_sanc_id')
                ->select(
                    'tbl_violation_record.*',
                    'tbl_student.student_fname',
                    'tbl_student.student_lname',
                    'tbl_offenses_with_sanction.offense_type'
                )
                ->whereIn('tbl_violation_record.status', ['inactive', 'cleared'])
                ->orderBy('tbl_violation_record.updated_at', 'desc')
                ->get();

            return response()->json($archivedViolations);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Restore archived violations
     */
    public function restore(Request $request)
    {
        $request->validate([
            'violation_ids' => 'required|array',
            'violation_ids.*' => 'exists:tbl_violation_record,violation_id'
        ]);

        try {
            ViolationRecord::whereIn('violation_id', $request->violation_ids)
                   ->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => count($request->violation_ids) . ' violation(s) restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring violations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete multiple violations
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'violation_ids' => 'required|array',
            'violation_ids.*' => 'exists:tbl_violation_record,violation_id'
        ]);

        try {
            ViolationRecord::whereIn('violation_id', $request->violation_ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($request->violation_ids) . ' violation(s) deleted permanently'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting violations: ' . $e->getMessage()
            ], 500);
        }
    }

    public function archiveAppointments(Request $request)
    {
        $request->validate([
            'appointment_ids' => 'required|array',
            'appointment_ids.*' => 'exists:tbl_violation_appointment,violation_app_id',
            'status' => 'required|in:Completed,Cancelled'
        ]);

        try {
            ViolationAppointment::whereIn('violation_app_id', $request->appointment_ids)
                       ->update(['violation_app_status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => count($request->appointment_ids) . ' appointment(s) archived as ' . $request->status . ' successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error archiving appointments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive violation anecdotals
     */
    public function archiveAnecdotals(Request $request)
    {
        $request->validate([
            'anecdotal_ids' => 'required|array',
            'anecdotal_ids.*' => 'exists:tbl_violation_anecdotal,violation_anec_id',
            'status' => 'required|in:completed,closed'
        ]);

        try {
            ViolationAnecdotal::whereIn('violation_anec_id', $request->anecdotal_ids)
                       ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => count($request->anecdotal_ids) . ' anecdotal record(s) archived as ' . $request->status . ' successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error archiving anecdotal records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get archived violation appointments
     */
    public function getArchivedAppointments()
    {
        try {
            $archivedAppointments = DB::table('tbl_violation_appointment')
                ->join('tbl_violation_record', 'tbl_violation_appointment.violation_id', '=', 'tbl_violation_record.violation_id')
                ->join('tbl_student', 'tbl_violation_record.violator_id', '=', 'tbl_student.student_id')
                ->select(
                    'tbl_violation_appointment.*',
                    'tbl_student.student_fname',
                    'tbl_student.student_lname'
                )
                ->whereIn('tbl_violation_appointment.violation_app_status', ['Completed', 'Cancelled'])
                ->orderBy('tbl_violation_appointment.updated_at', 'desc')
                ->get();

            return response()->json($archivedAppointments);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Get archived violation anecdotals
     */
    public function getArchivedAnecdotals()
    {
        try {
            $archivedAnecdotals = DB::table('tbl_violation_anecdotal')
                ->join('tbl_violation_record', 'tbl_violation_anecdotal.violation_id', '=', 'tbl_violation_record.violation_id')
                ->join('tbl_student', 'tbl_violation_record.violator_id', '=', 'tbl_student.student_id')
                ->select(
                    'tbl_violation_anecdotal.*',
                    'tbl_student.student_fname',
                    'tbl_student.student_lname'
                )
                ->whereIn('tbl_violation_anecdotal.status', ['completed', 'closed'])
                ->orderBy('tbl_violation_anecdotal.updated_at', 'desc')
                ->get();

            return response()->json($archivedAnecdotals);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Restore multiple archived records
     */
    public function restoreMultiple(Request $request)
    {
        $request->validate([
            'records' => 'required|array',
            'records.*.id' => 'required',
            'records.*.type' => 'required|in:violation,appointment,anecdotal'
        ]);

        try {
            $restoredCount = 0;

            foreach ($request->records as $record) {
                if ($record['type'] === 'violation') {
                    ViolationRecord::where('violation_id', $record['id'])
                        ->update(['status' => 'active']);
                    $restoredCount++;
                } elseif ($record['type'] === 'appointment') {
                    ViolationAppointment::where('violation_app_id', $record['id'])
                        ->update(['violation_app_status' => 'Pending']);
                    $restoredCount++;
                } elseif ($record['type'] === 'anecdotal') {
                    ViolationAnecdotal::where('violation_anec_id', $record['id'])
                        ->update(['status' => 'active']);
                    $restoredCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => $restoredCount . ' record(s) restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple archived records permanently
     */
    public function destroyMultipleArchived(Request $request)
    {
        $request->validate([
            'records' => 'required|array',
            'records.*.id' => 'required',
            'records.*.type' => 'required|in:violation,appointment,anecdotal'
        ]);

        try {
            $deletedCount = 0;

            foreach ($request->records as $record) {
                if ($record['type'] === 'violation') {
                    ViolationRecord::where('violation_id', $record['id'])->delete();
                    $deletedCount++;
                } elseif ($record['type'] === 'appointment') {
                    ViolationAppointment::where('violation_app_id', $record['id'])->delete();
                    $deletedCount++;
                } elseif ($record['type'] === 'anecdotal') {
                    ViolationAnecdotal::where('violation_anec_id', $record['id'])->delete();
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => $deletedCount . ' record(s) deleted permanently'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting records: ' . $e->getMessage()
            ], 500);
        }
    }


    // 🔍 Live Search Students
public function searchStudents(Request $request)
{
    $adviserId = Auth::guard('adviser')->id();
    $query = $request->input('query', '');

    $students = DB::table('tbl_student')
        ->where('adviser_id', $adviserId) // Only students under logged-in adviser
        ->where(function($q) use ($query) {
            $q->where('student_fname', 'like', "%$query%")
            ->orWhere('student_lname', 'like', "%$query%");
        })
        ->limit(10)
        ->get();

    $html = '';
    foreach ($students as $student) {
        $name = $student->student_fname . ' ' . $student->student_lname;
        $html .= "<div class='student-item' data-id='{$student->student_id}'>$name</div>";
    }
    return $html ?: '<div>No students found</div>';
}
    // Search offenses
    public function searchOffenses(Request $request)
    {
        $query = $request->input('query', '');
        $offenses = DB::table('tbl_offenses_with_sanction')
            ->select('offense_type', DB::raw('MIN(offense_sanc_id) as offense_sanc_id'))
            ->where('offense_type', 'like', "%$query%")
            ->groupBy('offense_type')
            ->limit(10)
            ->get();

        $html = '';
        foreach ($offenses as $offense) {
            $html .= "<div class='offense-item' data-id='{$offense->offense_sanc_id}'>{$offense->offense_type}</div>";
        }
        return $html ?: '<div>No results found</div>';
    }


 public function create()
    {
        return view('adviser.create-violation'); // Blade file
    }



    /**
     * Get violation details for selected violations (for modal display)
     */
    public function getSelectedViolations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'violation_ids' => 'required|array',
            'violation_ids.*' => 'exists:tbl_violation_record,violation_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid violation IDs',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $violations = ViolationRecord::with(['student', 'offense'])
                ->whereIn('violation_id', $request->violation_ids)
                ->where('status', 'active')
                ->get()
                ->map(function ($violation) {
                    return [
                        'violation_id' => $violation->violation_id,
                        'student_name' => $violation->student->student_fname . ' ' . $violation->student->student_lname,
                        'incident' => $violation->violation_incident,
                        'offense_type' => $violation->offense->offense_type,
                        'sanction' => $violation->offense->sanction_consequences
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $violations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching violation details: ' . $e->getMessage()
            ], 500);
        }
    }

}
