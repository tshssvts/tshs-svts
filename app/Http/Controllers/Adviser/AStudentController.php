<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Assuming Prefect is authenticated
use App\Models\OffensesWithSanction;
use App\Models\ViolationRecord;
use App\Models\Adviser;
use App\Models\ViolationAppointment;
use App\Models\ParentModel;




class AStudentController extends Controller
{

public function studentlist()
{
    $adviserId = Auth::guard('adviser')->id();

    // Get students only for the logged-in adviser - sorted by newest created first
    $students = Student::where('adviser_id', $adviserId)
                      ->where('status', 'active')
                      ->orderBy('created_at', 'desc') // Sort by newest created first
                      ->paginate(20);

    // Get sections only for the logged-in adviser
    $sections = Adviser::where('adviser_id', $adviserId)
                      ->select('adviser_section')
                      ->distinct()
                      ->pluck('adviser_section');

    // Summary Cards Data - filtered by logged-in adviser
    $totalStudents = Student::where('adviser_id', $adviserId)
                           ->where('status', 'active')
                           ->count();

    $activeStudents = $totalStudents; // Same as totalStudents since we're filtering active

    $completedStudents = Student::where('adviser_id', $adviserId)
                               ->where('status', 'completed')
                               ->count();

    $maleStudents = Student::where('adviser_id', $adviserId)
                          ->where('student_sex', 'male')
                          ->where('status', 'active')
                          ->count();

    $femaleStudents = Student::where('adviser_id', $adviserId)
                            ->where('student_sex', 'female')
                            ->where('status', 'active')
                            ->count();

    $otherStudents = Student::where('adviser_id', $adviserId)
                           ->where('student_sex', 'other')
                           ->where('status', 'active')
                           ->count();

    // Grade level counts for the logged-in adviser
    $adviserGradeLevel = Adviser::where('adviser_id', $adviserId)
                               ->value('adviser_gradelevel');

    $grade11Students = ($adviserGradeLevel == '11') ? $totalStudents : 0;
    $grade12Students = ($adviserGradeLevel == '12') ? $totalStudents : 0;

    // Violations and appointments for students under this adviser
    $studentIds = Student::where('adviser_id', $adviserId)->pluck('student_id');

    return view('adviser.studentlist', compact(
        'totalStudents',
        'grade11Students',
        'grade12Students',
        'students',
        'sections',
        'activeStudents',
        'completedStudents',
        'maleStudents',
        'femaleStudents',
        'otherStudents',
    ));
}

public function store(Request $request)
{
    // Get the logged-in adviser's ID
    $adviserId = Auth::guard('adviser')->id();

    $validated = $request->validate([
        'students' => 'required|array|min:1',
        'students.*.student_fname' => 'required|string|max:255',
        'students.*.student_lname' => 'required|string|max:255',
        'students.*.student_sex' => 'nullable|string|in:male,female,other',
        'students.*.student_birthdate' => 'required|date',
        'students.*.student_address' => 'required|string|max:255',
        'students.*.student_contactinfo' => 'required|string|max:50',
        'students.*.parent_id' => 'required|exists:tbl_parent,parent_id', // PARENT IS INCLUDED HERE
        'students.*.status' => 'nullable|string|in:active,inactive,transferred,graduated',
    ]);

    foreach ($validated['students'] as $studentData) {
        Student::create([
            'student_fname' => $studentData['student_fname'],
            'student_lname' => $studentData['student_lname'],
            'student_sex' => $studentData['student_sex'] ?? null,
            'student_birthdate' => $studentData['student_birthdate'],
            'student_address' => $studentData['student_address'],
            'student_contactinfo' => $studentData['student_contactinfo'],
            'parent_id' => $studentData['parent_id'], // PARENT IS ASSIGNED HERE
            'adviser_id' => $adviserId, // Automatically assign logged-in adviser
            'status' => $studentData['status'] ?? 'active',
        ]);
    }

    return redirect()->route('student.list')->with('success', 'Students saved successfully!');
}




