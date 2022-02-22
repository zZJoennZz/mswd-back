<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

class AuthController extends Controller
{
    //sign in function
    public function signin(Request $request) {

        //store the request payload to data array
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        //check if the login data is correct
        if (auth()->attempt($data)) {
            //generate token for the user then send it as the response
            $token = auth()->user()->createToken(env("SEACREAT_KIEY"))->accessToken;
            return response()->json(['token' => $token], 200);
        } else {
            //return error
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    //register function
    public function signup(Request $request) {
        //test if the data is validated and correct
        $this->validate($request, [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        //create new user login
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        //generate token for the user
        $token = $user->createToken(env("SEACREAT_KIEY"))->accessToken;
        //send the response
        return response()->json(['token' => $token], 200);
    }

    //signout the user
    public function signout(Request $request) {
        //get the tokens
        $tokenRepository = app(TokenRepository::class);
        //check the status of the user if logged in or not
        $is_logged_in = auth()->guard('api')->check();
        //get token of the user
        $token = auth()->user()->token();    
        
        //check if the user is logged in and revoke the token login access
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

    //checking if the token have access to api
    //this is used everytime we load an admin page
    public function validateToken(Request $request) {
        //check the token
        $is_logged_in = auth()->guard('api')->check();
        //dd($request->header());
        //check if the token is valid
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
 