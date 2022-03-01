<?php

namespace App\Http\Controllers;
// require_once(base_path('vendor') . '\pcloud\pcloud-php-sdk\lib\pCloud\autoload.php');
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\ApplicationFiles;
use pCloud;
use Mail;

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
        //put credentials for the cloud server
        $access_token = 'gxRm7Z2sQ7W52IlDfZzhSai7ZodY01KQSM8XsQraR76f8109rKDiy';
        $locationid = 1;
        $folder_id = 12362338034;
        
        //get a single application record using ID and store to $app variable
        $app = Application::find($id);
        $app_files = ApplicationFiles::where('app_id', $id)->get();
        $pCloudApp = new pCloud\App();
        $pCloudApp->setAccessToken($access_token);
        $pCloudApp->setLocationId($locationid);

        $pcloudFolder = new pCloud\Folder($pCloudApp);
        $pcloudFile = new pCloud\File($pCloudApp);

        $appFiles = [];
        foreach($app_files as $files) {
            $fileId = json_decode($files->file)->metadata->fileid;
            $fileName = json_decode($files->file)->metadata->name;

            $fileURL = $pcloudFile->getLink($fileId);

            array_push($appFiles, array(
                'id' => $files->id, 
                'app_id' => $files->app_id,
                'file_name' => $files->file_name . " - " . $fileName,
                'file_url' => $fileURL
            ));
        }

        //send response to frontend
        try {
            return response()->json([
                "success" => true,
                "data" => $app,
                "files" => $appFiles
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
        $access_token = 'gxRm7Z2sQ7W52IlDfZzhSai7ZodY01KQSM8XsQraR76f8109rKDiy';
        $locationid = 1;
        $folder_id = 12362338034;

        $pCloudApp = new pCloud\App();
        $pCloudApp->setAccessToken($access_token);
        $pCloudApp->setLocationId($locationid);

        $pcloudFolder = new pCloud\Folder($pCloudApp);
        $pcloudFile = new pCloud\File($pCloudApp);

        //create ID to prevent overwritting
        $rand = substr(md5(microtime()),rand(0,26),3);
        $app_id =  "SR" . $rand . date("mdyyHis");

        //check if the application submitted files then send response
        if (!$request->hasFile('application_pic') || !$request->hasFile('application_sig') || !$request->hasFile('docs')) {
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

        $allowedFileExtension = ['pdf'];

        $docs = $request->file('docs');
        
        foreach ($docs as $doc) {
            $check_doc = in_array($doc->getClientOriginalExtension(), $allowedFileExtension);
            if (!$check_doc) {
                return response()->json([
                    "success" => false,
                    "message" => "Invalid file format."
                ], 422);
            }
        }

        //store the original name to variables
        // $pic_name = $application_pic->getClientOriginalName();
        // $sig_name = $application_sig->getClientOriginalName();

        //store the path to variables
        // $pic_path = $application_pic->storeAs('public/d0czx/' . $app_id, $pic_name);
        // $sig_path = $application_sig->storeAs('public/d0czx/' . $app_id, $sig_name);

        //save the records then send response and application ID for tracking
        if ($app->save()) {
            //upload 1x1 pic
            $fileMetaData = $pcloudFile->upload($application_pic->getRealPath(), $folder_id, $app_id . $application_pic->getClientOriginalName());

            $app_pic = new ApplicationFiles;
            $app_pic->app_id = $app->id;
            $app_pic->file = json_encode($fileMetaData);
            $app_pic->file_name = "Application Picture (1x1)";
            $app_pic_save = $app_pic->save();

            if (!$app_pic_save) {
                return response()->json([
                    "success" => false,
                    "message" => "Application file FAILED to upload."
                ], 500);
            }

            //upload signature
            $fileMetaData = $pcloudFile->upload($application_sig->getRealPath(), $folder_id, $app_id . $application_sig->getClientOriginalName());

            $app_sig = new ApplicationFiles;
            $app_sig->app_id = $app->id;
            $app_sig->file = json_encode($fileMetaData);
            $app_sig->file_name = "Application Signature";
            $app_sig_save = $app_sig->save();

            if (!$app_sig_save) {
                return response()->json([
                    "success" => false,
                    "message" => "Application file FAILED to upload."
                ], 500);
            }

            //upload docs
            foreach ($docs as $doc) {
                $fileMetaData = $pcloudFile->upload($doc->getRealPath(), $folder_id, $app_id . $doc->getClientOriginalName());

                $app_doc = new ApplicationFiles;
                $app_doc->app_id = $app->id;
                $app_doc->file = json_encode($fileMetaData);
                $app_doc->file_name = "Documentation";
                $app_doc_save = $app_doc->save();

                if (!$app_doc_save) {
                    return response()->json([
                        "success" => false,
                        "message" => "Application file FAILED to upload."
                    ], 500);
                }

            }
            
            return response()->json([
                "success" => true,
                "message" => "Application success",
                "application_id" => $app_id 
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Application is NOT success"
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

    public function test_post(Request $request) { 
        // {
        //     "result": 0,
        //     "userid": 17978566,
        //     "locationid": 1,
        //     "token_type": "bearer",
        //     "access_token": "gxRm7Z2sQ7W52IlDfZzhSai7ZodY01KQSM8XsQraR76f8109rKDiy"
        // }

        // $access_token = 'gxRm7Z2sQ7W52IlDfZzhSai7ZodY01KQSM8XsQraR76f8109rKDiy';
        // $locationid = 1;
        // $folder_id = 12362338034;

        // $pCloudApp = new pCloud\App();
        // $pCloudApp->setAccessToken($access_token);
        // $pCloudApp->setLocationId($locationid);

        // $pcloudFolder = new pCloud\Folder($pCloudApp);
        // $pcloudFile = new pCloud\File($pCloudApp);
        
        // $files = $request->file('docs');
        // $errctr = 0;
        // if ($request->hasFile('docs')) {
        //     foreach ($files as $file) {
        //         $fileMetadata = $pcloudFile->upload($file, $folder_id, "test" . $file->getClientOriginalName());
        //         $app_pic = new ApplicationFiles;
        //         $app_pic->app_id = 24;
        //         $app_pic->file = json_encode($fileMetadata);
        //         $app_pic->file_name = "Document";
        //         $app_pic_save = $app_pic->save();

        //         if (!$app_pic_save) {
        //             $errctr += 1;
        //         }
        //     }
        // }

        // if ($errctr >= 1) {
        //     return response()->json([
        //         "success" => false,
        //         "message" => "Cannot be saved."
        //     ], 401);
        // } else {
        //     return response()->json([
        //         "success" => true,
        //         "message" => "Success"
        //     ], 200);
        // }
    }
}
