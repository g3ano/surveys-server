<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswerStoreRequest;
use App\Http\Requests\SurveyStoreRequest;
use App\Http\Requests\SurveyUpdateRequest;
use App\Http\Resources\SurveyQuestionResource;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyParticipant;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SurveyController extends Controller
{
    public $types = ['text', 'select', 'checkbox', 'radio'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $surveys = Survey::where('user_id', $user->id)->orderBy('updated_at', 'desc')->paginate(24);

        return response()->json([
            'surveys' => SurveyResource::collection($surveys),
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

        if (isset($data['image'])) {
            $relativePath = $this->saveImage($data['image']);
            $data['image'] = $relativePath;
        }

        $survey = Survey::create($data);

        // adding the questions

        $nQuestions = [];

        foreach ($questions as $question) {
            if (isset($question['data'])) {
                $question['data'] = json_encode($question['data']);
            }
            $question['survey_id'] = $survey->id;

            $validator = Validator::make($question, [
                'survey_id' => [Rule::exists('surveys', 'id')],
                'type' => ['bail','required', Rule::in($this->types)],
                'question' => ['bail','required', 'string', 'max:255'],
                'description' => ['bail','nullable', 'string', 'max:1000'],
                'data' => ['bail','nullable'],
            ]);

            $nQuestions[] = SurveyQuestion::create($validator->validated());
        }

        return response()->json([], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        /** @var Survey $survey */
        $survey = Survey::with('questions')->where('id', $id)->first();

        $user = $request->user();

        if ($survey->user_id !== $user->id) {
            return response()->json([
                'error' => 'unauthorized action',
            ], 403);
        }

        return response()->json([
            'survey' => SurveyResource::make($survey),
            'questions' => SurveyQuestionResource::collection($survey->questions),
        ]);
    }

    public function showBySlug(Request $request, string $slug)
    {
        /** @var Survey $survey */
        $survey = Survey::with('questions')->where('slug', $slug)->first();

        $user = $request->user();

        if ($survey->user_id !== $user->id) {
            return response()->json([
                'error' => 'unauthorized action',
            ], 403);
        }

        return response()->json([
            'survey' => SurveyResource::make($survey),
            'questions' => $survey->questions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SurveyUpdateRequest $request, string $id)
    {
        // TODO: add image update here


        /** @var Survey $survey */
        $survey = Survey::find($id);
        $user = $request->user();

        if ($survey->user_id !== $user->id) {
            return response()->json([
                'error' => 'unauthorized action',
            ], 403);
        }

        $data = $request->validated();

        $questions = $data['questions'];
        unset($data['questions']);

        $data['slug'] = Str::slug($data['title']);

        if (isset($data['image'])) {

            if (!preg_match('/^http:\/\/(\w+)/', $data['image'])) {
                $relativePath = $this->saveImage($data['image']);
                $data['image'] = $relativePath;

                if ($survey->image) {
                    File::delete(public_path($survey->image));
                }
            }
        }

        $survey->update($data);

        $existingIds = $survey->questions->pluck('id')->toArray();
        $newIds = collect($questions)->pluck('id')->toArray();

        $toCreate = array_diff($newIds, $existingIds);
        $toDelete = array_diff($existingIds, $newIds);

        SurveyQuestion::destroy($toDelete);

        foreach ($questions as $question) {
            if (in_array($question['id'], $toCreate)) {

                if (isset($question['data'])) {
                    $question['data'] = json_encode($question['data']);
                }

                $question['survey_id'] = $survey->id;

                $validator = Validator::make($question, [
                    'survey_id' => [Rule::exists('surveys', 'id')],
                    'type' => ['bail','required', Rule::in($this->types)],
                    'question' => ['bail','required', 'string', 'max:255'],
                    'description' => ['bail','nullable', 'string', 'max:1000'],
                    'data' => ['bail','nullable'],
                ]);

                SurveyQuestion::create($validator->validated());
            }
        }

        return response()->json([
            'status' => 'survey updated successfully',
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
        $survey = Survey::where('id', $id)->first();
        $user = $request->user();

        if ($survey->user_id !== $user->id) {
            return response()->json([
                'error' => 'unauthorized action',
            ], 403);
        }

        $survey->delete();
        return response()->noContent();
    }


    /**
     * Store the answers of a survey
     */
    public function storeAnswer(AnswerStoreRequest $request, string $slug)
    {

        $data = $request->validated();

        if (count($data) === 0) {
            return response()->json([
                'message' => 'No data is provided',
            ], 400);
        }

        /** @var Survey $survey */
        $survey = Survey::with('questions')->firstWhere('slug', $slug);

        $startDate = $data['startDate'];
        $data = $data['answers'];
        unset($data['startDate']);

        SurveyParticipant::create([
            'survey_id' => $survey->id,
            'start_date' => date('Y-m-d H:i:s', strtotime($startDate)),
            'end_date' => date('Y-m-d H:i:s'),
        ]);

        foreach ($data as $answer) {
            if (in_array($answer['questionId'], $survey->questions->pluck('id')->toArray())) {
                if (isset($answer['data'])) {
                    if (is_array($answer['data'])) {
                        $answer['data'] = json_encode($answer['data']);
                    }

                    SurveyAnswer::create([
                        'survey_id' => $survey->id,
                        'survey_question_id' => $answer['questionId'],
                        'answer' => $answer['data'],
                    ]);
                }
            }
        }

        return response()->json([], 201);
    }

    private function saveImage($image)
    {
        // Check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            // Take out the base64 encoded text without mime type
            $image = substr($image, strpos($image, ',') + 1);
            // Get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $dir = 'images/';
        $file = Str::random() . '.' . $type;
        $absolutePath = public_path($dir);
        $relativePath = $dir . $file;
        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }
        file_put_contents($relativePath, $image);

        return $relativePath;
    }


}
