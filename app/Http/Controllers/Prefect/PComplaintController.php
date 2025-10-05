<?php
namespace App\Http\Controllers\Prefect;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Complaints; // Make sure you have a Complaint model
use App\Models\ComplaintsAppointment; // Make sure you have a Complaint model
use App\Models\ComplaintsAnecdotal; // Make sure you have a Complaint model

use App\Models\OffensesWithSanction; // Make sure you have a Complaint model

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class PComplaintController extends Controller
{

    public function create()
    {
        return view('prefect.create-complaints');
    }
public function index()
{
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

    return view('prefect.complaint', compact(
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




 public function store(Request $request)
    {
        Log::info('=== COMPLAINT STORE METHOD STARTED ===');
        Log::info('Full Request Data:', $request->all());
        
        try {
            DB::beginTransaction();

            $messages = [];
            $prefect_id = Auth::id() ?? 1;
            $savedCount = 0;

            // Get all complaints data
            $complaintsData = $request->input('complaints', []);
            
            Log::info('Complaints Data Structure:', $complaintsData);
            Log::info('Number of complaints to process:', ['count' => count($complaintsData)]);

            // Check if we have any data
            if (empty($complaintsData)) {
                Log::warning('No complaints data found in request');
                DB::rollBack();
                return back()->with('error', 'No complaint data found. Please make sure you added complaints to the summary.');
            }

            // Loop through each complaint
            foreach ($complaintsData as $complaintIndex => $complaint) {
                Log::info("Processing complaint index: {$complaintIndex}", $complaint);
                
                $complainant_id = $complaint['complainant_id'] ?? null;
                $respondent_id = $complaint['respondent_id'] ?? null;
                $offense_sanc_id = $complaint['offense_sanc_id'] ?? null;
                $date = $complaint['date'] ?? null;
                $time = $complaint['time'] ?? null;
                $incident = $complaint['incident'] ?? null;

                // Validate required fields
                if (!$complainant_id || !$respondent_id || !$offense_sanc_id || !$date || !$time || !$incident) {
                    Log::warning("Skipping complaint {$complaintIndex} - missing required fields", [
                        'complainant_id' => $complainant_id,
                        'respondent_id' => $respondent_id,
                        'offense_sanc_id' => $offense_sanc_id,
                        'date' => $date,
                        'time' => $time,
                        'incident' => $incident
                    ]);
                    continue;
                }

                // Validate that students and offense exist
                $complainantExists = DB::table('tbl_student')->where('student_id', $complainant_id)->exists();
                $respondentExists = DB::table('tbl_student')->where('student_id', $respondent_id)->exists();
                $offenseExists = DB::table('tbl_offenses_with_sanction')->where('offense_sanc_id', $offense_sanc_id)->exists();

                if (!$complainantExists) {
                    Log::warning("Complainant does not exist: {$complainant_id}");
                    continue;
                }
                
                if (!$respondentExists) {
                    Log::warning("Respondent does not exist: {$respondent_id}");
                    continue;
                }
                
                if (!$offenseExists) {
                    Log::warning("Offense does not exist: {$offense_sanc_id}");
                    continue;
                }

                // Get student names for success message
                $complainant = DB::table('tbl_student')->where('student_id', $complainant_id)->first();
                $respondent = DB::table('tbl_student')->where('student_id', $respondent_id)->first();
                
                $complainantName = $complainant ? $complainant->student_fname . ' ' . $complainant->student_lname : 'Unknown';
                $respondentName = $respondent ? $respondent->student_fname . ' ' . $respondent->student_lname : 'Unknown';

                Log::info("Creating complaint record: {$complainantName} vs {$respondentName}");

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

                    Log::info("Complaint created successfully with ID: {$newComplaint->id}");
                    $savedCount++;
                    $messages[] = "✅ {$complainantName} vs {$respondentName}";
                    
                } catch (\Exception $e) {
                    Log::error("Failed to create complaint record: " . $e->getMessage());
                    continue;
                }
            }

            DB::commit();

            Log::info("Complaint storage completed. Saved: {$savedCount}, Attempted: " . count($complaintsData));

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
            Log::error('Complaint storage error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
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
     * Store multiple complaints
//      */
// public function store(Request $request)
// {
//     // ✅ Remove or comment out the dd once tested
//     // dd($request->all());

//     try {
//         // Loop through each group
//         foreach ($request->complainant_id as $groupId => $complainants) {
//             $respondents = $request->respondent_id[$groupId] ?? [];
//             $offenseId   = $request->offense_sanc_id[$groupId] ?? null;
//             $date        = $request->complaints_date[$groupId] ?? null;
//             $time        = $request->complaints_time[$groupId] ?? null;
//             $incident    = $request->complaints_incident[$groupId] ?? null;
//             $prefectId   = auth()->prefect_id ?? 1;

//             foreach ($complainants as $compId) {
//                 foreach ($respondents as $respId) {
//                     Complaints::create([
//                         'complainant_id'      => $compId,
//                         'respondent_id'       => $respId,
//                         'prefect_id'          => $prefectId,
//                         'offense_sanc_id'     => $offenseId,
//                         'complaints_incident' => $incident,
//                         'complaints_date'     => $date,
//                         'complaints_time'     => $time,
//                         'status'              => 'active',
//                     ]);
//                 }
//             }
//         }

//         return redirect()->route('prefect.complaints')
//                          ->with('messages', ['✅ All complaints stored successfully!']);
//     } catch (\Exception $e) {
//         return redirect()->route('prefect.complaints')
//                          ->with('messages', ['❌ Error saving complaints: ' . $e->getMessage()]);
//     }
// }


   public function update(Request $request, $id)
{

            // dd($request->all());

    $complaint = Complaints::findOrFail($id);

    $request->validate([
        'complainant_id'     => 'required|exists:tbl_student,student_id',
        'respondent_id'      => 'required|exists:tbl_student,student_id',
        'offense_sanc_id'    => 'required|exists:tbl_offenses_with_sanction,offense_sanc_id',
        'complaints_incident'=> 'required|string',
        'complaints_date'    => 'required|date',
        'complaints_time'    => 'required',
    ]);

    $complaint->update([
        'complainant_id'      => $request->complainant_id,
        'respondent_id'       => $request->respondent_id,
        'offense_sanc_id'     => $request->offense_sanc_id,
        'complaints_incident' => $request->complaints_incident,
        'complaints_date'     => $request->complaints_date,
        'complaints_time'     => $request->complaints_time,
    ]);

    return redirect()->route('prefect.complaints')
                     ->with('success', 'Complaint updated successfully.');
}



}
