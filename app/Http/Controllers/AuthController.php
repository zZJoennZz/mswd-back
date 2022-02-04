<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

class AuthController extends Controller
{
    //
    public function signin(Request $request) {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken(env("SEACREAT_KIEY"))->accessToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function signup(Request $request) {
        $this->validate($request, [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken(env("SEACREAT_KIEY"))->accessToken;

        return response()->json(['token' => $token], 200);
    }

    public function signout(Request $request) {
        $tokenRepository = app(TokenRepository::class);
        $is_logged_in = auth()->guard('api')->check();
        $token = auth()->user()->token();    
        
        if ($is_logged_in) {
            $request->user()->token()->revoke();
            return response()->json([
                'success' => true,
                'message' => 'Token access revoked',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed'
            ], 401);
        }
    }

    public function validateToken(Request $request) {
        $is_logged_in = auth()->guard('api')->check();
        //dd($request->header());
        if ($is_logged_in) {
            return response()->json([
                'success' => true,
                'message' => 'Token have access',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Your token have no access'
            ], 401);
        }
    }
}
 