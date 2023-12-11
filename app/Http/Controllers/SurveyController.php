<?php

namespace App\Http\Controllers;

use App\Http\Requests\SurveyStoreRequest;
use App\Http\Requests\SurveyUpdateRequest;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $surveys = SurveyResource::collection(
            Survey::where('user_id', $user->id)->where('user_id', $user->id)->latest()->paginate(10)
        );
        return response()->json([
            'surveys' => $surveys,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SurveyStoreRequest $request)
    {
        $data = $request->validated();
        $questions = $data['questions'];
        unset($data['questions']);
        $data['slug'] = Str::slug($data['title']);
        $survey = Survey::create($data);

        // TODO: add image upload feature

        $newQuestions = collect();

        foreach ($questions as $question) {
            $question['survey_id'] = $survey->id;

            if (is_array($question['data'])) {
                $question['data'] = json_encode($question['data']);
            }

            $validator = Validator::make($question, [
                'type' => ['required', Rule::in(['textarea', 'radio', 'select', 'checkbox', 'text'])],
                'question' => ['required'],
                'description' => ['nullable'],
                'data' => ['present'],
                'survey_id' => [Rule::exists('surveys', 'id')],
            ]);

            $newQuestions[] = SurveyQuestion::create($validator->validated());
        }

        return response()->json([
            'survey' => SurveyResource::make($survey),
            'questions' => $newQuestions,
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $survey = Survey::find($id);
        $user = $request->user();

        if ($survey->user_id !== $user->id) {
            return response()->json([
                'error' => 'unauthorized action',
            ], 403);
        }

        return response()->json([
            'survey' => $survey,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SurveyUpdateRequest $request, string $id)
    {
        // TODO: add image update here

        /** @var Survey $survey */
        $survey = Survey::with('questions')->find($id);
        $data = $request->validated();
        $questions = collect($data['questions'])->keyBy('id');
        unset($data['questions']);

        $survey->update($data);

        $existingIds = collect($survey->questions)->pluck('id');
        $newIds = $questions->pluck('id');

        $toDelete = $existingIds->diff($newIds);
        $toCreate = $newIds->diff($existingIds);

        SurveyQuestion::destroy($toDelete);

        /**
         * Create new questions, if there is any
         */
        foreach ($questions as $question) {

            if (in_array($question['id'], $toCreate->toArray())) {

                if (is_array($question['data'])) {
                    $question['data'] = json_encode($question['data']);
                }

                $question['survey_id'] = $survey->id;

                $validator = Validator::make($question, [
                    'type' => ['required', Rule::in(['textarea', 'radio', 'select', 'checkbox', 'text'])],
                    'question' => ['required'],
                    'description' => ['nullable'],
                    'data' => ['present'],
                    'survey_id' => [Rule::exists('surveys', 'id')],
                ]);

                SurveyQuestion::create($validator->validated());
            }
        }

        /**
         * Update existing questions
         * @var SurveyQuestion $question
         */
        foreach ($survey->questions as $question) {

            if ($existingIds->contains('id', $question->id)) {

                $question['survey_id'] = $survey->id;

                $validator = Validator::make($question, [
                    'type' => ['required', Rule::in(['textarea', 'radio', 'select', 'checkbox', 'text'])],
                    'question' => ['required'],
                    'description' => ['nullable'],
                    'data' => ['present'],
                ]);

                $question->update($validator->validated());
            }
        }

        return response()->json([
            'survey' => SurveyResource::make($survey),
            'questions' => $survey->questions,
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        // TODO: delete image from storage, when implemented above
        /**
         * @var Survey $survey
         */
        $survey = Survey::find($id);
        $user = $request->user();

        if ($survey->user_id !== $user->id) {
            return response()->json([
                'error' => 'unauthorized action',
            ], 403);
        }

        $survey->delete();
        return response()->noContent();
    }
}
