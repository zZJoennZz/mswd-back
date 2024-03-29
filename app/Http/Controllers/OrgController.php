<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OrgChart;
use App\Models\OrgDivision;
use App\Models\OrgPerson;
use App\Models\OrgPosition;

class OrgController extends Controller
{
    #region Org
    public function get_org() {
        $org = DB::table('org_charts')
            ->leftjoin('org_people', 'org_charts.person_id', '=', 'org_people.id')
            ->leftjoin('org_positions', 'org_charts.position_id', '=', 'org_positions.id')
            ->leftjoin('org_divisions', 'org_charts.division_id', '=', 'org_divisions.id')
            ->select('org_charts.id', 'org_charts.under_of', 'org_divisions.id as division_id', 'org_people.first_name', 'org_people.middle_initial', 'org_people.last_name', 'org_people.suffix', 'org_positions.position_name', 'org_divisions.division_name', 'org_divisions.sub_division_of', 'org_divisions.order', 'org_charts.created_at', 'org_charts.updated_at', 'org_people.img_path')
            ->get();
        
        if (!$org) {
            return response()->json([
                'success' => false,
                'message' => "Organization chart cannot be fetched"
            ], 500);
        } else {
            return response()->json([
                'success' => true,
                'data' => $org
            ], 200);
        }
    }

    public function get_org_all() {
        $org = OrChart::all();

        if (!$org) {
            return response()->json([
                'success' => false,
                'message' => "Organization chart cannot be fetched"
            ], 500);
        } else {
            return response()->json([
                'success' => true,
                'data' => $org
            ], 200);
        }
    }

    public function get_single_org($id) {
        $org = OrgChart::find($id);

        if (!$org) {
            return response()->json([
                'success' => false,
                'message' => "Organization entry cannot be fetched"
            ], 401);
        } else {
            return response()->json([
                'success' => true,
                'data' => $org
            ]);
        }
    }

