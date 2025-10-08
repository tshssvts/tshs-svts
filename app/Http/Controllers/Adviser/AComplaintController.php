<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth; // Assuming Prefect is authenticated
use App\Models\OffensesWithSanction;
use App\Models\Complaints;
use App\Models\ComplaintsAppointment;
use App\Models\ComplaintsAnecdotal;
use Carbon\Carbon;

class AComplaintController extends Controller
{
public function complaintsall()
{
    // $adviserId = Auth::guard('adviser')->id();

 // ✅ Load Anecdotal + Appointment models with relationships
    $cappointments = ComplaintsAppointment::with(['complaint.complainant', 'complaint.respondent'])->get();
    $canecdotals = ComplaintsAnecdotal::with(['complaint.complainant', 'complaint.respondent'])->get();

    // ✅ Get Actual Complaint Date Range
    $mostRecentComplaintDate = DB::table('tbl_complaints')->max('complaints_date');
    $earliestComplaintDate = DB::table('tbl_complaints')->min('complaints_date');

    $referenceDate = $mostRecentComplaintDate ? Carbon::parse($mostRecentComplaintDate) : Carbon::today();

    // ✅ Date Ranges
    $today = $referenceDate->copy();
    $startOfWeek = $referenceDate->copy()->startOfWeek();
    $endOfWeek = $referenceDate->copy()->endOfWeek();
    $startOfMonth = $referenceDate->copy()->startOfMonth();
    $endOfMonth = $referenceDate->copy()->endOfMonth();

    // ✅ Summary Counts
    $dailyComplaints = DB::table('tbl_complaints')
        ->whereDate('complaints_date', $today)
        ->count();

    $weeklyComplaints = DB::table('tbl_complaints')
        ->whereBetween('complaints_date', [$startOfWeek, $endOfWeek])
        ->count();

    $monthlyComplaints = DB::table('tbl_complaints')
        ->whereBetween('complaints_date', [$startOfMonth, $endOfMonth])
        ->count();

    // ✅ Fetch Complaint Records
    $complaints = DB::table('tbl_complaints as c')
        ->join('tbl_student as comp', 'comp.student_id', '=', 'c.complainant_id')
        ->join('tbl_student as resp', 'resp.student_id', '=', 'c.respondent_id')
        ->join('tbl_offenses_with_sanction as o', 'o.offense_sanc_id', '=', 'c.offense_sanc_id')
        ->select(
            'c.complaints_id',
            'c.complaints_incident',
            'c.complaints_date',
            'c.complaints_time',
            'c.status',
            'comp.student_fname as complainant_fname',
            'comp.student_lname as complainant_lname',
            'resp.student_fname as respondent_fname',
            'resp.student_lname as respondent_lname',
            'o.offense_type',
            'o.sanction_consequences'
        )
        ->orderBy('c.complaints_date', 'desc')
        ->paginate(10);
    return view('adviser.complaintsall', compact(
        'complaints',
        'cappointments',
        'canecdotals',
        'mostRecentComplaintDate',
        'earliestComplaintDate',
        'referenceDate',
        'today',
        'startOfWeek',
        'endOfWeek',
        'startOfMonth',
        'endOfMonth',
        'dailyComplaints',
        'weeklyComplaints',
        'monthlyComplaints'
    ));

}

}
