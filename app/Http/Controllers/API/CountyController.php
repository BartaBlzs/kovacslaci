<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\County;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CountyController extends Controller
{
    /**
     * @api {get} /api/counties List all counties
     * @apiName GetCounties
     * @apiGroup County
     * @apiVersion 1.0.0
     * 
     * @apiQuery {String} [name] Filter by county name (partial match)
     * @apiQuery {Number{1-100}} [per_page=15] Items per page
     * 
     * @apiSuccess {Object[]} data List of counties
     * @apiSuccess {Number} data.id County ID
     * @apiSuccess {String} data.name County name
     * @apiSuccess {String} data.created_at Creation timestamp
     * @apiSuccess {String} data.updated_at Update timestamp
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": [
     *         {
     *           "id": 1,
     *           "name": "Budapest",
     *           "created_at": "2024-01-01T12:00:00.000000Z",
     *           "updated_at": "2024-01-01T12:00:00.000000Z"
     *         }
     *       ],
     *       "links": {...},
     *       "meta": {...}
     *     }
     */
    public function index(Request $request): JsonResponse
    {
        $query = County::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $perPage = $request->get('per_page', 15);
        $counties = $query->paginate($perPage);

        return response()->json($counties, 200);
    }

    /**
     * @api {post} /api/counties Create new county
     * @apiName CreateCounty
     * @apiGroup County
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiBody {String} name County name (unique, max 255 chars)
     * 
     * @apiSuccess {String} message Success message
     * @apiSuccess {Object} data County data
     * @apiSuccess {Number} data.id County ID
     * @apiSuccess {String} data.name County name
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "message": "County created successfully",
     *       "data": {
     *         "id": 1,
     *         "name": "Budapest"
     *       }
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     * @apiError (Error 422) ValidationError Invalid input data
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
     * @api {get} /api/counties/:id Get county details
     * @apiName GetCounty
     * @apiGroup County
     * @apiVersion 1.0.0
     * 
     * @apiParam {Number} id County ID
     * 
     * @apiSuccess {Object} data County data with cities
     * @apiSuccess {Number} data.id County ID
     * @apiSuccess {String} data.name County name
     * @apiSuccess {Object[]} data.cities List of cities in this county
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": {
     *         "id": 1,
     *         "name": "Budapest",
     *         "cities": [...]
     *       }
     *     }
     * 
     * @apiError (Error 404) NotFound County not found
     */
    public function show(County $county): JsonResponse
    {
        $county->load('cities');

        return response()->json([
            'data' => $county
        ], 200);
    }

    /**
     * @api {put} /api/counties/:id Update county
     * @apiName UpdateCounty
     * @apiGroup County
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiParam {Number} id County ID
     * 
     * @apiBody {String} [name] County name (unique, max 255 chars)
     * 
     * @apiSuccess {String} message Success message
     * @apiSuccess {Object} data Updated county data
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "message": "County updated successfully",
     *       "data": {
     *         "id": 1,
     *         "name": "Budapest"
     *       }
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     * @apiError (Error 404) NotFound County not found
     * @apiError (Error 422) ValidationError Invalid input data
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
     * @api {delete} /api/counties/:id Delete county
     * @apiName DeleteCounty
     * @apiGroup County
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiParam {Number} id County ID
     * 
     * @apiSuccess {String} message Success message
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "message": "County deleted successfully"
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     * @apiError (Error 404) NotFound County not found
     * @apiError (Error 400) BadRequest Cannot delete county with associated cities
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
     * @api {get} /api/counties/:id/stats Get county statistics
     * @apiName GetCountyStats
     * @apiGroup County
     * @apiVersion 1.0.0
     * 
     * @apiParam {Number} id County ID
     * 
     * @apiSuccess {Object} data Statistics data
     * @apiSuccess {String} data.county County name
     * @apiSuccess {Number} data.cities_count Number of cities
     * @apiSuccess {Number} data.postal_codes_count Number of postal codes
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": {
     *         "county": "Budapest",
     *         "cities_count": 23,
     *         "postal_codes_count": 215
     *       }
     *     }
     * 
     * @apiError (Error 404) NotFound County not found
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