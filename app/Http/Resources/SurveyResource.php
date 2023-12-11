<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'user_id' => $this->user_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'image' => $this->image,
            'status' => $this->status,
            'description' => $this->description,
            'expire_date' => $this->expire_date,
        ];
    }
}
