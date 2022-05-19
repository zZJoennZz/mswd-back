<?php

namespace App\Http\Controllers;

use App\Mail\Notification;
use Illuminate\Http\Request;
use App\Models\ClientMessage;

use Illuminate\Support\Facades\Mail;

class ClientMessageController extends Controller
{
    //
    public function get_all() {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
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
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
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
            Mail::to($msg['email_address'])->send(new Notification($msg['email_address']));
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
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $msg_changes = $request->json()->all();
        $msg = ClientMessage::find($id);

        if (!$msg) {
            return response()->json([
                'success' => false,
                'message' => 'Message could NOT be found'
            ], 404);
        }

        $msg->status = $msg_changes['status'];

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

    public function update_note(Request $request, $id) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $msg_changes = $request->json()->all();
        $msg = ClientMessage::find($id);

        if (!$msg) {
            return response()->json([
                'success' => false,
                'message' => 'Message could NOT be found'
            ], 404);
        }

        $msg->notes = $msg_changes['notes'] . ' - ' . auth()->user()['name'] . ' ' . auth()->user()['last_name'];

        if ($msg->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Note is updated'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Note could NOT be updated'
            ], 500);
        }
    }

    public function delete_msg($id) {
        if (auth()->user()['is_admin'] !== "1") return response()->json([
            "success" => false,
            "message" => "You have NO authorization here"
        ], 401);
        $msg = ClientMessage::find($id);

        if (!$msg) {
            return response()->json([
                'success' => false,
                'message' => 'Client message could NOT be found'
            ], 401);
        } else {
            if ($msg->delete()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Client message deleted'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Client message NOT deleted'
                ], 401);
            }
        }
    }

}
