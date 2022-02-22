<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ClientMessage;
use App\Models\Application;

class OtherController extends Controller
{
    //
    public function count_dashboard() {
        $inq = ClientMessage::all()->count();
        $app = Application::all()->count();
        $app_proce = DB::table('applications')->WHERE('status', 0)->count();
        
        return response()->json([
            'success' => true,
            'inq_count' => $inq,
            'app_count' => $app,
            'app_proce_count' => $app_proce
        ], 200);
    }
}