    public function post_org(Request $request) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);

        $data = $request->json()->all();

        $new_org = new OrgChart;

        $this->validate($request, [
            'person_id' => 'required',
            'position_id' => 'required',
            'division_id' => 'required'
        ]);

        $new_org->person_id = $data['person_id'];
        $new_org->position_id = $data['position_id'];
        $new_org->division_id = $data['division_id'];
        $new_org->under_of = 0;
        $new_org->order = 0;

        if ($new_org->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Organization chart entry is saved successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Organization chart entry is NOT saved'
            ], 400);
        }
    }

    public function put_org(Request $request, $id) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $data = $request->json()->all();
        $org = OrgChart::find($id);

        if (!is_null($data['person_id']) && intval($data['person_id']) !== 0) {
            $org->person_id = intval($data['person_id']);
        }
        if (!is_null($data['position_id']) && intval($data['position_id']) !== 0) {
            $org->position_id = intval($data['position_id']);
        }
        if (!is_null($data['division_id']) && intval($data['division_id']) !== 0) {
            $org->division_id = intval($data['division_id']);
        }

        // return response()->json([
        //     'data' => intval($data['position_id']),
        //     'org' => $org
        // ], 200);

        if ($org->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Organization chart entry is saved successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Organization chart entry is NOT saved'
            ], 400);
        }
    }

    public function delete_org($id) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $org = OrgChart::find($id);

        if (!$org) {
            return response()->json([
                'success' => false,
                'message' => "Entry cannot be found"
            ], 404);
        }

        try {
            if ($org->delete()) {
                return response()->json([
                    'success' => true,
                    'message' => "Entry is deleted"
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Entry cannot be deleted"
                ], 401);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Entry cannot be deleted"
            ], 401);
        }
    }
    #endregion

    #region Org Division
    public function get_division() {
        $data = OrgDivision::all();
        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'Organization division fetched successfully',
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'NO organization division fetched'
            ], 500);
        }
    }

    public function get_single_division($id) {
        $division = OrgDivision::find($id);

        if (!$division) {

        }
    }

    public function post_division(Request $request) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $data = $request->json()->all();
        $new_division = new OrgDivision;
        $new_division->division_name = $data['division_name'];
        $new_division->sub_division_of = $data['sub_division_of'];
        $new_division->order = $data['order'];

        if ($new_division->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Organization division successfully saved',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Organization division is NOT saved'
            ], 500);
        }
    }

    public function put_division(Request $request, $id) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $data = $request->json()->all();
        $division = OrgDivision::find($id);
        
        if (!$division) {
            return response()->json([
                'success' => false,
                'message' => 'Organization division could NOT be found'
            ], 404);
        }

        $updated = $division->fill($data)->save();

        if ($updated) { 
            return response()->json([
                'success' => true,
                'message' => 'Organization division changes saved'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Organization division changes is NOT saved'
            ], 500);
        }
    }

    public function delete_division($id) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $division = OrgDivision::find($id);

        if (!$division) {
            return response()->json([
                'success' => false,
                'message' => 'Organization division could NOT be found'
            ], 404);
        }
        
        if ($division->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Organization division successfully deleted'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Organization division is NOT deleted'
            ], 500);
        }
    }
    #endregion

    #region Org Person
    public function get_person() {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);

        $person = OrgPerson::all();

        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => 'Organization person could NOT be fetched'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $person
        ], 200);
    }

    public function post_person(Request $request) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);

        if (!$request->hasFile('img_path')) {
            return response()->json([
                "success" => false,
                "message" => "No file attached"
            ], 400);
        } 

        //person image
        $allowedFileExtension = ['png', 'jpg', 'jpeg', 'gif', 'PNG'];
        $personImg = $request->file('img_path');
        $extension = $personImg->getClientOriginalExtension();
        $check = in_array($extension, $allowedFileExtension);
        $res;

        if ($check) {
            //generate unique ID for saving and to avoid overwritting an image
            $img_id =  uniqid("person", false) . date("mmddyyyHis");
            //get the file original name
            $name = $personImg->getClientOriginalName();
            //save the file to ImgBB through their API
            // $path = $featuredImg->storeAs('public/ann-img', $img_id . $name);
            $contents = file_get_contents($request->file('img_path')->getRealPath());

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
        }

        //$person = $request->json()->all();
        $new_person = new OrgPerson;
        $new_person->first_name = $request->first_name;
        $new_person->middle_initial = $request->middle_initial;
        $new_person->last_name = $request->last_name;
        $new_person->suffix = $request->suffix;
        $new_person->gender = $request->gender;
        $new_person->birthday = $request->birthday;
        $new_person->img_path = $res->data->url;

        if ($new_person->save()) {
            return response()->json([
                'success' => true,
                'message' => "Person record saved"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Organization person record could NOT be saved"
            ], 500);
        }
    }

    public function put_person(Request $request, $id) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);

        $person = OrgPerson::find($id);


        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => "Organization person record could NOT be found"
            ], 404);
        }

        if ($request->hasFile('img_path')) {
            //person image
            $allowedFileExtension = ['png', 'jpg', 'jpeg', 'gif', 'PNG'];
            $personImg = $request->file('img_path');
            $extension = $personImg->getClientOriginalExtension();
            $check = in_array($extension, $allowedFileExtension);
            $res;

            if ($check) {
                //generate unique ID for saving and to avoid overwritting an image
                $img_id =  uniqid("person", false) . date("mmddyyyHis");
                //get the file original name
                $name = $personImg->getClientOriginalName();
                //save the file to ImgBB through their API
                // $path = $featuredImg->storeAs('public/ann-img', $img_id . $name);
                $contents = file_get_contents($request->file('img_path')->getRealPath());

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
                $person->img_path = $res->data->url;
            }
        
        }
        // $updated = $person->fill($new_person)->save();
        if (!is_null($request->first_name)) { $person->first_name = $request->first_name; }
        if (!is_null($request->middle_initial)) { $person->middle_initial = $request->middle_initial; }
        if (!is_null($request->last_name)) { $person->last_name = $request->last_name; }
        if (!is_null($request->suffix)) { $person->suffix = $request->suffix; }
        if ($request->gender !== 3 || $request->gender !== 0) { $person->gender = $request->gender; }
        if (!is_null($request->birthday)) { $person->birthday = $request->birthday; }

        $updated = $person->save();

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => "Organization person record changes successfully saved"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Organization person record chanages is NOT saved"
            ], 500);
        }
    }

    public function delete_person($id) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $person = OrgPerson::find($id);

        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => "Organization person could NOT be found"
            ], 404);
        }
        try {
            if ($person->delete()) {
                return response()->json([
                    'success' => true,
                    'message' => "Organization person successfully deleted"
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Organization person is NOT deleted"
                ], 200);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Organization person is NOT deleted"
            ], 200);
        }
        
    }
    #endregion

    #region Org Position
    public function get_position() {
        $position = OrgPosition::all();

        if (!$position) {
            return response()->json([
                'success' => false,
                'message' => 'Organization position could NOT be fetched'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $position
        ], 200);
    }

    public function post_position(Request $request) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $position = $request->json()->all();
        $new_position = new OrgPosition;
        $new_position->position_name = $position['position_name'];
        $new_position->position_desc = $position['position_desc'];

        if ($new_position->save()) {
            return response()->json([
                'success' => true,
                'data' => $position
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Organization position record could NOT be saved"
            ], 500);
        }
    }

    public function put_position(Request $request, $id) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $new_position = $request->json()->all();
        $position = OrgPosition::find($id);

        if (!$position) {
            return response()->json([
                'success' => false,
                'message' => "Organization position record could NOT be found"
            ], 404);
        }

        $updated = $position->fill($new_position)->save();

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => "Organization position record changes successfully saved"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Organization position record chanages is NOT saved"
            ], 500);
        }
    }

    public function delete_position($id) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);

        $position = OrgPosition::find($id);

        if (!$position) {
            return response()->json([
                'success' => false,
                'message' => "Organization position could NOT be found"
            ], 404);
        }

        if ($position->delete()) {
            return response()->json([
                'success' => true,
                'message' => "Organization position successfully deleted"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Organization position is NOT deleted"
            ], 500);
        }
    }
    #endregion

}
