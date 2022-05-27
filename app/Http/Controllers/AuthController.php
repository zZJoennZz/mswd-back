<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

class AuthController extends Controller
{
    //admin signin function
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
            $isAdmin = auth()->user()['is_admin'];

            return response()->json(['token' => $token, 'isAdmin' => $isAdmin,], 200);
        } else {
            //return error
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
    
    //update user 
    public function update(Request $request) {
        $userId = $request->user()->id;
        $user = User::find($userId);

        if (!is_null($request->name)) {
            $user->name = $request->name;
        }

        if (!is_null($request->last_name)) {
            $user->last_name = $request->last_name;
        }

        if (!is_null($request->email)) {
            $user->email = $request->email;
        }

        if (!is_null($request->password)) {
            $user->password = bcrypt($request->password);
        }

        if ($user->save()) {
            return response()->json([
                "success" => true,
                "message" => "User changes successfully saved"
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "User changes NOT saved"
            ], 401);
        }
    }

    //update user 
    public function update_user(Request $request, $id) {
        $user = User::find($id);

        if (!is_null($request->name)) {
            $user->name = $request->name;
        }

        if (!is_null($request->last_name)) {
            $user->last_name = $request->last_name;
        }

        if (!is_null($request->email)) {
            $user->email = $request->email;
        }

        if (!is_null($request->password)) {
            $user->password = bcrypt($request->password);
        }

        if ($user->save()) {
            return response()->json([
                "success" => true,
                "message" => "User changes successfully saved"
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "User changes NOT saved"
            ], 401);
        }
    }

    //add new user function
    public function signup(Request $request) {
        //test if the data is validated and correct
        $this->validate($request, [
            'name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        //create new user login
        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_admin' => 1
        ]);

        //generate token for the user
        $token = $user->createToken(env("SEACREAT_KIEY"))->accessToken;
        //send the response
        return response()->json(['token' => $token], 200);
    }

    //register user function
    public function register(Request $request) {
        //test if the data is validated and correct
        $this->validate($request, [
            'name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        //create new user login
        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_admin' => 0
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
            if (auth()->user()['is_admin'] !== "1") return response()->json([
                "success" => false,
                "message" => "You have NO authorization here"
            ], 401);
            $userId = auth()->user();
            return response()->json([
                'success' => true,
                'message' => 'Token have access',
                'id' => $request->user()->id,
                'name' => auth()->user()['name'],
                'last_name' => auth()->user()['last_name']
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Your token have no access'
            ], 401);
        }
    }

    public function validateUserToken(Request $request) {
        $is_logged_in = auth()->guard('api')->check();
        if ($is_logged_in) {
            if (auth()->user()['is_admin'] !== "0") return response()->json([
                "success" => false,
                "message" => "You are NOT allowed here"
            ], 401);
            
            return response()->json([
                'success' => true,
                'message' => 'Token have access',
                'name' => auth()->user()['name'],
                'last_name' => auth()->user()['last_name'],
                'email' => auth()->user()['email']
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Your token have no access'
        ], 401);
    }

    //get user profile
    public function getProfile(Request $request) {
        $userId = $request->user()->id;
        $userName = $request->user()->name;
        $userLastName = $request->user()->last_name;
        $userEmail = $request->user()->email;
        return response()->json([
            'success' => true,
            'id' => $userId,
            'email' => $userEmail,
            'name' => $userName,
            'last_name' => $userLastName,
        ], 200);
    }

    //get all users
    public function get_all() {
        $users = User::get();

        if ($users) {
            return response()->json([
                "success" => true,
                "data" => $users
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Could NOT get users"
            ], 401);
        }
    }

    //get single user
    public function get_single($id) {
        $user = User::find($id);

        if ($user) {
            return response()->json([
                "success" => true,
                "data" => $user
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "User cannot be found"
            ], 401);
        }
    }
}
 