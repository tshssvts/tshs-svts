<?php

namespace App\Http\Controllers\Prefect;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\OffensesWithSanction;
use App\Models\ViolationRecord;
use App\Models\ViolationAnecdotal; // Make sure to import this

class PComplaintAnecdotalController extends Controller
{
     public function createCAnecdotal()
    {
        return view('prefect.create-CAnecdotal');
    }
}