    public function createStudent(Request $request){
        $parents = ParentModel::with('students')->get();
        $advisers = Adviser::all();
        $students = Student::with(['parent', 'adviser'])
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('adviser.create-student', compact('students', 'parents','advisers'));
    }

public function archive(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:tbl_student,student_id'
        ]);

        try {
            Student::whereIn('student_id', $request->student_ids)
                   ->update(['status' => 'inactive']);

            return response()->json([
                'success' => true,
                'message' => count($request->student_ids) . ' student(s) archived successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error archiving students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get archived students
     */
/**
 * Get archived students (including graduated)
 */
public function getArchived()
{
    try {
        $archivedStudents = Student::whereIn('status', ['inactive', 'graduated'])
                                  ->orderBy('updated_at', 'desc')
                                  ->get();

        return response()->json($archivedStudents);
    } catch (\Exception $e) {
        return response()->json([], 500);
    }
}

    /**
     * Restore archived students
     */
    public function restore(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:tbl_student,student_id'
        ]);

        try {
            Student::whereIn('student_id', $request->student_ids)
                   ->update(['status' => 'active']);

            return response()->json([
                'success' => true,
                'message' => count($request->student_ids) . ' student(s) restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete multiple students
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:tbl_student,student_id'
        ]);

        try {
            Student::whereIn('student_id', $request->student_ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($request->student_ids) . ' student(s) deleted permanently'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting students: ' . $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'student_fname' => 'required|string|max:255',
            'student_lname' => 'required|string|max:255',
            'student_sex' => 'required|string|max:10',
            'student_birthdate' => 'required|date',
            'student_address' => 'required|string|max:255',
            'student_contactinfo' => 'required|string|max:20',
            'status' => 'required|string|max:20',
        ]);

        $student = Student::findOrFail($id);
        $student->update($request->all());

        return redirect()->back()->with('success', '✅ Student updated successfully!');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->back()->with('success', 'Student deleted permanently!');
    }

public function searchParents(Request $request)
{
    try {
        // Get query from either JSON or FormData
        $query = $request->input('query');

        Log::info('Parent search request received', [
            'query' => $query,
            'all_input' => $request->all(),
            'content_type' => $request->header('Content-Type')
        ]);

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        // Make sure you're using the correct model name
        // If your model is named 'Parent', you might need to use the full namespace
        $parents = \App\Models\ParentModel::where(function($q) use ($query) {
                $q->where('parent_fname', 'LIKE', "%{$query}%")
                  ->orWhere('parent_lname', 'LIKE', "%{$query}%")
                  ->orWhereRaw("CONCAT(parent_fname, ' ', parent_lname) LIKE ?", ["%{$query}%"]);
            })
            ->where('status', 'active')
            ->limit(10)
            ->get(['parent_id', 'parent_fname', 'parent_lname']);

        Log::info('Parent search results', [
            'query' => $query,
            'results_count' => $parents->count()
        ]);

        return response()->json($parents);

    } catch (\Exception $e) {
        Log::error('Parent search error: ' . $e->getMessage());
        Log::error('Parent search stack trace: ' . $e->getTraceAsString());
        return response()->json([], 500);
    }
}

public function searchAdvisers(Request $request)
{
    $query = $request->input('query');

    $advisers = Adviser::where('adviser_fname', 'LIKE', "%{$query}%")
        ->orWhere('adviser_lname', 'LIKE', "%{$query}%")
        ->orWhereRaw("CONCAT(adviser_fname, ' ', adviser_lname) LIKE ?", ["%{$query}%"])
        ->where('status', 'active')
        ->limit(10)
        ->get(['adviser_id', 'adviser_fname', 'adviser_lname']);

    return response()->json($advisers);
}

/**
 * Mark students as cleared/graduated
 */
public function markAsCleared(Request $request)
{
    $request->validate([
        'student_ids' => 'required|array',
        'student_ids.*' => 'exists:tbl_student,student_id'
    ]);

    try {
        Student::whereIn('student_id', $request->student_ids)
               ->update(['status' => 'graduated']);

        return response()->json([
            'success' => true,
            'message' => count($request->student_ids) . ' student(s) marked as graduated successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error marking students as graduated: ' . $e->getMessage()
        ], 500);
    }
}

}
