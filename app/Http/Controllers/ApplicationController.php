<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class ApplicationController extends Controller
{
    //get all application records
    public function get_all() {
        //store all application records to $app variable
        $apps = Application::all();

        //send response to frontend
        try {
            return response()->json([
                "success" => true,
                "data" => $apps
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th
            ], 500);
        }
    }

    //get single record of application
    public function get_single($id) {
        //get a single application record using ID and store to $app variable
        $app = Application::find($id);

        //send response to frontend
        try {
            return response()->json([
                "success" => true,
                "data" => $app
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th
            ], 500);
        }
    }

    //post application
    public function post_application(Request $request) {
        //create ID to prevent overwritting
        $rand = substr(md5(microtime()),rand(0,26),3);
        $app_id =  "SR" . $rand . date("mdyyHis");

        //check if the application submitted files then send response
        if (!$request->hasFile('application_pic') || !$request->hasFile('application_sig')) {
            return response()->json([
                "success" => false,
                "message" => "No file attached"
            ], 400);
        }

        //create new record that will be saved to database
        $app = new Application;
        $app->application_data = $request->application_data;
        $app->application_id = $app_id;

        //list of allowed file extention
        $allowedFileExtension = ['jpg', 'jpeg', 'png'];

        //store the pic and sig to variable
        $application_pic = $request->file('application_pic');
        $application_sig = $request->file('application_sig');

        //check if both files' extension exist in the allowed file extension
        $check_pic = in_array($application_pic->getClientOriginalExtension(), $allowedFileExtension);
        $check_sig = in_array($application_sig->getClientOriginalExtension(), $allowedFileExtension);

        //check the results of the checking of file extension then send a response
        if (!$check_pic || !$check_sig) {
            return response()->json([
                "success" => false,
                "message" => "Invalid file format."
            ], 422);
        }

        //store the original name to variables
        $pic_name = $application_pic->getClientOriginalName();
        $sig_name = $application_sig->getClientOriginalName();

        //store the path to variables
        $pic_path = $application_pic->storeAs('public/d0czx/' . $app_id, $pic_name);
        $sig_path = $application_sig->storeAs('public/d0czx/' . $app_id, $sig_name);

        //save the records then send response and application ID for tracking
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

    //delete an application
    public function delete_application($id) {
        //find the application you wanted to delete
        $app = Application::find($id);

        //try to delete the record
        try {
            //if deleted, will send the response to frontend or if not, will send an error
            if ($app->delete()) {
                return response()->json([
                    "success" => true,
                    "message" => "Application record is deleted"
                ], 200);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Application record is NOT deleted"
                ], 500);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th
            ], 500);
        }
    }
}
