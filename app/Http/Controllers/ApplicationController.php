<?php

namespace App\Http\Controllers;

//require_once(base_path('vendor') . '\pcloud\pcloud-php-sdk\lib\pCloud\autoload.php'); //for dev
use App\Mail\AppNotif;
use App\Mail\AppApprove;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Application;
use App\Models\ApplicationFiles;
use App\Models\ApplicationTracker;
use App\Models\UserApplicationHistory;
use pCloud;

class ApplicationController extends Controller
{
    //get all application records
    public function get_all()
    {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
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
    public function get_single($id)
    {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $rand = substr(md5(microtime()), rand(0, 26), 3);
        $app_id =  $rand . date("mdyyHis");
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

        //$pcloudFolder = new pCloud\Folder($pCloudApp);
        $pcloudFile = new pCloud\File($pCloudApp);

        $method = "getfilepublink";


        $appFiles = [];
        foreach ($app_files as $files) {
            $fileId = json_decode($files->file)->metadata->fileid;
            $fileName = json_decode($files->file)->metadata->name;
            $fileType = json_decode($files->file)->metadata->contenttype;
            $params = array(
                'fileid' => $fileId,
            );
            $req = new pCloud\Request($pCloudApp);
            $res = $req->get($method, $params);

            $thumbnailLink = "n/a";

            if (substr($fileType, 0, 5) === "image") {
                $method1 = "getpubthumblink";
                $params1 = array(
                    'fileid' => $fileId,
                    'code' => $res->code,
                    'size' => "100x100"
                );

                $req1 = new pCloud\Request($pCloudApp);
                $res1 = $req1->get($method1, $params1);
                $toUpload = $res1->hosts[0] . stripslashes($res1->path);

                // upload to different imghost
                //$contents = file_get_contents($toUpload);
                $image_base64 = base64_encode(file_get_contents("https://" . $toUpload));

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key=d1f0d556dc4121b9db63e20830ba67f7');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                $imgToUpload = array('image' => $image_base64, 'name' => $app_id);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $imgToUpload);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    return response()->json([
                        "success" => false,
                        "message" => curl_error($ch)
                    ], 400);
                }
                curl_close($ch);
                $res2 = json_decode($result);

                //end upload to different imghost

                $thumbnailLink = $res2->data->url;
            }

