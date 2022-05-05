<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

class ClientController extends Controller
{
    //
    public function signin(Request $request) {

        //store the request payload to data array
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        //check if the login data is correct
        if (auth()->guard('client')->attempt($data)) {
            //generate token for the user then send it as the response
            $token = auth()->guard('client')->user()->createToken(env("SEACREAT_KIEY1"))->accessToken;
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
        $user = Client::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_admin' => 0,
        ]);

        //generate token for the user
        $token = $user->createToken(env("SEACREAT_KIEY1"))->accessToken;
        //send the response
        return response()->json(['token' => $token], 200);
    }

}
