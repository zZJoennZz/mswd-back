<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class ApplicationController extends Controller
{
    //
    public function get_all() {
        $apps = Application::all();

        try {

            return response()->json([
                "success" => true,
                "data" => $apps
            ], 200);

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function get_single($id) {
        $app = Application::find($id);

        try {

            return response()->json([
                "success" => true,
                "data" => $app
            ], 200);

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function post_application(Request $request) {
        $rand = substr(md5(microtime()),rand(0,26),5);
        $app_id =  "SR" . $rand . date("mdyyHis");

        if (!$request->hasFile('application_pic') || !$request->hasFile('application_sig')) {
            return response()->json([
                "success" => false,
                "message" => "No file attached"
            ], 400);
        }

        $app = new Application;
        $app->application_data = $request->application_data;
        $app->application_id = $app_id;

        $allowedFileExtension = ['jpg', 'jpeg', 'png'];

        $application_pic = $request->file('application_pic');
        $application_sig = $request->file('application_sig');

        $check_pic = in_array($application_pic->getClientOriginalExtension(), $allowedFileExtension);
        $check_sig = in_array($application_sig->getClientOriginalExtension(), $allowedFileExtension);

        if (!$check_pic || !$check_sig) {
            return response()->json([
                "success" => false,
                "message" => "Invalid file format."
            ], 422);
        }

        $pic_name = $application_pic->getClientOriginalName();
        $sig_name = $application_sig->getClientOriginalName();

        $pic_path = $application_pic->storeAs('public/d0czx/' . $app_id, $pic_name);
        $sig_path = $application_sig->storeAs('public/d0czx/' . $app_id, $sig_name);

        if ($app->save()) {
            return response()->json([
                "success" => true,
                "message" => "Application success",
                "application_id" => $app_id 
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Application is NOT succes"
            ], 500);
        }
    }
}
