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


public function create()
    {
        return view('adviser.create-complaints');
    }

public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $messages = [];
            $prefect_id = Auth::id() ?? 1;
            $savedCount = 0;

            // Get all complaints data
            $complaintsData = $request->input('complaints', []);

            // Check if we have any data
            if (empty($complaintsData)) {
                DB::rollBack();
                return back()->with('error', 'No complaint data found. Please make sure you added complaints to the summary.');
            }

            // Loop through each complaint
            foreach ($complaintsData as $complaintIndex => $complaint) {
                $complainant_id = $complaint['complainant_id'] ?? null;
                $respondent_id = $complaint['respondent_id'] ?? null;
                $offense_sanc_id = $complaint['offense_sanc_id'] ?? null;
                $date = $complaint['date'] ?? null;
                $time = $complaint['time'] ?? null;
                $incident = $complaint['incident'] ?? null;

                // Validate required fields
                if (!$complainant_id || !$respondent_id || !$offense_sanc_id || !$date || !$time || !$incident) {
                    continue;
                }

                // Validate that students and offense exist
                $complainantExists = DB::table('tbl_student')->where('student_id', $complainant_id)->exists();
                $respondentExists = DB::table('tbl_student')->where('student_id', $respondent_id)->exists();
                $offenseExists = DB::table('tbl_offenses_with_sanction')->where('offense_sanc_id', $offense_sanc_id)->exists();

                if (!$complainantExists || !$respondentExists || !$offenseExists) {
                    continue;
                }

                // Get student names for success message
                $complainant = DB::table('tbl_student')->where('student_id', $complainant_id)->first();
                $respondent = DB::table('tbl_student')->where('student_id', $respondent_id)->first();

                $complainantName = $complainant ? $complainant->student_fname . ' ' . $complainant->student_lname : 'Unknown';
                $respondentName = $respondent ? $respondent->student_fname . ' ' . $respondent->student_lname : 'Unknown';

                // Create the complaint record
                try {
                    $newComplaint = Complaints::create([
                        'complainant_id' => $complainant_id,
                        'respondent_id' => $respondent_id,
                        'prefect_id' => $prefect_id,
                        'offense_sanc_id' => $offense_sanc_id,
                        'complaints_incident' => $incident,
                        'complaints_date' => $date,
                        'complaints_time' => $time,
                        'status' => 'active'
                    ]);

                    $savedCount++;
                    $messages[] = "✅ {$complainantName} vs {$respondentName}";

                } catch (\Exception $e) {
                    continue;
                }
            }

            DB::commit();

            if ($savedCount === 0) {
                return back()->with('error',
                    'No complaints were saved. Please check that:<br>'
                    . '1. All students exist in the database<br>'
                    . '2. The offense exists in the database<br>'
                    . '3. All fields are properly filled'
                );
            }

            $successMessage = "Successfully saved $savedCount complaint record(s)!<br><br>" . implode('<br>', array_slice($messages, 0, 10));
            if (count($messages) > 10) {
                $successMessage .= "<br>... and " . (count($messages) - 10) . " more";
            }

            return back()->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving complaints: ' . $e->getMessage());
        }
    }

    /**
     * Search students for complainant/respondent.
     */
    public function searchStudents(Request $request)
    {
        $query = $request->input('query', '');
        $students = DB::table('tbl_student')
            ->where('student_fname', 'like', "%$query%")
            ->orWhere('student_lname', 'like', "%$query%")
            ->limit(10)
            ->get();

        $html = '';
        foreach ($students as $student) {
            $name = $student->student_fname . ' ' . $student->student_lname;
            $html .= "<div class='student-item' data-id='{$student->student_id}'>$name</div>";
        }

        return $html ?: '<div>No students found</div>';
    }

    /**
     * Search offenses for complaints.
     */
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

    /**
     * Get sanction for selected offense (optional, for reference).
     */
    public function getSanction(Request $request)
    {
        $offense_id = $request->input('offense_id');
        if (!$offense_id) return "Missing parameters";

        $sanction = DB::table('tbl_offenses_with_sanction')
            ->where('offense_sanc_id', $offense_id)
            ->value('sanction_consequences');

        return $sanction ?: 'No sanction found';
    }

    /**
     * Update complaint record
     */
    public function updateComplaint(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'complaints_incident' => 'required|string|max:1000',
            'offense_type' => 'required|string|max:255',
            'sanction_consequences' => 'required|string|max:500',
            'complaints_date' => 'required|date',
            'complaints_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $complaint = Complaints::findOrFail($id);

            // First, find the offense_sanc_id based on offense_type and sanction_consequences
            $offense = DB::table('tbl_offenses_with_sanction')
                ->where('offense_type', $request->offense_type)
                ->where('sanction_consequences', $request->sanction_consequences)
                ->first();

            if (!$offense) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offense type and sanction combination not found.'
                ], 404);
            }

            $complaint->update([
                'complaints_incident' => $request->complaints_incident,
                'offense_sanc_id' => $offense->offense_sanc_id,
                'complaints_date' => $request->complaints_date,
                'complaints_time' => $request->complaints_time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Complaint updated successfully!',
                'data' => $complaint
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating complaint: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update appointment record
     */
    public function updateAppointment(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'comp_app_status' => 'required|string|in:scheduled,completed,cancelled,rescheduled',
            'comp_app_date' => 'required|date',
            'comp_app_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $appointment = ComplaintsAppointment::findOrFail($id);

            $appointment->update([
                'comp_app_status' => $request->comp_app_status,
                'comp_app_date' => $request->comp_app_date,
                'comp_app_time' => $request->comp_app_time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment updated successfully!',
                'data' => $appointment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating appointment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update anecdotal record
     */
    public function updateAnecdotal(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'comp_anec_solution' => 'required|string|max:2000',
            'comp_anec_recommendation' => 'required|string|max:2000',
            'comp_anec_date' => 'required|date',
            'comp_anec_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $anecdotal = ComplaintsAnecdotal::findOrFail($id);

            $anecdotal->update([
                'comp_anec_solution' => $request->comp_anec_solution,
                'comp_anec_recommendation' => $request->comp_anec_recommendation,
                'comp_anec_date' => $request->comp_anec_date,
                'comp_anec_time' => $request->comp_anec_time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Anecdotal record updated successfully!',
                'data' => $anecdotal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating anecdotal record: ' . $e->getMessage()
            ], 500);
        }
    }

    // Store multiple anecdotal records
    public function storeMultipleAnecdotals(Request $request)
    {
        $request->validate([
            'complaint_ids' => 'required|array',
            'complaint_ids.*' => 'exists:tbl_complaints,complaints_id',
            'comp_anec_solution' => 'required|string',
            'comp_anec_recommendation' => 'required|string',
            'anecdotal_date' => 'required|date',
            'anecdotal_time' => 'required',
        ]);

        $complaintIds = $request->complaint_ids;

        try {
            DB::beginTransaction();

            $createdAnecdotals = [];

            foreach ($complaintIds as $complaintId) {
                $anecdotal = ComplaintsAnecdotal::create([
                    'complaints_id' => $complaintId,
                    'comp_anec_solution' => $request->comp_anec_solution,
                    'comp_anec_recommendation' => $request->comp_anec_recommendation,
                    'comp_anec_date' => $request->anecdotal_date,
                    'comp_anec_time' => $request->anecdotal_time,
                    'status' => 'active'
                ]);

                // Load relationships for the response
                $anecdotal->load(['complaint.complainant', 'complaint.respondent']);
                $createdAnecdotals[] = $anecdotal;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Anecdotal records created successfully!',
                'data' => $createdAnecdotals
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating anecdotal records: ' . $e->getMessage()
            ], 500);
        }
    }

    // Store multiple appointment records
    public function storeMultipleAppointments(Request $request)
    {
        $request->validate([
            'complaint_ids' => 'required|array',
            'complaint_ids.*' => 'exists:tbl_complaints,complaints_id',
            'comp_app_date' => 'required|date',
            'comp_app_time' => 'required',
            'comp_app_status' => 'required|string',
        ]);

        $complaintIds = $request->complaint_ids;

        try {
            DB::beginTransaction();

            $createdAppointments = [];

            foreach ($complaintIds as $complaintId) {
                $appointment = ComplaintsAppointment::create([
                    'complaints_id' => $complaintId,
                    'comp_app_date' => $request->comp_app_date,
                    'comp_app_time' => $request->comp_app_time,
                    'comp_app_status' => $request->comp_app_status,
                    'status' => 'active'
                ]);

                $createdAppointments[] = $appointment;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Appointments created successfully!',
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
     * Get complaint details for editing
     */
    public function getComplaintDetails($id)
    {
        try {
            $complaint = DB::table('tbl_complaints as c')
                ->join('tbl_student as comp', 'comp.student_id', '=', 'c.complainant_id')
                ->join('tbl_student as resp', 'resp.student_id', '=', 'c.respondent_id')
                ->join('tbl_offenses_with_sanction as o', 'o.offense_sanc_id', '=', 'c.offense_sanc_id')
                ->select(
                    'c.complaints_id',
                    'c.complaints_incident',
                    'c.complaints_date',
                    'c.complaints_time',
                    'comp.student_fname as complainant_fname',
                    'comp.student_lname as complainant_lname',
                    'resp.student_fname as respondent_fname',
                    'resp.student_lname as respondent_lname',
                    'o.offense_type',
                    'o.sanction_consequences'
                )
                ->where('c.complaints_id', $id)
                ->first();

            if (!$complaint) {
                return response()->json([
                    'success' => false,
                    'message' => 'Complaint not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $complaint
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching complaint details: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
 * Get archived complaint records
 */
public function getComplaintArchive()
{
    $archivedComplaints = DB::table('tbl_complaints as c')
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
        ->whereIn('c.status', ['cleared', 'inactive'])
        ->orderBy('c.complaints_date', 'desc')
        ->get();

    return response()->json($archivedComplaints);
}

/**
 * Get archived appointment records
 */
public function getAppointmentArchive()
{
    $archivedAppointments = DB::table('tbl_complaints_appointment as ca')
        ->join('tbl_complaints as c', 'c.complaints_id', '=', 'ca.complaints_id')
        ->join('tbl_student as comp', 'comp.student_id', '=', 'c.complainant_id')
        ->join('tbl_student as resp', 'resp.student_id', '=', 'c.respondent_id')
        ->select(
            'ca.comp_app_id',
            'ca.comp_app_status',
            'ca.comp_app_date',
            'ca.comp_app_time',
            'ca.status',
            'comp.student_fname as complainant_fname',
            'comp.student_lname as complainant_lname',
            'resp.student_fname as respondent_fname',
            'resp.student_lname as respondent_lname'
        )
        ->whereIn('ca.status', ['cleared', 'inactive'])
        ->orderBy('ca.comp_app_date', 'desc')
        ->get();

    return response()->json($archivedAppointments);
}

/**
 * Get archived anecdotal records
 */
public function getAnecdotalArchive()
{
    $archivedAnecdotals = DB::table('tbl_complaints_anecdotal as ca')
        ->join('tbl_complaints as c', 'c.complaints_id', '=', 'ca.complaints_id')
        ->join('tbl_student as comp', 'comp.student_id', '=', 'c.complainant_id')
        ->join('tbl_student as resp', 'resp.student_id', '=', 'c.respondent_id')
        ->select(
            'ca.comp_anec_id',
            'ca.comp_anec_solution',
            'ca.comp_anec_recommendation',
            'ca.comp_anec_date',
            'ca.comp_anec_time',
            'ca.status',
            'comp.student_fname as complainant_fname',
            'comp.student_lname as complainant_lname',
            'resp.student_fname as respondent_fname',
            'resp.student_lname as respondent_lname'
        )
        ->whereIn('ca.status', ['cleared', 'inactive'])
        ->orderBy('ca.comp_anec_date', 'desc')
        ->get();

    return response()->json($archivedAnecdotals);
}

/**
 * Update complaint status
 */
public function updateComplaintStatus(Request $request, $id)
{
    try {
        $complaint = Complaints::findOrFail($id);
        $complaint->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint status updated successfully!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error updating complaint status: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update appointment status
 */
public function updateAppointmentStatus(Request $request, $id)
{
    try {
        $appointment = ComplaintsAppointment::findOrFail($id);
        $appointment->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment status updated successfully!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error updating appointment status: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update anecdotal status
 */
public function updateAnecdotalStatus(Request $request, $id)
{
    try {
        $anecdotal = ComplaintsAnecdotal::findOrFail($id);
        $anecdotal->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Anecdotal status updated successfully!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error updating anecdotal status: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Restore complaint record
 */
public function restoreComplaint($id)
{
    try {
        $complaint = Complaints::findOrFail($id);
        $complaint->update([
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint restored successfully!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error restoring complaint: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Restore appointment record
 */
public function restoreAppointment($id)
{
    try {
        $appointment = ComplaintsAppointment::findOrFail($id);
        $appointment->update([
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment restored successfully!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error restoring appointment: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Restore anecdotal record
 */
public function restoreAnecdotal($id)
{
    try {
        $anecdotal = ComplaintsAnecdotal::findOrFail($id);
        $anecdotal->update([
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Anecdotal record restored successfully!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error restoring anecdotal record: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Permanently delete complaint record
 */
public function deleteComplaintPermanent($id)
{
    try {
        $complaint = Complaints::findOrFail($id);
        $complaint->delete();

        return response()->json([
            'success' => true,
            'message' => 'Complaint permanently deleted!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error deleting complaint: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Permanently delete appointment record
 */
public function deleteAppointmentPermanent($id)
{
    try {
        $appointment = ComplaintsAppointment::findOrFail($id);
        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appointment permanently deleted!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error deleting appointment: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Permanently delete anecdotal record
 */
public function deleteAnecdotalPermanent($id)
{
    try {
        $anecdotal = ComplaintsAnecdotal::findOrFail($id);
        $anecdotal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Anecdotal record permanently deleted!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error deleting anecdotal record: ' . $e->getMessage()
        ], 500);
    }
}

}
