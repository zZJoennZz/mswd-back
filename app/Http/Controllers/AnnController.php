<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Announcement;

class AnnController extends Controller
{
    //function to get all announcements from the database and sends out as json object to the frontend website
    public function get_all()
    {

        //get all the announcements from the database  and store $ann
        $ann = Announcement::all();

        //sending out the announcements as json object to the frontend website
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

    //function to get a single announcement by using the ID
    public function get_single($id)
    {
        //gets the ID from the route path then store the single announcement record to $ann
        $ann = Announcement::find($id);

        //send out the announcement record as json object to frontend website
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

    //for application form to be posted
    public function post_ann(Request $request)
    {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $res;
        //check if the request payload have a file attached and send response if none
        if (!$request->hasFile('announcement_img')) {
            return response()->json([
                "success" => false,
                "message" => "No file attached"
            ], 400);
        }

        //create new record through the Announcement model
        $ann = new Announcement;
        $ann->announcement_title = $request->announcement_title;
        $ann->announcement_body = $request->announcement_body;

        //array of allowed file extensions
        $allowedFileExtension = ['png', 'jpg', 'jpeg', 'gif', 'PNG'];

        //store the announcement featured image file to a variable
        $featuredImg = $request->file('announcement_img');

        //get the stored file's file extension
        $extension = $featuredImg->getClientOriginalExtension();
        //check if the file's extension is in the array of allowed extension
        $check = in_array($extension, $allowedFileExtension);
        //initiate the path variable
        $path = "";

        //check if the check variable is true or false about the file's extension
        if ($check) {
            //generate unique ID for saving and to avoid overwritting an image
            $img_id =  uniqid("ann", false) . date("mmddyyyHis");
            //get the file original name
            $name = $featuredImg->getClientOriginalName();
            //save the file to ImgBB through their API
            // $path = $featuredImg->storeAs('public/ann-img', $img_id . $name);
            $contents = file_get_contents($request->file('announcement_img')->getRealPath());

            $image_base64 = base64_encode($contents);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key=d1f0d556dc4121b9db63e20830ba67f7');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            $imgToUpload = array('image' => $image_base64, 'name' => $name . $img_id);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $imgToUpload);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return response()->json([
                    "success" => false,
                    "message" => curl_error($ch)
                ], 400);
            }
            curl_close($ch);
            $res = json_decode($result);
        } else {
            //send invalid file format response if the check variable contains false
            return response()->json([
                "success" => false,
                "message" => "Invalid file format"
            ], 422);
        }

        //add the image file name to table field

        $ann->image_path = $res->data->url;

        //save the record to the database and send a success or fail message as response to frontend website
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

    //updating a announcement post
    public function put_ann(Request $request, $id)
    {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $res;
        //find the announcement you were going to edit using the id from route path
        $ann = Announcement::find($id);
        //initiate path and name variable
        $path;
        $name;
        //create a unique id for files again like the posting
        $img_id = uniqid("ann", false) . date("mmddyyyHis");
        //check if the request payload have a file
        if ($request->hasFile('announcement_img')) {
            //allowed file extension array
            $allowedFileExtension = ['png', 'jpg', 'jpeg', 'gif'];
            //store the attached file to featuredImg
            $featuredImg = $request->file('announcement_img');
            //get the extension of the attached file
            $extension = $featuredImg->getClientOriginalExtension();
            //check if true or felse if extension is in the allowed file extension
            $check = in_array($extension, $allowedFileExtension);
            //check if the check variable is true or false then send the appropriate response
            if ($check) {
                $name = $featuredImg->getClientOriginalName();
                //$path = $featuredImg->storeAs('public/ann-img', $img_id . $name);
                $contents = file_get_contents($request->file('announcement_img')->getRealPath());

                $image_base64 = base64_encode($contents);
                //dd($image_base64);
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key=d1f0d556dc4121b9db63e20830ba67f7');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                $imgToUpload = array('image' => $image_base64, 'name' => $name . $img_id);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $imgToUpload);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    return response()->json([
                        "success" => false,
                        "message" => curl_error($ch)
                    ], 400);
                }
                curl_close($ch);
                $res = json_decode($result);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid file format"
                ], 422);
            }
            //save the image file name to the field
            $ann->image_path = $res->data->url;
        }

        //echo "It's" . is_null($request->announcement_title);

        //check if the announcement title and announcement body is empty or not
        if (!is_null($request->announcement_title)) {
            $ann->announcement_title = $request->announcement_title;
        }

        if (!is_null($request->announcement_body)) {
            $ann->announcement_body = $request->announcement_body;
        }

        //save the data to database and send response
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

    //delete function for announcements
    public function delete_ann($id)
    {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        //find the announcement you wanted to delete
        $ann = Announcement::find($id);

        //check if the record is existing
        if (!$ann) {
            return response()->json([
                "success" => false,
                "message" => "Announcement could NOT be found"
            ], 404);
        }

        //try to delete the announcement
        try {
            //deletes the announcement
            if ($ann->delete()) {
                return response()->json([
                    "success" => true,
                    "message" => "Announcement successfully deleted"
                ], 200);
            } else {
                //not deleted for some reason
                return response()->json([
                    "success" => false,
                    "message" => "Announcement could NOT be deleted"
                ], 500);
            }
        } catch (\Throwable $th) {
            //something went wrong that wasn't handled
            return response()->json([
                "success" => false,
                "message" => "Something went wrong. " . $th
            ], 500);
        }
    }
}