            array_push($appFiles, array(
                'id' => $files->id,
                'app_id' => $files->app_id,
                'file_name' => $files->file_name . " - " . $fileName,
                'file_url' => $res->link,
                'image_url' => $thumbnailLink
            ));
        }

        $app_status = DB::table('application_trackers')
            ->leftjoin('applications', 'applications.id', '=', 'application_trackers.app_id')
            ->select('application_trackers.*', 'applications.application_id')
            ->where('application_trackers.app_id', $id)
            ->get();

        //send response to frontend
        try {
            return response()->json([
                "success" => true,
                "data" => $app,
                "files" => $appFiles,
                "app_status" => $app_status
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th
            ], 500);
        }
    }

    //post application
    public function post_application(Request $request)
    {
        if (auth()->user()['is_admin'] === "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);

        $getEmail = json_decode($request->application_data, true)['email_address'];
        $getAppType = json_decode($request->application_data, true)['appliType'];
        $isNew = 0;
        if ($getAppType !== 3) {
            $isNew = json_decode($request->application_data, true)['appli_type'];
        }
        $getFirstName = json_decode($request->application_data, true)['first_name'];
        $getMiddleName = json_decode($request->application_data, true)['middle_name'];
        $getLastName = json_decode($request->application_data, true)['last_name'];
        $getBirthday = json_decode($request->application_data, true)['dob'];

        $checkRecords = DB::table('user_application_histories')
            ->leftjoin('applications', 'user_application_histories.app_id', '=', 'applications.id')
            ->where('applications.status', '=', 0)
            ->select('applications.id', 'applications.application_data')
            ->get();

        if (count($checkRecords) >= 1) {
            $getAppIds = array();

            for ($i = 0; $i < count($checkRecords); $i++) {
                array_push($getAppIds, json_decode($checkRecords[$i]->application_data, true)['appliType']);
            }


            $isExists = in_array($getAppType, $getAppIds);
            if ($isExists) {
                return response()->json([
                    "success" => false,
                    "message" => "You still have existing application"
                ], 400);
            }
        }

        if ($getAppType === 1 || $getAppType === 2) {
            $filter = [['applications.status', '=', 3], ['user_application_histories.user_id', '=', auth()->user()['id']], ['user_application_histories.app_type', '=', $getAppType]];

            $checkRecordsForOldApp = DB::table('user_application_histories')
                ->leftjoin('applications', 'user_application_histories.app_id', '=', 'applications.id')
                // ->where('applications.status', '=', 3)
                // ->where('user_application_histories.user_id', '=', auth()->user()['id'])
                // ->where('user_application_histories.app_type', '=', $getAppType)
                ->where($filter)
                ->select('applications.id', 'applications.application_data')
                ->get();

            if ($isNew === "new" && count($checkRecordsForOldApp) >= 1) {
                return response()->json([
                    "success" => false,
                    "message" => "You are already approved to this ID",

                ], 400);
            }

            $filter = [['applications.status', '=', 3], ['user_application_histories.app_type', '=', $getAppType]];
            $checkRecordsForOldAppAgain = DB::table('user_application_histories')
                ->leftjoin('applications', 'user_application_histories.app_id', '=', 'applications.id')
                ->where($filter)
                ->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(`applications`.`application_data`, '$.first_name'))"), "=", $getFirstName)
                ->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(`applications`.`application_data`, '$.middle_name'))"), "=", $getMiddleName)
                ->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(`applications`.`application_data`, '$.last_name'))"), "=", $getLastName)
                ->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(`applications`.`application_data`, '$.dob'))"), "=", $getBirthday)
                ->select('applications.id', 'applications.application_data')
                ->get();

            if ($isNew === "new" && count($checkRecordsForOldAppAgain) >= 1) {
                return response()->json([
                    'success' => false,
                    'message' => "You already applied for this ID"
                ], 400);
            }
        }

        $access_token = 'gxRm7Z2sQ7W52IlDfZzhSai7ZodY01KQSM8XsQraR76f8109rKDiy';
        $locationid = 1;
        $folder_id = 12362338034;

        $pCloudApp = new pCloud\App();
        $pCloudApp->setAccessToken($access_token);
        $pCloudApp->setLocationId($locationid);

        $pcloudFolder = new pCloud\Folder($pCloudApp);
        $pcloudFile = new pCloud\File($pCloudApp);

        //create ID to prevent overwriting
        $rand = substr(md5(microtime()), rand(0, 26), 3);
        $app_id_prefix = ($getAppType === 1 ? "SP" : ($getAppType === 2 ? "PWD" : "SC"));

        $app_id =  $app_id_prefix . $rand . date("mdyyHis");

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

        $allowedFileExtension = ['pdf', 'PDF'];

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

        //save the records then send response and application ID for tracking
        if ($app->save()) {
            //upload 1x1 pic
            $fileMetaData = $pcloudFile->upload($application_pic->getRealPath(), $folder_id, $app_id . rand(1, 1000) . $application_pic->getClientOriginalName());

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

            $app_status = new ApplicationTracker;
            $app_status->app_id = $app->id;
            $app_status->statusMsg = "Application submitted on " . $app->created_at;
            $app_status->status = 0;

            if (!$app_status->save()) {
                return response()->json([
                    "success" => false,
                    "message" => "Application is NOT successful"
                ], 500);
            }

            $userHis = new UserApplicationHistory;
            $userHis->user_id = $request->user()->id;
            $userHis->app_id = $app->id;
            $userHis->app_type = $getAppType;

            if (!$userHis->save()) {
                return response()->json([
                    "success" => false,
                    "message" => "Something went wrong! Your application is submitted but you cannot view it in your history records."
                ], 500);
            }

            Mail::to($getEmail)->send(new AppNotif($getEmail, $app_id));

            return response()->json([
                "success" => true,
                "message" => "Application success",
                "application_id" => $app_id
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Application is NOT successful"
            ], 500);
        }
    }

    //delete an application
    public function delete_application($id)
    {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        //find the application files for this specific application
        $appFileMeta = ApplicationFiles::where("app_id", $id)->get();

        $access_token = 'gxRm7Z2sQ7W52IlDfZzhSai7ZodY01KQSM8XsQraR76f8109rKDiy';
        $locationid = 1;
        $folder_id = 12362338034;

        $pCloudApp = new pCloud\App();
        $pCloudApp->setAccessToken($access_token);
        $pCloudApp->setLocationId($locationid);

        foreach ($appFileMeta as $file) {
            $pcloudFile = new pCloud\File($pCloudApp);

            $fileId = json_decode($file->file)->metadata->fileid;
            $pcloudFile->delete($fileId);
        }

        $appFiles = ApplicationFiles::where("app_id", $id)->delete();

        $appTrackers = ApplicationTracker::where("app_id", $id)->delete();

        $appHistory = UserApplicationHistory::where("app_id", $id)->delete();

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

    public function get_app_status($app_id)
    {
        $app_status = DB::table('application_trackers')
            ->leftjoin('applications', 'applications.id', '=', 'application_trackers.app_id')
            ->select('application_trackers.*', 'applications.application_id')
            ->where('applications.application_id', $app_id)
            ->get();

        if (is_null($app_status) || $app_status === []) {
            return response()->json([
                "success" => false,
                "message" => "Application status could NOT be fetched"
            ], 500);
        }

        return response()->json([
            "success" => true,
            "data" => $app_status
        ], 200);
    }

    public function post_app_status(Request $request)
    {
        $status_count = DB::table('application_trackers')
            ->where('app_id', $request->app_id)
            ->count();

        if ($status_count === 3) {
            return response()->json([
                "success" => false,
                "message" => "You CANNOT add more status update"
            ]);
        }

        if ($status_count === 2) {
            $status = new ApplicationTracker;
            $status->app_id = $request->app_id;
            $status->statusMsg = $request->statusMsg . "\n - " . auth()->user()['name'];
            $status->status = $request->status;

            $app = Application::find($request->app_id);
            $app->status = $request->status;

            $getEmail = json_decode($app->application_data, true)['email_address'];

            if ($status->save() && $app->save()) {

                if (intval($request->status) === 3) {
                    Mail::to($getEmail)->send(new AppApprove($getEmail, $request->app_id));
                }

                return response()->json([
                    "success" => true,
                    "message" => "Application status updated"
                ], 200);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Application status NOT updated"
                ], 500);
            }
        }

        if ($status_count === 1) {
            $status = new ApplicationTracker;
            $status->app_id = $request->app_id;
            $status->statusMsg = $request->statusMsg;
            $status->status = 1;

            if ($status->save()) {
                return response()->json([
                    "success" => true,
                    "message" => "Application status updated"
                ], 200);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Application status NOT updated"
                ], 500);
            }
        }

        return response()->json([
            "success" => false,
            "message" => "Something went wrong."
        ], 500);
    }

    public function get_user_app_history()
    {
        $user_history = DB::table('applications')
            ->leftjoin('user_application_histories', 'user_application_histories.app_id', '=', 'applications.id')
            ->where('user_application_histories.user_id', '=', auth()->user()->id)
            ->select('user_application_histories.id', 'applications.application_id', 'applications.created_at', 'applications.application_data', 'applications.status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $user_history
        ], 200);
    }

    public function test_post(Request $request)
    {
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
