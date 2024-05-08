<?php

namespace App\Http\Controllers;

use App\Models\practice_scores;
use App\Models\scores;
use App\Models\subjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistController extends Controller
{
    //Thống kê của ADMIN
    public function statist(Request $request)
    {
        $query = subjects::select('subjects.subject_detail', 'subjects.subject_id')
            ->leftJoin('tests', 'subjects.subject_id', '=', 'tests.subject_id')
            ->leftJoin('scores', 'tests.test_code', '=', 'scores.test_code')
            ->groupBy('subjects.subject_detail', 'subjects.subject_id');

        if ($request->grade_id) {
            $query->selectRaw('SUM(IF(scores.test_code IS NOT NULL AND tests.grade_id = ?, 1, 0)) AS tested_time', [$request->grade_id]);
        } else {
            $query->selectRaw('SUM(IF(scores.test_code IS NOT NULL, 1, 0)) AS tested_time');
        }

        $statistics = $query->get();

        return response()->json([
            'message' => 'Thống kê thành công!',
            'data' => $statistics
        ]);
    }
    public function statistScores(Request $request)
    {
        $query = scores::selectRaw('SUM(IF(scores.score_number < 5, 1, 0)) AS bad, SUM(IF(scores.score_number >= 5 AND scores.score_number < 6.5, 1, 0)) AS complete, SUM(IF(scores.score_number >= 6.5 AND scores.score_number < 8, 1, 0)) AS good, SUM(IF(scores.score_number >= 8, 1, 0)) AS excellent')
            ->leftJoin('tests', 'scores.test_code', '=', 'tests.test_code');

        if ($request->grade_id) {
            $query->where('tests.grade_id', $request->grade_id);
        }

        $statistics = $query->get();

        return response()->json([
            'message' => 'Thống kê điểm số thành công!',
            'data' => $statistics
        ]);
    }

    //Thống kê của học sinh
    public function statistStudent(Request $request)
    {
        $statistics = subjects::select('subjects.subject_detail', 'subjects.subject_id', DB::raw('SUM(IF(practice_scores.practice_code IS NOT NULL, 1, 0)) AS tested_time'))
            ->leftJoin('practice', 'subjects.subject_id', '=', 'practice.subject_id')
            ->leftJoin('practice_scores', 'practice.practice_code', '=', 'practice_scores.practice_code')
            ->where('practice.student_id', $request->user()->id)
            ->groupBy('subjects.subject_detail', 'subjects.subject_id')
            ->get();

        return response()->json([
            'message' => 'Thống kê thành công!',
            'data' => $statistics
        ]);
    }
    public function subjectScore(Request $request)
    {
        $statistics = practice_scores::select('practice_scores.score_number as score', 'practice_scores.completion_time as day')
            ->leftJoin('practice', 'practice_scores.practice_code', '=', 'practice.practice_code')
            ->leftJoin('subjects', 'practice.subject_id', '=', 'subjects.subject_id')
            ->where('practice.student_id', auth()->id())
            ->where('practice.subject_id', $request->subject_id)
            ->orderBy('practice_scores.completion_time')
            ->limit(10)
            ->get();

        return response()->json([
            'message' => 'Thống kê điểm môn học thành công!',
            'data' => $statistics
        ]);
    }
}
