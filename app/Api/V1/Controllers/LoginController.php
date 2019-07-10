<?php

namespace App\Api\V1\Controllers;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Auth;
use App\User;

class LoginController extends Controller
{
    /**
     * Log the user in
     *
     * @param LoginRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        try {
            $credentials = $request->only(['email', 'password']);
            $token = Auth::guard()->attempt($credentials);

            if (!$token) {
                if (User::where('email', '=', $request['email'])->count() > 0) {
                    return response()->json([
                        'status' => '403',
                        'message' => 'Wrong Password Entered.'
                    ], 403);
                } else {
                    return response()->json([
                        'status' => '403',
                        'message' => 'User not exists'
                    ], 403);
                }
               // throw new HttpException(403);
            }

        } catch (JWTException $e) {
            return response()->json([
                'status' => '500',
                'message' => 'Internal server error'
            ], 500);
           // throw new HttpException(500);
        }

        return response()
            ->json([
                'status' => '200',
                'token' => $token,
                'expires_in' => Auth::guard()->factory()->getTTL() * 60
            ]);
    }
}
