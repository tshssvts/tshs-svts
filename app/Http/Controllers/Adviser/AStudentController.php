<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth; // Assuming Prefect is authenticated
use App\Models\OffensesWithSanction;
use App\Models\ViolationRecord;
use App\Models\Adviser;
use App\Models\ViolationAppointment;



class AStudentController extends Controller
{

public function studentlist()
{
    // $adviserId = Auth::guard('adviser')->id();

    // // Active and Cleared students
     $totalStudents = DB::table('tbl_student')->count();

        // Grade 11 students (join adviser to check gradelevel)
        $grade11Students = DB::table('tbl_student')
            ->join('tbl_adviser', 'tbl_student.adviser_id', '=', 'tbl_adviser.adviser_id')
            ->where('tbl_adviser.adviser_gradelevel', '11')
            ->count();

        // Grade 12 students
        $grade12Students = DB::table('tbl_student')
            ->join('tbl_adviser', 'tbl_student.adviser_id', '=', 'tbl_adviser.adviser_id')
            ->where('tbl_adviser.adviser_gradelevel', '12')
            ->count();

        // Only show active students in main table
        $students = Student::where('status', 'active')->paginate(10);
        $sections = Adviser::select('adviser_section')->distinct()->pluck('adviser_section');

        // Summary Cards Data
        $totalStudents = Student::where('status', 'active')->count();
        $activeStudents = Student::where('status', 'active')->count();
        $completedStudents = Student::where('status', 'completed')->count();
        $maleStudents = Student::where('student_sex', 'male')->where('status', 'active')->count();
        $femaleStudents = Student::where('student_sex', 'female')->where('status', 'active')->count();
        $otherStudents = Student::where('student_sex', 'other')->where('status', 'active')->count();
        $violationsToday = ViolationRecord::whereDate('violation_date', now())->count();
        $pendingAppointments = ViolationAppointment::where('violation_app_status', 'Pending')->count();

    return view('adviser.studentlist', compact('totalStudents','grade11Students','grade12Students',
            'students',
            'sections',
            'totalStudents',
            'activeStudents',
            'completedStudents',
            'maleStudents',
            'femaleStudents',
            'otherStudents',
            'violationsToday',
            'pendingAppointments'
        ));
}


}
