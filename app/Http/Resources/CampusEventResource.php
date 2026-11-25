<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampusEventResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'capacity' => $this->capacity,
            'is_public' => $this->is_public,
            'organizer' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'venue' => $this->whenLoaded('venue', fn () => new VenueResource($this->venue)),
            'topics' => $this->whenLoaded('topics', fn () => TopicResource::collection($this->topics)),
            'topics_count' => $this->whenCounted('topics'),
        ];
    }
}
