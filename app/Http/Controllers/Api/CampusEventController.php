<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexCampusEventRequest;
use App\Http\Requests\StoreCampusEventRequest;
use App\Http\Requests\UpdateCampusEventRequest;
use App\Http\Resources\CampusEventResource;
use App\Models\CampusEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

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
     * Store a newly created resource in storage.
     */
    public function store(StoreCampusEventRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['is_public'] = $request->boolean('is_public', true);

        $event = $request->user()->campusEvents()->create(Arr::except($validated, ['topics']));
        $event->topics()->sync($validated['topics'] ?? []);

        return (new CampusEventResource(
            $event->load(['user', 'venue', 'topics'])->loadCount('topics')
        ))->response()->setStatusCode(201);
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

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCampusEventRequest $request, CampusEvent $event): CampusEventResource
    {
        $validated = $request->validated();
        $validated['is_public'] = $request->boolean('is_public', $event->is_public);

        $event->update(Arr::except($validated, ['topics']));
        $event->topics()->sync($validated['topics'] ?? []);

        return new CampusEventResource(
            $event->load(['user', 'venue', 'topics'])->loadCount('topics')
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CampusEvent $event): Response
    {
        Gate::authorize('delete', $event);

        $event->delete();

        return response()->noContent();
    }
}
