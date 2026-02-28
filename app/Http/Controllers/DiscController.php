<?php

namespace App\Http\Controllers;

use App\Http\Resources\DiscResource;
use App\Models\Disc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DiscController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $discs = $request->user()
            ->discs()
            ->when($request->has('is_in_bag'), fn ($q) => $q->where('is_in_bag', $request->boolean('is_in_bag')))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->paginate($request->integer('per_page', 15));

        return DiscResource::collection($discs);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Disc::class);

        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'type' => 'required|in:driver,fairway_driver,mid_range,putter',
            'weight_grams' => 'nullable|numeric|min:100|max:200',
            'color' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_in_bag' => 'boolean',
        ]);

        $disc = $request->user()->discs()->create($validated);

        return (new DiscResource($disc))->response()->setStatusCode(201);
    }

    public function show(Disc $disc): DiscResource
    {
        $this->authorize('view', $disc);

        return new DiscResource($disc);
    }

    public function update(Request $request, Disc $disc): DiscResource
    {
        $this->authorize('update', $disc);

        $validated = $request->validate([
            'brand' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:driver,fairway_driver,mid_range,putter',
            'weight_grams' => 'nullable|numeric|min:100|max:200',
            'color' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_in_bag' => 'boolean',
        ]);

        $disc->update($validated);

        return new DiscResource($disc);
    }

    public function destroy(Disc $disc): Response
    {
        $this->authorize('delete', $disc);

        $disc->delete();

        return response()->noContent();
    }
}
