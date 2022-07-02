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
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\OtherController;
use App\Http\Controllers\ClientController;

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

// Route::post('validate', [AuthController::class, 'validateToken']);
Route::post('signin', [AuthController::class, 'signin'])->name('login');
Route::post('register', [AuthController::class, 'register']);
// Route::post('user/login', [ClientController::class, 'signin']);
// Route::post('user/register', [ClientController::class, 'signup']);
// Route::group(['prefix' => 'client', 'middleware' => ['auth:clientapi', 'scopes:client']], function () {
//     Route::get('hellotest', [ClientController::class, 'signin']);
// });

Route::middleware('auth:api')->group(function () {
    Route::get('getHistory', [ApplicationController::class, 'get_user_app_history']);

    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('validate', [AuthController::class, 'validateToken']);
    Route::post('validate-user', [AuthController::class, 'validateUserToken']);
    Route::get('getprofile', [AuthController::class, 'getProfile']);
    Route::put('updateprofile', [AuthController::class, 'update']);
    Route::get('getall', [AuthController::class, 'get_all']);
    Route::get('getsingle/{id}', [AuthController::class, 'get_single']);
    Route::post('updatesingle/{id}', [AuthController::class, 'update_user']);
    Route::delete('delete_user/{id}', [AuthController::class, 'delete_user']);

    Route::post('application/post', [ApplicationController::class, 'post_application']);
    Route::post('application/additional', [ApplicationController::class, 'submit_additional']);
    route::get('application/get_all_report/{start_date}/{end_date}', [ApplicationController::class, 'get_all_for_report']);

    Route::post('org/division/post', [OrgController::class, 'post_division']);
    Route::put('org/division/put/{id}', [OrgController::class, 'put_division']);
    Route::delete('org/division/delete/{id}', [OrgController::class, 'delete_division']);

    Route::get('org/person', [OrgController::class, 'get_person']);
    Route::post('org/person/post', [OrgController::class, 'post_person']);
    Route::post('org/person/put/{id}', [OrgController::class, 'put_person']);
    Route::delete('org/person/delete/{id}', [OrgController::class, 'delete_person']);

    Route::post('org/position/post', [OrgController::class, 'post_position']);
    Route::put('org/position/put/{id}', [OrgController::class, 'put_position']);
    Route::delete('org/position/delete/{id}', [OrgController::class, 'delete_position']);

    Route::post('org/chart/post', [OrgController::class, 'post_org']);
    Route::get('org/chart/{id}', [OrgController::class, 'get_single_org']);
    Route::put('org/chart/put/{id}', [OrgController::class, 'put_org']);
    Route::delete('org/chart/delete/{id}', [OrgController::class, 'delete_org']);

    Route::put('client_message/put/{id}', [ClientMessageController::class, 'change_status']);
    Route::put('client_message/put_note/{id}', [ClientMessageController::class, 'update_note']);
    Route::delete('client_message/delete/{id}', [ClientMessageController::class, 'delete_msg']);
    Route::get('client_message', [ClientMessageController::class, 'get_all']);
    Route::get('client_message/{id}', [ClientMessageController::class, 'get_msg']);

    Route::get('faq/{id}', [FaqController::class, 'get_single']);
    Route::post('faq/post', [FaqController::class, 'post_faq']);
    Route::put('faq/put/{id}', [FaqController::class, 'put_faq']);
    Route::delete('faq/delete/{id}', [FaqController::class, 'delete_faq']);

    Route::post('announcement/post', [AnnController::class, 'post_ann']);
    //http://127.0.0.1:8000/api/announcement/1?_method=PUT
    Route::post('announcement/put/{id}', [AnnController::class, 'put_ann']);
    Route::delete('announcement/delete/{id}', [AnnController::class, 'delete_ann']);

    Route::post('signout', [AuthController::class, 'signout']);

    Route::get('appli', [ServAppliController::class, 'get_all']);

    Route::get('application', [ApplicationController::class, 'get_all']);
    Route::get('application/{id}', [ApplicationController::class, 'get_single']);
    Route::delete('application/delete/{id}', [ApplicationController::class, 'delete_application']);
    Route::post('application/status/post', [ApplicationController::class, 'post_app_status']);

    Route::get('dashboard/count', [OtherController::class, 'count_dashboard']);
});

//test
Route::post('appli/post', [ServAppliController::class, 'submit_appli']);

//public end points
Route::get('org', [OrgController::class, 'get_org']);
Route::get('org/division', [OrgController::class, 'get_division']);
Route::get('org/position', [OrgController::class, 'get_position']);

Route::get('announcement', [AnnController::class, 'get_all']);
Route::get('announcement/{id}', [AnnController::class, 'get_single']);

Route::post('client_message/post', [ClientMessageController::class, 'post_msg']);

Route::get('faq', [FaqController::class, 'get_all']);


Route::post('test', [ApplicationController::class, 'test_post']);


Route::get('track/{app_id}', [ApplicationController::class, 'get_app_status']);

//invalid access
Route::get('invalid', function () {
    return response()->json([
        'success' => false,
        'message' => "Invalid access"
    ], 500);
})->name('invalid_access');
