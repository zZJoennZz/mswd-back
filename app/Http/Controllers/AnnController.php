<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Announcement;

class AnnController extends Controller
{
    //
    public function get_all() {
        $ann = Announcement::all();
        
        if ($ann) {
            return response()->json([
                "success" => true,
                "data" => $ann,
                // "image_path" => url("storage/ann-img/" . $ann->image_path)
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Announcements could NOT be fetched"
            ]);
        }
    }

    public function get_single($id) {
        $ann = Announcement::find($id);

        if ($ann) {
            return response()->json([
                "success" => true,
                "data" => $ann
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Announcement could NOT be fetched"
            ]);
        }
    }

    public function post_ann(Request $request) {
        if (!$request->hasFile('announcement_img')) {
            return response()->json([
                "success" => false,
                "message" => "No file attached"
            ], 400);
        }

        $ann = new Announcement;
        $ann->announcement_title = $request->announcement_title;
        $ann->announcement_body = $request->announcement_body;

        $allowedFileExtension = ['png', 'jpg', 'jpeg', 'gif'];

        $featuredImg = $request->file('announcement_img');

        $extension = $featuredImg->getClientOriginalExtension();
        $check = in_array($extension, $allowedFileExtension);
        $path;

        if ($check) {
            $img_id =  uniqid("ann", false) . date("mmddyyyHis");
            $name = $featuredImg->getClientOriginalName();
            $path = $featuredImg->storeAs('public/ann-img', $img_id . $name);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Invalid file format"
            ], 422);
        }

        $ann->image_path = $img_id . $name;

        if ($ann->save()) {
            return response()->json([
                "success" => true,
                "message" => "Announcement successfully posted"
            ], 200);
        } else {
            return response()->json([
                "success" => true,
                "message" => "Announcement is NOT posted"
            ], 500);
        }
    }

    public function put_ann(Request $request, $id) {
        
        $ann = Announcement::find($id);
        $path;
        $name;
        $img_id = uniqid("ann", false) . date("mmddyyyHis");
        if ($request->hasFile('announcement_img')) {
            $allowedFileExtension = ['png', 'jpg', 'jpeg', 'gif'];

            $featuredImg = $request->file('announcement_img');

            $extension = $featuredImg->getClientOriginalExtension();
            $check = in_array($extension, $allowedFileExtension);
            
            if ($check) {
                $name = $featuredImg->getClientOriginalName();
                $path = $featuredImg->storeAs('public/ann-img', $img_id . $name);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid file format"
                ], 422);
            }

            $ann->image_path = $img_id . $name;
        }

        echo "It's" . is_null($request->announcement_title);

        if (!is_null($request->announcement_title)) {
            $ann->announcement_title = $request->announcement_title;
        }
        
        if (!is_null($request->announcement_body)) {
            $ann->announcement_body = $request->announcement_body;
        }


        if ($ann->save()) {
            return response()->json([
                "success" => true,
                "message" => "Announcement successfully changed"
            ], 200);
        } else {
            return response()->json([
                "success" => true,
                "message" => "Announcement is NOT changed"
            ], 500);
        }
    }
}
