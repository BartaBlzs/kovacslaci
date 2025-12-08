<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CityController extends Controller
{
    /**
     * @api {get} /api/cities List all cities
     * @apiName GetCities
     * @apiGroup City
     * @apiVersion 1.0.0
     * 
     * @apiQuery {String} [name] Filter by city name (partial match)
     * @apiQuery {Number} [county_id] Filter by county ID
     * @apiQuery {Number{1-100}} [per_page=15] Items per page
     * 
     * @apiSuccess {Object[]} data List of cities
     * @apiSuccess {Number} data.id City ID
     * @apiSuccess {String} data.name City name
     * @apiSuccess {Number} data.county_id County ID
     * @apiSuccess {Object} data.county County details
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": [
     *         {
     *           "id": 1,
     *           "name": "Budapest",
     *           "county_id": 1,
     *           "county": {
     *             "id": 1,
     *             "name": "Budapest"
     *           }
     *         }
     *       ]
     *     }
     */
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

    /**
     * @api {get} /api/cities/initial-letters/:countyId Get initial letters
     * @apiName GetInitialLetters
     * @apiGroup City
     * @apiVersion 1.0.0
     * 
     * @apiParam {Number} countyId County ID
     * 
     * @apiSuccess {String[]} data Array of initial letters
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     ["A", "B", "C", "D"]
     * 
     * @apiDescription Get unique initial letters of cities in a county
     */
    public function getInitialLetters($countyId)
    {
        $letters = City::where('county_id', $countyId)
            ->select(DB::raw('DISTINCT UPPER(SUBSTRING(name, 1, 1)) as letter'))
            ->orderBy('letter')
            ->pluck('letter');

        return response()->json($letters);
    }

    /**
     * @api {get} /api/cities/filter/:countyId/:letter Filter cities by letter
     * @apiName FilterCitiesByLetter
     * @apiGroup City
     * @apiVersion 1.0.0
     * 
     * @apiParam {Number} countyId County ID
     * @apiParam {String} letter Initial letter (A-Z)
     * 
     * @apiSuccess {Object[]} data List of filtered cities with postal codes
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *       {
     *         "id": 1,
     *         "name": "Budapest",
     *         "county": {...},
     *         "postal_codes": [...]
     *       }
     *     ]
     * 
     * @apiDescription Get cities starting with specific letter in a county
     */
    public function filterByCountyAndLetter($countyId, $letter)
    {
        $cities = City::with(['county', 'postalCodes'])
            ->where('county_id', $countyId)
            ->where('name', 'like', $letter . '%')
            ->orderBy('name')
            ->get();

        return response()->json($cities);
    }

    /**
     * @api {post} /api/cities Create new city
     * @apiName CreateCity
     * @apiGroup City
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiBody {String} name City name (max 255 chars)
     * @apiBody {Number} county_id Existing county ID
     * @apiBody {String[]} [postal_codes] Array of 4-digit postal codes
     * 
     * @apiSuccess {Number} id City ID
     * @apiSuccess {String} name City name
     * @apiSuccess {Object} county County details
     * @apiSuccess {Object[]} postal_codes Postal codes
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "id": 1,
     *       "name": "Budapest",
     *       "county": {...},
     *       "postal_codes": [...]
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     * @apiError (Error 422) ValidationError Invalid input data
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id',
            'postal_codes' => 'array',
            'postal_codes.*' => 'string|size:4'
        ]);

        $city = City::create([
            'name' => $validated['name'],
            'county_id' => $validated['county_id']
        ]);

        if (isset($validated['postal_codes'])) {
            foreach ($validated['postal_codes'] as $code) {
                $city->postalCodes()->create(['code' => $code]);
            }
        }

        $city->load('county', 'postalCodes');

        return response()->json($city, 201);
    }

    /**
     * @api {get} /api/cities/:id Get city details
     * @apiName GetCity
     * @apiGroup City
     * @apiVersion 1.0.0
     * 
     * @apiParam {Number} id City ID
     * 
     * @apiSuccess {Number} id City ID
     * @apiSuccess {String} name City name
     * @apiSuccess {Object} county County details
     * @apiSuccess {Object[]} postal_codes Postal codes
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "name": "Budapest",
     *       "county": {...},
     *       "postal_codes": [...]
     *     }
     * 
     * @apiError (Error 404) NotFound City not found
     */
    public function show(City $city)
    {
        $city->load('county', 'postalCodes');
        return response()->json($city);
    }

    /**
     * @api {put} /api/cities/:id Update city
     * @apiName UpdateCity
     * @apiGroup City
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiParam {Number} id City ID
     * 
     * @apiBody {String} [name] City name (max 255 chars)
     * @apiBody {Number} [county_id] Existing county ID
     * 
     * @apiSuccess {Number} id City ID
     * @apiSuccess {String} name City name
     * @apiSuccess {Object} county County details
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "name": "Budapest",
     *       "county": {...}
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     * @apiError (Error 404) NotFound City not found
     * @apiError (Error 422) ValidationError Invalid input data
     */
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

    /**
     * @api {delete} /api/cities/:id Delete city
     * @apiName DeleteCity
     * @apiGroup City
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiParam {Number} id City ID
     * 
     * @apiSuccess {String} message Success message
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "message": "City deleted successfully"
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     * @apiError (Error 404) NotFound City not found
     */
    public function destroy(City $city)
    {
        $city->delete();
        return response()->json(['message' => 'City deleted successfully'], 200);
    }

    /**
     * Kezdőbetűk lekérése megyénként
     */
    public function getStartingLetters(Request $request)
    {
        $countyId = $request->get('county_id');
        
        if (!$countyId) {
            return response()->json([
                'success' => false,
                'message' => 'county_id parameter is required'
            ], 400);
        }
        
        $letters = City::where('county_id', $countyId)
            ->selectRaw('UPPER(SUBSTRING(name, 1, 1)) as letter')
            ->distinct()
            ->orderBy('letter')
            ->pluck('letter')
            ->values();
        
        return response()->json($letters, 200);
    }
}