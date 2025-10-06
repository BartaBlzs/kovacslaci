<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::with('county');

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('county_id')) {
            $query->where('county_id', $request->county_id);
        }

        $perPage = $request->get('per_page', 15);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id',
        ]);

        $city = City::create($validated);
        $city->load('county');

        return response()->json($city, 201);
    }

    public function show(City $city)
    {
        $city->load('county', 'postalCodes');
        return response()->json($city);
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'county_id' => 'sometimes|required|exists:counties,id',
        ]);

        $city->update($validated);
        $city->load('county');

        return response()->json($city);
    }

    public function destroy(City $city)
    {
        $city->delete();
        return response()->json(['message' => 'City deleted successfully'], 200);
    }
}