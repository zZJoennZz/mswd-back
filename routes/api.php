<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BlogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrgController;
use App\Http\Controllers\ClientMessageController;
use App\Http\Controllers\ServAppliController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\AnnController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('signin', [AuthController::class, 'signin'])->name('login');
Route::post('signup', [AuthController::class, 'signup']);
Route::post('validate', [AuthController::class, 'validateToken']);
Route::middleware('auth:api')->group(function () {
    Route::resource('blog', BlogController::class);

    Route::post('org/division', [OrgController::class, 'post_division']);
    Route::put('org/division/{id}', [OrgController::class, 'put_division']);
    Route::delete('org/division/{id}', [OrgController::class, 'delete_division']);

    Route::get('org/person', [OrgController::class, 'get_person']);
    Route::post('org/person', [OrgController::class, 'post_person']);
    Route::put('org/person/{id}', [OrgController::class, 'put_person']);
    Route::delete('org/person/{id}', [OrgController::class, 'delete_person']);

    Route::post('org/position', [OrgController::class, 'post_position']);
    Route::put('org/position/{id}', [OrgController::class, 'put_position']);
    Route::delete('org/position/{id}', [OrgController::class, 'delete_position']);

    Route::put('client_message/{id}', [ClientMessageController::class, 'change_status']);
    Route::delete('client_message/{id}', [ClientMessageController::class, 'delete_msg']);
    Route::get('client_message', [ClientMessageController::class, 'get_all']);
    Route::get('client_message/{id}', [ClientMessageController::class, 'get_msg']);

    Route::get('faq/{id}', [FaqController::class, 'get_single']);
    Route::post('faq', [FaqController::class, 'post_faq']);
    Route::put('faq/{id}', [FaqController::class, 'put_faq']);
    Route::delete('faq/{id}', [FaqController::class, 'delete_faq']);

    Route::post('announcement/post', [AnnController::class, 'post_ann']);
    //http://127.0.0.1:8000/api/announcement/1?_method=PUT
    Route::put('announcement/{id}', [AnnController::class, 'put_ann']);

    Route::post('signout', [AuthController::class, 'signout']);

    Route::get('appli', [ServAppliController::class, 'get_all']);
});

//test
Route::post('appli', [ServAppliController::class, 'submit_appli']);

//public end points
Route::get('org', [OrgController::class, 'get_org']);
Route::get('org/division', [OrgController::class, 'get_division']);
Route::get('org/position', [OrgController::class, 'get_position']);

Route::get('announcement', [AnnController::class, 'get_all']);

Route::post('client_message', [ClientMessageController::class, 'post_msg']);

Route::get('faq', [FaqController::class, 'get_all']);

//invalid access
Route::get('invalid', function() {
    return response()->json([
        'success' => false,
        'message' => "Invalid access"
    ], 500);
})->name('invalid_access');