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
            ->select('org_charts.id', 'org_charts.under_of', 'org_divisions.id as division_id', 'org_people.first_name', 'org_people.middle_initial', 'org_people.last_name', 'org_people.suffix', 'org_positions.position_name', 'org_divisions.division_name', 'org_divisions.sub_division_of', 'org_divisions.order')
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
        $person = $request->json()->all();
        $new_person = new OrgPerson;
        $new_person->first_name = $person['first_name'];
        $new_person->middle_initial = $person['middle_initial'];
        $new_person->last_name = $person['last_name'];
        $new_person->suffix = $person['suffix'];
        $new_person->gender = $person['gender'];
        $new_person->birthday = $person['birthday'];

        if ($new_person->save()) {
            return response()->json([
                'success' => true,
                'data' => $person
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Organization person record could NOT be saved"
            ], 500);
        }
    }

    public function put_person(Request $request, $id) {
        $new_person = $request->json()->all();
        $person = OrgPerson::find($id);

        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => "Organization person record could NOT be found"
            ], 404);
        }

        $updated = $person->fill($new_person)->save();

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
        $person = OrgPerson::find($id);

        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => "Organization person could NOT be found"
            ], 404);
        }

        if ($person->delete()) {
            return response()->json([
                'success' => true,
                'message' => "Organization person successfully deleted"
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Organization person is NOT deleted"
            ], 500);
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
