<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    //get all faqs function
    public function get_all() {
        //get all faqs
        $faqs = Faq::all();

        //check if the values was fetched then send response
        if ($faqs) {
            return response()->json([
                "success" => true,
                "data" => $faqs
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "FAQs can NOT be fetched"
            ], 500);
        }
    }

    //get single faq
    public function get_single($id) {
        //find using id
        $faq = Faq::find($id);

        //check if the values was fetched
        if ($faq) {
            return response()->json([
                "success" => true,
                "data" => $faq
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "FAQ can NOT be fetched"
            ], 400);
        }
    }

    //post new faq
    public function post_faq(Request $request) {
        //get the payload data
        $data = $request->json()->all();

        //initialize the model for new record
        $faq = new Faq;
        //put the data to their respective fields
        $faq->question = $data['question'];
        $faq->answer = $data['answer'];

        //execute save and send response if success or not
        if ($faq->save()) {
            return response()->json([
                "success" => true,
                "message" => "FAQ saved"
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "FAQ NOT saved"
            ], 400);
        }
    }

    //put faq changes
    public function put_faq(Request $request, $id) {
        //store payload data
        $data = $request->json()->all();

        //find the faq you wanted to edit
        $current_faq = Faq::find($id);

        //check if faq not found then send response
        if (!$current_faq) {
            return response()->json([
                "success" => false,
                "message" => "FAQ could NOT be found"
            ], 404);
        }

        //store the updated data we sent over and execute save
        $updated = $current_faq->fill($data)->save();

        //check if the saving was successful then send response
        if ($updated) {
            return response()->json([
                "success" => true,
                "message" => "FAQ changes successfully saved"
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "FAQ changes could NOT be saved"
            ], 500);
        }
    }

    //delete a faq
    public function delete_faq($id) {
        //find the faq you wanted to delete
        $faq = Faq::find($id);

        //check if faq was found
        if (!$faq) {
            return response->json([
                "success" => false,
                "message" => "FAQ could NOT be found"
            ], 404);
        }

        //execute delete then send response
        if ($faq->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'FAQ successfully deleted'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'FAQ is NOT deleted'
            ], 500);
        }
    }
}
