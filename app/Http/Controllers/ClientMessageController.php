<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientMessage;

class ClientMessageController extends Controller
{
    //
    public function get_all() {
        $msgs = ClientMessage::all();
        
        if (!$msgs) {
            return response()->json([
                'success' => false,
                'message' => "Client messages could NOT be displayed"
            ], 401);
        } else {
            return response()->json([
                'success' => true,
                'data' => $msgs
            ], 200);
        }
    }

    public function get_msg($id) {
        $msg = ClientMessage::find($id);

        if (!$msg) {
            return response()->json([
                'success' => false,
                'message' => "Client message could NOT be displayed"
            ], 401);
        } else {
            return response()->json([
                'success' => true,
                'data' => $msg
            ], 200);
        }
    }

    public function post_msg(Request $request) {
        $msg = $request->json()->all();

        $new_msg = new ClientMessage;
        $new_msg->email_address = $msg['email_address'];
        $new_msg->full_name = $msg['full_name'];
        $new_msg->subject = $msg['subject'];
        $new_msg->message = $msg['message'];
        $new_msg->notes = "";
        $new_msg->status = 0;

        if ($new_msg->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Message is submitted'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Message is NOT submitted'
            ], 500);
        }
    }

    public function change_status(Request $request, $id) {
        $msg_changes = $request->json()->all();
        $msg = ClientMessage::find($id);

        if (!$msg) {
            return response()->json([
                'success' => false,
                'message' => 'Message could NOT be found'
            ], 404);
        }

        $msg->status = $msg_changes['status'];
        echo $msg->status . "-" . $msg_changes['status'];

        if ($msg->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Status is updated'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Status could NOT be updated'
            ], 500);
        }
    }

    public function delete_msg($id) {
        $msg = ClientMessage::find($id);

        if (!$msg) {
            return response()->json([
                'success' => false,
                'message' => 'Client message could NOT be found'
            ], 401);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Client message deleted'
            ], 200);
        }
    }

}
