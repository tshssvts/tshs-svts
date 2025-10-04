<?php

namespace App\Http\Controllers\Prefect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Adviser;
use App\Models\ViolationRecord;
use App\Models\Complaints;
use App\Models\OffensesWithSanction;
use App\Models\ViolationAppointment;
use App\Models\ComplaintsAppointment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PDashboardController extends Controller
{
    public function dashboard()
    {
        // Total Students
        $totalStudents = Student::where('status', 'active')->count();
        
        // Total Violations
        $totalViolations = ViolationRecord::where('status', 'active')->count();
        
        // Total Complaints
        $totalComplaints = Complaints::where('status', 'active')->count();
        
        // Violation Types with counts - Only show offenses with violations
        $violationTypes = OffensesWithSanction::select(
                'offense_type',
                DB::raw('COUNT(tbl_violation_record.violation_id) as count')
            )
            ->leftJoin('tbl_violation_record', function($join) {
                $join->on('tbl_offenses_with_sanction.offense_sanc_id', '=', 'tbl_violation_record.offense_sanc_id')
                     ->where('tbl_violation_record.status', 'active');
            })
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
            
            // Count violations for this specific day
            $violationCounts[] = ViolationRecord::where('status', 'active')
                ->whereBetween('violation_date', [$dateStart, $dateEnd])
                ->count();
                
            // Count complaints for this specific day
            $complaintCounts[] = Complaints::where('status', 'active')
                ->whereBetween('complaints_date', [$dateStart, $dateEnd])
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
            ->where('violation_app_date', '>=', Carbon::today())
            ->where('violation_app_date', '<=', Carbon::today()->addDays(7))
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
            
        // For complaint appointments
        $complaintAppointments = ComplaintsAppointment::with(['complaint.complainant'])
            ->where('status', 'active')
            ->where('comp_app_date', '>=', Carbon::today())
            ->where('comp_app_date', '<=', Carbon::today()->addDays(7))
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

        $advisers = Adviser::all();

        return view('prefect.dashboard', compact(
            'advisers',
            'totalStudents',
            'totalViolations',
            'totalComplaints',
            'violationTypes',
            'recentActivity',
            'upcomingAppointments'
        ));
    }
}