<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class SurveyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'image' => $this->image ? URL::to($this->image) : '',
            'description' => $this->description ? $this->description : '',
            'create_date' => date('Y-m-d', strtotime($this->created_at)),
            'updated_date' => date('Y-m-d', strtotime($this->updated_at)),
            'expire_date' => date('Y-m-d', strtotime($this->expire_date)),
            'numberOfQuestions' => count($this->questions),
            'numberOfAnswers' => count($this->answers),
            'numberOfParticipants' => count($this->participants),
        ];
    }
}
