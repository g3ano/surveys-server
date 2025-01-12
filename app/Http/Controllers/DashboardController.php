<?php

namespace App\Http\Controllers;

use App\Http\Resources\SurveyAnswerResource;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyParticipant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();

        $totalSurveys = Survey::where('user_id', $user->id)->count();
        $latestSurvey = Survey::where('user_id', $user->id)->latest('updated_at')->first();
        /** @var SurveyAnswer $totalAnswers */
        $totalAnswers = SurveyAnswer::with('survey')->whereRelation('survey', 'user_id', $user->id)->count();
        $latestAnswers = SurveyAnswer::with('survey')->whereRelation('survey', 'user_id', $user->id)
        ->latest('updated_at')->paginate(7);
        $totalParticipants = SurveyParticipant::with('survey')->whereRelation('survey', 'user_id', $user->id)->count();

        return [
            'totalSurveys' => $totalSurveys,
            'totalAnswers' => $totalAnswers,
            'totalParticipants' => $totalParticipants,
            'latestSurvey' => $latestSurvey ? new SurveyResource($latestSurvey) : null,
            'latestAnswers' => SurveyAnswerResource::collection($latestAnswers),
        ];
    }
}
