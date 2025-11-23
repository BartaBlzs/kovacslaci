<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * @api {post} /api/register Register new user
     * @apiName Register
     * @apiGroup Auth
     * @apiVersion 1.0.0
     * 
     * @apiBody {String} name User name (max 255 chars)
     * @apiBody {String} email Email address (unique)
     * @apiBody {String} password Password (min 8 chars)
     * @apiBody {String} password_confirmation Password confirmation
     * 
     * @apiSuccess {String} message Success message
     * @apiSuccess {Object} user User data
     * @apiSuccess {String} token Access token
     * @apiSuccess {String} token_type Token type (Bearer)
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "message": "User registered successfully",
     *       "user": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john@example.com"
     *       },
     *       "token": "1|abcdef...",
     *       "token_type": "Bearer"
     *     }
     * 
     * @apiError (Error 422) ValidationError Invalid input data
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * @api {post} /api/login Login user
     * @apiName Login
     * @apiGroup Auth
     * @apiVersion 1.0.0
     * 
     * @apiBody {String} email Email address
     * @apiBody {String} password Password
     * 
     * @apiSuccess {String} message Success message
     * @apiSuccess {Object} user User data
     * @apiSuccess {String} token Access token
     * @apiSuccess {String} token_type Token type (Bearer)
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "message": "Login successful",
     *       "user": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john@example.com"
     *       },
     *       "token": "2|ghijkl...",
     *       "token_type": "Bearer"
     *     }
     * 
     * @apiError (Error 422) ValidationError Invalid credentials
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * @api {post} /api/logout Logout user
     * @apiName Logout
     * @apiGroup Auth
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiSuccess {String} message Success message
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "message": "Logged out successfully"
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * @api {get} /api/user Get current user
     * @apiName GetUser
     * @apiGroup Auth
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     * 
     * @apiHeader {String} Authorization Bearer token
     * 
     * @apiSuccess {Object} user Current user data
     * 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "user": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john@example.com"
     *       }
     *     }
     * 
     * @apiError (Error 401) Unauthorized Missing or invalid token
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ], 200);
    }
}