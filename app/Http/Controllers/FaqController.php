<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    //
    public function get_all() {
        $faqs = Faq::all();

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

    public function get_single($id) {
        $faq = Faq::find($id);

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

    public function post_faq(Request $request) {
        $data = $request->json()->all();

        $faq = new Faq;
        $faq->question = $data['question'];
        $faq->answer = $data['answer'];

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

    public function put_faq(Request $request, $id) {
        $data = $request->json()->all();

        $current_faq = Faq::find($id);

        if (!$current_faq) {
            return response()->json([
                "success" => false,
                "message" => "FAQ could NOT be found"
            ], 404);
        }

        $updated = $current_faq->fill($data)->save();

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

    public function delete_faq($id) {
        $faq = Faq::find($id);

        if (!$faq) {
            return response->json([
                "success" => false,
                "message" => "FAQ could NOT be found"
            ], 404);
        }

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
