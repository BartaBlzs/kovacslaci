<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\County;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CountyController extends Controller
{
    /**
     * Összes megye listázása (lapozással és szűréssel)
     * 
     * GET /api/counties
     * Query paraméterek: name, per_page
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = County::query();

        // Szűrés név alapján
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Lapozás
        $perPage = $request->get('per_page', 15);
        $counties = $query->paginate($perPage);

        return response()->json($counties, 200);
    }

    /**
     * Új megye létrehozása
     * 
     * POST /api/counties
     * Body: { "name": "Megye név" }
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties,name',
        ]);

        $county = County::create($validated);

        return response()->json([
            'message' => 'County created successfully',
            'data' => $county
        ], 201);
    }

    /**
     * Egy megye megtekintése (településekkel együtt)
     * 
     * GET /api/counties/{id}
     * 
     * @param County $county
     * @return JsonResponse
     */
    public function show(County $county): JsonResponse
    {
        // Betöltjük a kapcsolódó településeket
        $county->load('cities');

        return response()->json([
            'data' => $county
        ], 200);
    }

    /**
     * Megye adatainak módosítása
     * 
     * PUT /api/counties/{id}
     * Body: { "name": "Új megye név" }
     * 
     * @param Request $request
     * @param County $county
     * @return JsonResponse
     */
    public function update(Request $request, County $county): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:counties,name,' . $county->id,
        ]);

        $county->update($validated);

        return response()->json([
            'message' => 'County updated successfully',
            'data' => $county
        ], 200);
    }

    /**
     * Megye törlése
     * 
     * DELETE /api/counties/{id}
     * 
     * @param County $county
     * @return JsonResponse
     */
    public function destroy(County $county): JsonResponse
    {
        try {
            $county->delete();

            return response()->json([
                'message' => 'County deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Cannot delete county. It may have associated cities.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Megye statisztikák lekérése
     * 
     * GET /api/counties/{id}/stats
     * 
     * @param County $county
     * @return JsonResponse
     */
    public function stats(County $county): JsonResponse
    {
        $stats = [
            'county' => $county->name,
            'cities_count' => $county->cities()->count(),
            'postal_codes_count' => $county->postalCodes()->count(),
        ];

        return response()->json([
            'data' => $stats
        ], 200);
    }
}