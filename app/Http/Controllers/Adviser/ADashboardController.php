<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\ViolationRecord;
use App\Models\Complaints;
use App\Models\OffensesWithSanction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Adviser;
use App\Models\ViolationAppointment;
use App\Models\ComplaintsAppointment;


class ADashboardController extends Controller
{
    public function dashboard()
{
    $adviserId = Auth::guard('adviser')->id();

    // Total Students (only those under this adviser)
    $totalStudents = Student::where('status', 'active')
        ->where('adviser_id', $adviserId)
        ->count();

    // Total Violations (only students under this adviser)
    $totalViolations = ViolationRecord::where('status', 'active')
        ->whereHas('student', function ($query) use ($adviserId) {
            $query->where('adviser_id', $adviserId);
        })
        ->count();

    // Total Complaints (only students under this adviser)
    $totalComplaints = Complaints::where('status', 'active')
        ->whereHas('complainant', function ($query) use ($adviserId) {
            $query->where('adviser_id', $adviserId);
        })
        ->count();

    // Violation Types with counts - Only show offenses with violations for adviser's students
// Violation Types with counts - Only show offenses with violations for adviser's students
$violationTypes = OffensesWithSanction::select(
        'offense_type',
        DB::raw('COUNT(tbl_violation_record.violation_id) as count')
    )
    ->leftJoin('tbl_violation_record', function($join) {
        $join->on('tbl_offenses_with_sanction.offense_sanc_id', '=', 'tbl_violation_record.offense_sanc_id')
             ->where('tbl_violation_record.status', 'active');
    })
    ->leftJoin('tbl_student', 'tbl_violation_record.violator_id', '=', 'tbl_student.student_id') // ✅ fixed
    ->where('tbl_student.adviser_id', $adviserId)
    ->groupBy('tbl_offenses_with_sanction.offense_type')
    ->having('count', '>', 0)
    ->orderBy('count', 'desc')
    ->get();


    // Recent Activity (last 7 days)
    $recentDates = [];
    $violationCounts = [];
    $complaintCounts = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = Carbon::now()->subDays($i);
        $dateFormatted = $date->format('M j');
        $recentDates[] = $dateFormatted;

        $dateStart = $date->copy()->startOfDay();
        $dateEnd = $date->copy()->endOfDay();

        // Count violations for this adviser's students for this specific day
        $violationCounts[] = ViolationRecord::where('status', 'active')
            ->whereBetween('violation_date', [$dateStart, $dateEnd])
            ->whereHas('student', function ($query) use ($adviserId) {
                $query->where('adviser_id', $adviserId);
            })
            ->count();

        // Count complaints for this adviser's students for this specific day
        $complaintCounts[] = Complaints::where('status', 'active')
            ->whereBetween('complaints_date', [$dateStart, $dateEnd])
            ->whereHas('complainant', function ($query) use ($adviserId) {
                $query->where('adviser_id', $adviserId);
            })
            ->count();
    }

    $recentActivity = [
        'dates' => $recentDates,
        'violations' => $violationCounts,
        'complaints' => $complaintCounts
    ];

    // Upcoming Appointments (next 7 days)
    $violationAppointments = ViolationAppointment::with(['violation.student'])
        ->where('status', 'active')
        ->where('violation_app_status', 'Scheduled')
        ->where('violation_app_date', '>=', Carbon::today())
        ->where('violation_app_date', '<=', Carbon::today()->addDays(7))
        ->whereHas('violation.student', function ($query) use ($adviserId) {
            $query->where('adviser_id', $adviserId);
        })
        ->orderBy('violation_app_date')
        ->orderBy('violation_app_time')
        ->limit(4)
        ->get()
        ->map(function($appointment) {
            $studentName = 'Unknown Student';
            if ($appointment->violation && $appointment->violation->student) {
                $studentName = $appointment->violation->student->student_fname . ' ' . $appointment->violation->student->student_lname;
            }
            return [
                'student_name' => $studentName,
                'date' => Carbon::parse($appointment->violation_app_date)->format('M d'),
                'time' => Carbon::parse($appointment->violation_app_time)->format('g:i A'),
                'type' => 'Violation',
                'color' => '#FF6B6B'
            ];
        });

    // For complaint appointments (only adviser’s students)
    $complaintAppointments = ComplaintsAppointment::with(['complaint.complainant'])
        ->where('status', 'active')
        ->where('comp_app_status', 'Scheduled')
        ->where('comp_app_date', '>=', Carbon::today())
        ->where('comp_app_date', '<=', Carbon::today()->addDays(7))
        ->whereHas('complaint.complainant', function ($query) use ($adviserId) {
            $query->where('adviser_id', $adviserId);
        })
        ->orderBy('comp_app_date')
        ->orderBy('comp_app_time')
        ->limit(4)
        ->get()
        ->map(function($appointment) {
            $studentName = 'Unknown Student';
            if ($appointment->complaint && $appointment->complaint->complainant) {
                $studentName = $appointment->complaint->complainant->student_fname . ' ' . $appointment->complaint->complainant->student_lname;
            }
            return [
                'student_name' => $studentName,
                'date' => Carbon::parse($appointment->comp_app_date)->format('M d'),
                'time' => Carbon::parse($appointment->comp_app_time)->format('g:i A'),
                'type' => 'Complaint',
                'color' => '#4ECDC4'
            ];
        });

    // Combine and sort appointments
    $upcomingAppointments = $violationAppointments->merge($complaintAppointments)
        ->sortBy(function($appointment) {
            if ($appointment['date'] === 'N/A') {
                return Carbon::now()->addYears(10);
            }
            return Carbon::parse($appointment['date'] . ' ' . $appointment['time']);
        })
        ->take(4)
        ->values();

    // If no real appointments, show sample data
    if ($upcomingAppointments->isEmpty() || ($upcomingAppointments->count() === 1 && $upcomingAppointments->first()['student_name'] === 'No upcoming appointments')) {
        $upcomingAppointments = collect([
            [
                'student_name' => 'No upcoming appointments',
                'date' => 'N/A',
                'time' => '',
                'type' => 'Information',
                'color' => '#95a5a6'
            ]
        ]);
    }
    $appointmentStats = [
    'scheduled' => $violationAppointments->count() + $complaintAppointments->count(),
    'completed' => ViolationAppointment::where('violation_app_status', 'Completed')
                    ->whereHas('violation.student', function($query) use ($adviserId) {
                        $query->where('adviser_id', $adviserId);
                    })->count() 
                    + 
                    ComplaintsAppointment::where('comp_app_status', 'Completed')
                    ->whereHas('complaint.complainant', function($query) use ($adviserId) {
                        $query->where('adviser_id', $adviserId);
                    })->count(),
    'cancelled' => ViolationAppointment::where('violation_app_status', 'Cancelled')
                    ->whereHas('violation.student', function($query) use ($adviserId) {
                        $query->where('adviser_id', $adviserId);
                    })->count() 
                    + 
                    ComplaintsAppointment::where('comp_app_status', 'Cancelled')
                    ->whereHas('complaint.complainant', function($query) use ($adviserId) {
                        $query->where('adviser_id', $adviserId);
                    })->count(),
];

    return view('adviser.dashboard', compact(
        'totalStudents',
        'totalViolations',
        'totalComplaints',
        'violationTypes',
        'recentActivity',
        'upcomingAppointments'
    ));
}

}
