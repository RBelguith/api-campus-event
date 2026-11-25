<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexCampusEventRequest;
use App\Http\Resources\CampusEventResource;
use App\Models\CampusEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampusEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexCampusEventRequest $request): AnonymousResourceCollection
    {
        $perPage = (int) ($request->validated('per_page') ?? 5);

        $events = CampusEvent::query()
            ->with(['user', 'venue', 'topics'])
            ->withCount('topics')
            ->when(
                $request->validated('venue'),
                fn (Builder $query, string $slug) => $query->whereRelation('venue', 'slug', $slug)
            )
            ->latest('starts_at')
            ->paginate($perPage)
            ->withQueryString();

        return CampusEventResource::collection($events);
    }

    /**
     * Display the specified resource.
     */
    public function show(CampusEvent $event): CampusEventResource
    {
        return new CampusEventResource(
            $event->load(['user', 'venue', 'topics'])->loadCount('topics')
        );
    }
}
