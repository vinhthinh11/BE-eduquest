<?php

namespace App\Http\Controllers;

use App\Models\classes;
use App\Models\grade;
use App\Models\scores;
use Illuminate\Http\Request;

class StatistController extends Controller
{
    public function statistics(Request $request)
    {
        $info = new grade();
        $grade_id = $request->input('grade_id');
        return response()->json($info->statistics($grade_id));
    }

    public function statistics_score(Request $request)
    {
        $info = new grade();
        $grade_id = $request->input('grade_id');
        return response()->json($info->statistics_score($grade_id));
    }
}
