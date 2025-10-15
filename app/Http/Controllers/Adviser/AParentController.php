<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth; // Assuming Prefect is authenticated
use App\Models\OffensesWithSanction;
use App\Models\ParentModel;

class AParentController extends Controller
{
    public function parentlist()
{
        $totalParents = DB::table('tbl_parent')->count();

        // Get active parents
        $activeParents = DB::table('tbl_parent')
            ->where('status', 'active')
            ->count();

        // Get archived parents
        $archivedParents = DB::table('tbl_parent')
            ->where('status', 'archived')
            ->count();
// Alternative if no created_at column exists:
$parents = ParentModel::where('status', 'active')
    ->orderBy('parent_id', 'desc') // Sort by highest ID first (newest)
    ->paginate(20);        $archivedParents = ParentModel::where('status', 'inactive')->get();

    return view('adviser.parentlist', compact('parents', 'archivedParents','totalParents','activeParents','archivedParents'));


}

 /**
     * Store new parent
     */public function parentStore(Request $request)
{
    // Debug: Check what's being received
    \Log::info('Received data:', $request->all());

    // Validate the array of parents
    $request->validate([
        'parents' => 'required|array|min:1',
        'parents.*.parent_fname' => 'required|string|max:255',
        'parents.*.parent_lname' => 'required|string|max:255',
        'parents.*.parent_sex' => 'required|in:Male,Female,Other', // Match your form values
        'parents.*.parent_relationship' => 'required|string|max:255',
        'parents.*.parent_birthdate' => 'required|date',
        'parents.*.parent_contactinfo' => 'required|string|max:20',
        'parents.*.parent_email' => 'nullable|email|max:255',
    ]);

    try {
        $insertedCount = 0;
        $errors = [];

        foreach ($request->parents as $index => $parentData) {
            // Check if required fields are not empty
            if (empty($parentData['parent_fname']) || empty($parentData['parent_lname']) ||
                empty($parentData['parent_birthdate']) || empty($parentData['parent_contactinfo'])) {
                $errors[] = "Parent #" . ($index + 1) . " has missing required fields";
                continue;
            }

            DB::table('tbl_parent')->insert([
                'parent_fname' => $parentData['parent_fname'],
                'parent_lname' => $parentData['parent_lname'],
                'parent_sex' => $parentData['parent_sex'],
                'parent_relationship' => $parentData['parent_relationship'],
                'parent_birthdate' => $parentData['parent_birthdate'],
                'parent_contactinfo' => $parentData['parent_contactinfo'],
                'parent_email' => $parentData['parent_email'] ?? null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $insertedCount++;
        }

        if (!empty($errors)) {
            return back()->withInput()->with('error', implode(', ', $errors));
        }

        if ($insertedCount > 0) {
            return redirect()->route('parent.list')->with('success', $insertedCount . ' parent(s) added successfully!');
        } else {
            return back()->withInput()->with('error', 'No parents were saved. Please check your data.');
        }

    } catch (\Exception $e) {
        \Log::error('Error saving parents: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Error saving parents: ' . $e->getMessage());
    }
}

 public function createParent()
    {
        return view('adviser.create-parent');
    }


    /**
     * Update parent
     */
    public function parentUpdate(Request $request, $id)
{
    // Validate the request
    $request->validate([
        'parent_fname' => 'required|string|max:255',
        'parent_lname' => 'required|string|max:255',
        'parent_birthdate' => 'required|date',
        'parent_contactinfo' => 'required|string|max:20',
        'parent_sex' => 'required|in:Male,Female',
        'parent_relationship' => 'required|string|max:255',
        'status' => 'required|in:active,inactive',
    ]);

    try {
        $parent = DB::table('tbl_parent')->where('parent_id', $id)->first();

        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'Parent not found.'
            ], 404);
        }

        DB::table('tbl_parent')->where('parent_id', $id)->update([
            'parent_fname' => $request->parent_fname,
            'parent_lname' => $request->parent_lname,
            'parent_birthdate' => $request->parent_birthdate,
            'parent_contactinfo' => $request->parent_contactinfo,
            'parent_sex' => $request->parent_sex,
            'parent_relationship' => $request->parent_relationship,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Parent updated successfully!'
        ]);

    } catch (\Exception $e) {
        \Log::error('Error updating parent: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error updating parent: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Move parents to archive (set status to inactive)
     */
    public function archiveParents(Request $request)
        {
            $request->validate([
                'parent_ids' => 'required|array',
                'parent_ids.*' => 'exists:tbl_parent,parent_id'
            ]);

            ParentModel::whereIn('parent_id', $request->parent_ids)
                ->update(['status' => 'inactive']);

            return response()->json([
                'success' => true,
                'message' => count($request->parent_ids) . ' parent(s) moved to archive successfully!'
            ]);
        }

    /**
     * Get archived parents (status = inactive)
     */
    public function getArchivedParents()
    {
        $archivedParents = ParentModel::where('status', 'inactive')->get();
        return response()->json($archivedParents);
    }

    /**
     * Restore parents from archive
     */
    public function restoreParents(Request $request)
    {
        $request->validate([
            'parent_ids' => 'required|array',
            'parent_ids.*' => 'exists:tbl_parent,parent_id'
        ]);

        ParentModel::whereIn('parent_id', $request->parent_ids)
            ->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => count($request->parent_ids) . ' parent(s) restored successfully!'
        ]);
    }

    /**
     * Permanently delete parents
     */
    public function destroyParentsPermanent(Request $request)
    {
        $request->validate([
            'parent_ids' => 'required|array',
            'parent_ids.*' => 'exists:tbl_parent,parent_id'
        ]);

        ParentModel::whereIn('parent_id', $request->parent_ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->parent_ids) . ' parent(s) permanently deleted!'
        ]);
    }


    /**
     * Single parent destroy (for individual deletion)
     */
    public function destroyParent($id)
    {
        try {
            $parent = DB::table('tbl_parent')->where('parent_id', $id)->first();

            if (!$parent) {
                return redirect()->back()->with('error', 'Parent not found.');
            }

            // Update status to inactive instead of deleting
            DB::table('tbl_parent')->where('parent_id', $id)->update([
                'status' => 'inactive',
                'updated_at' => now(),
            ]);

            return redirect()->route('adviser.parentlist')->with('success', 'Parent moved to archive successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error archiving parent: ' . $e->getMessage());
        }
    }

    public function getArchivedParentsCount()
    {
        $count = ParentModel::where('status', 'inactive')->count();
        return response()->json(['count' => $count]);
    }


    /**
     * Send SMS to parent
     */
    public function sendSms(Request $request)
    {
        $parentId = $request->parent_id;
        $message = $request->message;

        // Retrieve parent info
        $parent = DB::table('tbl_parent')->where('parent_id', $parentId)->first();

        if (!$parent) {
            return back()->with('error', 'Parent not found.');
        }

        // Here you would integrate your SMS API
        // Example: SmsService::send($parent->parent_contactinfo, $message);

        return back()->with('success', 'SMS sent to ' . $parent->parent_fname);
    }


}
