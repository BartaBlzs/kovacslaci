<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PostalCode;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = PostalCode::with(['city.county']);

        // Keresési szűrők
        if ($request->has('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        if ($request->has('city')) {
            $query->whereHas('city', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->city . '%');
            });
        }

        if ($request->has('county')) {
            $query->whereHas('city.county', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->county . '%');
            });
        }

        $perPage = $request->get('per_page', 15);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:4',
            'city_id' => 'required|exists:cities,id',
        ]);

        $postalCode = PostalCode::create($validated);
        $postalCode->load('city.county');

        return response()->json($postalCode, 201);
    }

    public function show(PostalCode $postalCode)
    {
        $postalCode->load('city.county');
        return response()->json($postalCode);
    }

    public function update(Request $request, PostalCode $postalCode)
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|size:4',
            'city_id' => 'sometimes|required|exists:cities,id',
        ]);

        $postalCode->update($validated);
        $postalCode->load('city.county');

        return response()->json($postalCode);
    }

    public function destroy(PostalCode $postalCode)
    {
        $postalCode->delete();
        return response()->json(['message' => 'Postal code deleted successfully'], 200);
    }
}