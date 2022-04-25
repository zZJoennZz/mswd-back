<?php

namespace App\Http\Controllers;

use App\Mail\AppNotif;
use Illuminate\Http\Request;
use App\Models\ServAppli;
use Illuminate\Support\Facade\Mail;

class ServAppliController extends Controller
{
    //
    public function index() {
        return response()->json([
            "message" => "invalid"
        ], 500);
    }

    public function get_all() {
        $servapplis = ServAppli::all();

        if(!$servapplis) {
            return response()->json([
                "success" => false,
                "message" => "Applications are not available"
            ], 400);
        } else {
            return response()->json([
                "success" => true,
                "data" => $servapplis
            ], 200);
        }
    }

    public function get_single($id) {
        $servapplis = ServApply::find($id);
        
        if(!$servapplis) {
            return response()->json([
                "success" => false,
                "message" => "Application is not available"
            ], 400);
        } else {
            return response()->json([
                "success" => true,
                "data" => $servapplis
            ], 200);
        }
    }

    public function submit_appli(Request $request) {

        if (!$request->hasFile('file')) {
            return response()->json([
                "success" => false,
                "message" => "No file attached"
            ], 400);
        }

        // $request->validate([
        //     'file' => 'required|mimes:pdf|max:4076'
        // ]);
        $app_id =  uniqid("SRB", false) . date("mmddyyyHis");
        $appli = new ServAppli;
        $appli->service_id = $request->service_id;
        $appli->application_id = $app_id;
        $appli->first_name = $request->first_name;
        $appli->middle_name = $request->middle_name;
        $appli->last_name = $request->last_name;
        $appli->birthday = $request->birthday;
        $appli->gender = $request->gender;
        $appli->email_address = $request->email_address;
        $appli->contact_number = $request->contact_number;

        $allowedFileExtension = ['pdf'];
        $files = $request->file('file');

        $is_saved;
        if ($appli->save()) {
            $is_saved = true;
        } else {
            $is_saved = false;
        }

        foreach ($files as $file) {

            $extension = $file->getClientOriginalExtension();

            $check = in_array($extension, $allowedFileExtension);
            
            if ($check) {
                foreach ($request->file as $mediaFile) {
                    $name = $mediaFile->getClientOriginalName();
                    $path = $mediaFile->storeAs('public/d0czx/' . trim($request->last_name . $request->birthday . $request->first_name . $request->last_name) . $appli->id, $name);
                }
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid file format"
                ], 422);
            }
            
        }

        if ($is_saved) {
            Mail::to($request->email_address)->send(new AppNotif($request->email_address));
            return response()->json([
                "success" => true,
                "message" => "Application successfully submitted",
                "application_id" => $app_id
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Application is NOT submitted"
            ], 500);
        }

    }

}
