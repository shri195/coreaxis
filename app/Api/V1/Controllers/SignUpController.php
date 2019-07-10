<?php

namespace App\Api\V1\Controllers;

use Config;
use App\User;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\SignUpRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Ramsey\Uuid\Uuid;

class SignUpController extends Controller
{
    public function signUp(SignUpRequest $request, JWTAuth $JWTAuth)
    {
        $user = new User($request->all());
		// Generate a version 1 (time-based) UUID object
        $uuid1 = Uuid::uuid1();
        $uuidstr = $uuid1->toString() . "\n"; // i.e. e4eaaaf2-d142-11e1-b3e4-080027620cdd		
        $user->uuid = $uuidstr;
        $user->confirmation_code = md5(uniqid(mt_rand(), true));

        if (User::where('email', '=', $request['email'])->exists()) {
            // user found
            return response()->json([
                'status' => '409',
                'message' => 'User Already Exists'
            ], 409);
        }

        if (!$user->save()) {
            throw new HttpException(500);
        }

        if (!Config::get('boilerplate.sign_up.release_token')) {
            return response()->json([
                'status' => '201',
                'message' => 'Registered Successfully'
            ], 201);
        }

        $token = $JWTAuth->fromUser($user);
        return response()->json([
            'status' => '200',
            'message' => 'Registered Successfully',
            'token' => $token
        ], 201);

    }
}
