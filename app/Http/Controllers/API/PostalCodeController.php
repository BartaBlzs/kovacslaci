<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PostalCode;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    /**
     * @api {get} /api/postal-codes List all postal codes
     * @apiName GetPostalCodes
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * 
     * @apiQuery {String} [code] Filter by postal code (partial match)
     * @apiQuery {String} [city] Filter by city name (partial match)
     * @apiQuery {String} [county] Filter by county name (partial match)
     * @apiQuery {Number{1-100}} [per_page=15] Items per page
     * 
     * @apiSuccess {Object[]} data List of postal codes
     * @apiSuccess {Number} data.id Postal code ID
     * @apiSuccess {String} data.code 4-digit postal code
     * @apiSuccess {Number} data.city_id City ID
     * @apiSuccess {Object} data.city City details
     * @apiSuccess {String} data.city.name City name
     * @apiSuccess {Object} data.city.county County details
     * @apiSuccess {String} data.city.county.name County name
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": [
     *         {
     *           "id": 1,
     *           "code": "1011",
     *           "city_id": 1,
     *           "city": {
     *             "id": 1,
     *             "name": "Budapest",
     *             "county": {
     *               "id": 1,
     *               "name": "Budapest"
     *             }
     *           }
     *         }
     *       ],
     *       "links": {...},
     *       "meta": {...}
     *     }
     */
    public function index(Request $request)
    {
        $query = PostalCode::with(['city.county']);

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

    /**
     * @api {post} /api/postal-codes Create new postal code
     * @apiName CreatePostalCode
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiBody {String{4}} code 4-digit postal code
     * @apiBody {Number} city_id Existing city ID
     * 
     * @apiSuccess {Number} id Postal code ID
     * @apiSuccess {String} code 4-digit postal code
     * @apiSuccess {Number} city_id City ID
     * @apiSuccess {Object} city City details with county
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "id": 1,
     *       "code": "1011",
     *       "city_id": 1,
     *       "city": {
     *         "id": 1,
     *         "name": "Budapest",
     *         "county": {
     *           "id": 1,
     *           "name": "Budapest"
     *         }
     *       }
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     * @apiError (Error 422) ValidationError Invalid input data
     */
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

    /**
     * @api {get} /api/postal-codes/:id Get postal code details
     * @apiName GetPostalCode
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * 
     * @apiParam {Number} id Postal code ID
     * 
     * @apiSuccess {Number} id Postal code ID
     * @apiSuccess {String} code 4-digit postal code
     * @apiSuccess {Number} city_id City ID
     * @apiSuccess {Object} city City details with county
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "code": "1011",
     *       "city_id": 1,
     *       "city": {
     *         "id": 1,
     *         "name": "Budapest",
     *         "county": {
     *           "id": 1,
     *           "name": "Budapest"
     *         }
     *       }
     *     }
     * 
     * @apiError (Error 404) NotFound Postal code not found
     */
    public function show(PostalCode $postalCode)
    {
        $postalCode->load('city.county');
        return response()->json($postalCode);
    }

    /**
     * @api {put} /api/postal-codes/:id Update postal code
     * @apiName UpdatePostalCode
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiParam {Number} id Postal code ID
     * 
     * @apiBody {String{4}} [code] 4-digit postal code
     * @apiBody {Number} [city_id] Existing city ID
     * 
     * @apiSuccess {Number} id Postal code ID
     * @apiSuccess {String} code Updated postal code
     * @apiSuccess {Number} city_id City ID
     * @apiSuccess {Object} city City details with county
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "code": "1012",
     *       "city_id": 1,
     *       "city": {...}
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     * @apiError (Error 404) NotFound Postal code not found
     * @apiError (Error 422) ValidationError Invalid input data
     */
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

    /**
     * @api {delete} /api/postal-codes/:id Delete postal code
     * @apiName DeletePostalCode
     * @apiGroup PostalCode
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiParam {Number} id Postal code ID
     * 
     * @apiSuccess {String} message Success message
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "message": "Postal code deleted successfully"
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     * @apiError (Error 404) NotFound Postal code not found
     */
    public function destroy(PostalCode $postalCode)
    {
        $postalCode->delete();
        return response()->json(['message' => 'Postal code deleted successfully'], 200);
    }
}