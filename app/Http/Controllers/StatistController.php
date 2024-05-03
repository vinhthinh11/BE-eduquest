<?php

namespace App\Http\Controllers;

use App\Models\classes;
use App\Models\grade;
use App\Models\questions;
use App\Models\scores;
use App\Models\student;
use App\Models\subjects;
use App\Models\tests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Claims\Subject;

class StatistController extends Controller
{
    public function statist(Request $request)
    {
        $grade_id = $request->grade_id;
        $statistics = Subjects::select('subjects.subject_detail AS subject_detail', 'subjects.subject_id AS subject_id', DB::raw('SUM(IF(scores.test_code IS NOT NULL, 1, 0)) AS tested_time'))
            ->leftJoin('tests', 'subjects.subject_id', '=', 'tests.subject_id')
            ->leftJoin('scores', 'tests.test_code', '=', 'scores.test_code')
            ->when($grade_id, function ($query) use ($grade_id) {
                $query->where('tests.grade_id', $grade_id)
                    ->havingRaw('SUM(IF(scores.test_code IS NOT NULL, 1, 0)) > 0');
            }, function ($query) {
                $query->whereNull('scores.test_code')
                    ->havingRaw('SUM(IF(scores.test_code IS NOT NULL, 1, 0)) = 0');
            })
            ->groupBy('subject_detail', 'subject_id')
            ->get();

        return response()->json([
            'message' => 'Show thống kê thành công!',
            'statistics' => $statistics
        ]);
    }
    public function statistScores(Request $request)
    {
        $grade_id = $request->grade_id;
        $query = scores::selectRaw('SUM(IF(scores.score_number < 5, 1, 0)) AS bad_score')
            ->selectRaw('SUM(IF(scores.score_number >= 5 AND scores.score_number < 6.5, 1, 0)) AS complete_score')
            ->selectRaw('SUM(IF(scores.score_number >= 6.5 AND scores.score_number < 8, 1, 0)) AS good_score')
            ->selectRaw('SUM(IF(scores.score_number >= 8, 1, 0)) AS excellent_score')
            ->when($grade_id, function ($query) use ($grade_id) {
                $query->join(tests::getTableName(), function ($join) use ($grade_id) {
                    $join->on('scores.test_code', '=', tests::getTableName() . '.test_code')
                        ->where(tests::getTableName() . '.grade_id', '=', $grade_id);
                });
            });

        $result = $query->first([
            'bad_score',
            'complete_score',
            'good_score',
            'excellent_score'
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Show thống kê điểm thành công!',
            'data'      => $result
        ]);
    }

    public function statistStudent(Request $request)
    {
        $user = $request->user('students');
        $statistics = subjects::select('subjects.subject_detail AS subject_detail', 'subjects.subject_id AS subject_id', DB::raw('IF(practice_scores.practice_code IS NOT NULL AND practice.student_id=\'' . $user->student_id . '\',1,0) AS test_existed'))
                                ->leftJoin('practice', 'subjects.subject_id', '=', 'practice.subject_id')
                                ->leftJoin('practice_scores', 'practice.practice_code', '=', 'practice_scores.practice_code')
                                // ->groupBy('subject_detail', 'subjects.subject_id')
                                ->get();

        return response()->json([
            'message'   => 'Show thống kê cho học sinh thành công!',
            'data'      => $statistics
        ]);
    }
    public function statistScoreStudent(Request $request)
    {
        $student_id = $request->student_id;
        $subject_id = $request->subject_id;
        $results = DB::table('practice_scores')
                    ->select('practice_scores.score_number as score', 'practice_scores.completion_time as day')
                    ->leftJoin('practice', 'practice_scores.practice_code', '=', 'practice.practice_code')
                    ->leftJoin('subjects', 'practice.subject_id', '=', 'subjects.subject_id')
                    ->where('practice.student_id', $student_id)
                    ->where('practice.subject_id', $subject_id)
                    ->orderBy('practice_scores.completion_time')
                    ->limit(10)
                    ->get();

        return response()->json([
            'message'   => 'Show thống kê điểm học sinh thành công!',
            'data'      => $results
        ]);
    }

    public function listStatistTest(Request $request)
    {
        $grade_id = questions::find($request->grade_id);

        $info = questions::select('questions.grade_id', 'subject_id', 'level_id', DB::raw('count(question_id) as total_question'))
            ->join('grades', 'questions.grade_id', '=', 'grades.grade_id')
            ->where('questions.grade_id', $grade_id)
            ->groupBy('questions.grade_id', 'subject_id', 'level_id')
            ->orderBy('questions.grade_id')
            ->get();

        return response()->json($info);
    }
}
