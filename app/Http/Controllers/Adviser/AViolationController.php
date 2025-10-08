<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;

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
        // $adviserId = Auth::guard('adviser')->id();

      $vappointments = ViolationAppointment::with(['violation.student'])->get();
$vanecdotals = ViolationAnecdotal::with(['violation.student'])->get();

    // Get the actual dates from your violation records
    $mostRecentViolationDate = DB::table('tbl_violation_record')->max('violation_date');
    $earliestViolationDate = DB::table('tbl_violation_record')->min('violation_date');

    // Use the most recent violation date for calculations, or today if no records exist
    $referenceDate = $mostRecentViolationDate ? Carbon::parse($mostRecentViolationDate) : Carbon::today();

    // Calculate date ranges based on the actual violation dates
    $today = $referenceDate->copy();
    $startOfWeek = $referenceDate->copy()->startOfWeek();
    $endOfWeek = $referenceDate->copy()->endOfWeek();
    $startOfMonth = $referenceDate->copy()->startOfMonth();
    $endOfMonth = $referenceDate->copy()->endOfMonth();

    // ✅ Summary Counts
    $dailyViolations = DB::table('tbl_violation_record')
        ->whereDate('violation_date', $today)
        ->count();

    $weeklyViolations = DB::table('tbl_violation_record')
        ->whereBetween('violation_date', [$startOfWeek, $endOfWeek])
        ->count();

    $monthlyViolations = DB::table('tbl_violation_record')
        ->whereBetween('violation_date', [$startOfMonth, $endOfMonth])
        ->count();

    // ✅ Fetch Main Violation Records
    $violations = ViolationRecord::with(['student', 'offense'])
        ->orderBy('violation_date', 'desc')
        ->paginate(30);

    // ✅ Fetch Violation Appointments
    $appointments = DB::table('tbl_violation_appointment')
        ->join('tbl_violation_record', 'tbl_violation_appointment.violation_id', '=', 'tbl_violation_record.violation_id')
        ->select(
            'tbl_violation_appointment.*',
            'tbl_violation_record.violation_incident'
        )
        ->orderBy('tbl_violation_appointment.violation_app_date', 'desc')
        ->paginate(30);

    // ✅ Fetch Violation Anecdotals
    $anecdotals = DB::table('tbl_violation_anecdotal')
        ->join('tbl_violation_record', 'tbl_violation_anecdotal.violation_id', '=', 'tbl_violation_record.violation_id')
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

    //NEWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW FOR ADVISER

 public function Acreate()
    {
        return view('adviser.create-violation'); // Blade file
    }


    // public function Astore(Request $request)
    // {
    //     $student_ids = $request->input('student_id', []);
    //     $offense_ids = $request->input('offense', []);
    //     $dates       = $request->input('date', []);
    //     $times       = $request->input('time', []);
    //     $incidents   = $request->input('incident', []);

    //     $messages = [];

    //     foreach ($offense_ids as $i => $offense_id) {
    //         $student_id = $student_ids[$i] ?? null;
    //         $date       = $dates[$i] ?? null;
    //         $time       = $times[$i] ?? null;
    //         $incident   = $incidents[$i] ?? null;

    //         if (!$student_id || !$offense_id) continue;

    //         $previous_count = DB::table('tbl_violation_record')
    //             ->where('student_id', $student_id)
    //             ->where('offense_id', $offense_id)
    //             ->count();

    //         $stage_number = $previous_count + 1;

    //         $sanction_row = DB::table('tbl_offenses_with_sanction')
    //             ->where('offense_sanc_id', $offense_id)
    //             ->where('stage_number', $stage_number)
    //             ->first();

    //         if (!$sanction_row) {
    //             $sanction_row = DB::table('tbl_offenses_with_sanction')
    //                 ->where('offense_sanc_id', $offense_id)
    //                 ->orderByDesc('stage_number')
    //                 ->first();
    //         }

    //         $sanction = $sanction_row->sanction_consequences ?? 'No sanction found';

    //         DB::table('tbl_violation_record')->insert([
    //             'student_id' => $student_id,
    //             'offense_id' => $offense_id,
    //             'violation_date' => $date,
    //             'violation_time' => $time,
    //             'violation_details' => $incident
    //         ]);

    //         $messages[] = "✅ Violation recorded. Sanction: $sanction";
    //     }

    //     return back()->with('messages', $messages);
    // }

    // public function AsearchStudents(Request $request)
    // {
    //     $query = $request->input('query', '');
    //     $students = DB::table('tbl_student')
    //         ->where('student_fname', 'like', "%$query%")
    //         ->orWhere('student_lname', 'like', "%$query%")
    //         ->limit(10)
    //         ->get();

    //     $html = '';
    //     foreach ($students as $student) {
    //         $name = $student->student_fname . ' ' . $student->student_lname;
    //         $html .= "<div class='student-item' data-id='{$student->student_id}'>$name</div>";
    //     }

    //     return $html ?: '<div>No students found</div>';
    // }

    // public function AsearchOffenses(Request $request)
    // {
    //     $query = $request->input('query', '');
    //     $offenses = DB::table('tbl_offenses_with_sanction')
    //         ->select('offense_type', DB::raw('MIN(offense_sanc_id) as offense_sanc_id'))
    //         ->where('offense_type', 'like', "%$query%")
    //         ->groupBy('offense_type')
    //         ->limit(10)
    //         ->get();

    //     $html = '';
    //     foreach ($offenses as $offense) {
    //         $html .= "<div class='offense-item' data-id='{$offense->offense_sanc_id}'>{$offense->offense_type}</div>";
    //     }

    //     return $html ?: '<div>No results found</div>';
    // }

    // public function AgetSanction(Request $request)
    // {
    //     $student_id = $request->input('student_id');
    //     $offense_id = $request->input('offense_id');

    //     if (!$student_id || !$offense_id) return "Missing parameters";

    //     $previous_count = DB::table('tbl_violation_record')
    //         ->where('violator_id', $student_id)
    //         ->where('offense_sanc_id', $offense_id)
    //         ->count();

    //     $stage_number = $previous_count + 1;

    //     $sanction = DB::table('tbl_offenses_with_sanction')
    //         ->where('offense_sanc_id', $offense_id)
    //         ->where('stage_number', $stage_number)
    //         ->value('sanction_consequences');

    //     if (!$sanction) {
    //         $sanction = DB::table('tbl_offenses_with_sanction')
    //             ->where('offense_sanc_id', $offense_id)
    //             ->orderByDesc('stage_number')
    //             ->value('sanction_consequences');
    //     }

    //     return $sanction ?: 'No sanction found';
    // }


}
