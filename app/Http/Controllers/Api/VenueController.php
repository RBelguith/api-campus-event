<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VenueResource;
use App\Models\Venue;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VenueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return VenueResource::collection(
            Venue::withCount('campusEvents')->orderBy('name')->get()
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Venue $venue): VenueResource
    {
        return new VenueResource($venue->loadCount('campusEvents'));
    }
}
