<?php

use App\Http\Controllers\DataController;
use App\Http\Controllers\PhotographyController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('register', [UserController::class, 'register']); //email, password, user_type(product_owner or photographer)
Route::post('login', [UserController::class, 'authenticate']); //email, password
Route::post('updateProfileInfo/{user}', [UserController::class, 'updateProfileInfo']);
Route::get('open', [DataController::class, 'open']);

Route::group(['middleware' => ['jwt.verify']], function () {
    //product owner requesting product photograph
    Route::post('photograph-request', [PhotographyController::class, 'productOwnerRequest']); //product_name

    //photographer viewing all the request made
    Route::get('view-request', [PhotographyController::class, 'ViewRequests']);

    //photographer viewing single request
    Route::get('view-request/{id}', [PhotographyController::class, 'ViewOneRequest']); //photo request id

    //photographer uploading image for a request
    Route::post('upload-image/{id}', [PhotographyController::class, 'uploadImage']); //photo request id ,  images[]

    //product owner viewing all  photographs thumbnails
    Route::get('view-photos/{id}', [PhotographyController::class, 'productOwnerView']); //photo request id

    //product owner approving or disapproving thumbnails before it is made available to them or not
    Route::post('decide-photos/{id}', [PhotographyController::class, 'productOwnerDecide']); //photograph id , status('approve' 'disapprove')

    Route::get('user', [UserController::class, 'getAuthenticatedUser']);
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
