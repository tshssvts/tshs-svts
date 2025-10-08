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
        $parents = ParentModel::where('status', 'active')->paginate(10);
        $archivedParents = ParentModel::where('status', 'inactive')->get();

    return view('adviser.parentlist', compact('parents', 'archivedParents','totalParents','activeParents','archivedParents'));


}

}
