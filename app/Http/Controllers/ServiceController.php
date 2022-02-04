<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    //

    public function get_service() {
        $services = Service::all();

        if ($services) {
            return response()->json([
                "success" => true,
                "data" => $services
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "No services available"
            ], 404);
        }
    }

    public function post_service(Request $request) {
        $services = $request->json->all();
    }
}
