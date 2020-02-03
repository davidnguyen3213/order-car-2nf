<?php

use Illuminate\Http\Request;

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
Route::post('check-app-version', 'Api\AppController@checkAppVersion');
Route::post('login-user', 'Api\UserController@login');
Route::post('register-user', 'Api\UserController@register');
Route::post('check-phone-user', 'Api\UserController@checkPhoneUser');
Route::post('generate-otp', 'Api\UserController@generateOtp');
Route::post('accuracy-otp', 'Api\UserController@accuracyOtp');
Route::get('forgot-password', 'Api\UserController@forgotPassword');

Route::post('/company/login-company', 'Api\CompanyController@loginCompany');
Route::post('/company/register-company', 'Api\CompanyController@registerCompany');

//Api User
Route::middleware('auth:api')->group(function () {
    Route::post('update-user', 'Api\UserController@updateInfo');
    Route::post('user-info', 'Api\UserController@userInfo');
    Route::post('cancel-request-by-user', 'Api\RequestUserController@cancelRequestByUser');
    Route::get('list-call', 'Api\UnregisteredCompanyController@listCall');
    Route::post('favourite', 'Api\FavouriteController@favourite');
    Route::post('call-count', 'Api\CallCountController@callCount');
    Route::post('request-company', 'Api\RequestUserController@addRequestCompany');
    Route::get('list-request', 'Api\RequestUserController@listUserRequest');
    Route::get('history-request', 'Api\ResponseForUserController@getHistoryForUser');
    Route::get('list-response', 'Api\ResponseForUserController@getListResponseOfCompany');
    Route::post('request-company-order', 'Api\ResponseForUserController@requestCompanyOrder');
    Route::post('delete-history', 'Api\RequestUserController@deleteHistory');
    Route::get('get-note', 'Api\RequestUserController@getFrequencyUserByNote');
    Route::get('get-address-to', 'Api\RequestUserController@getFrequencyUserByAddressTo');
    Route::post('delete-suggested-note', 'Api\RequestUserController@deleteSuggestedNote');
    Route::post('delete-suggested-address-to', 'Api\RequestUserController@deleteSuggestedAddressTo');
    Route::post('read-response', 'Api\ResponseForUserController@readResponse');
    Route::post('count-unread-response', 'Api\ResponseForUserController@countUnreadResponse');
});

//Api Company
Route::group(['prefix' => 'company', 'middleware' => 'auth:company-api'], function () {
    Route::get('company-info', 'Api\CompanyController@companyInfo');
    Route::post('update-notification', 'Api\CompanyController@onOffPushNotify');
    Route::get('list-request-user-for-company', 'Api\CompanyController@listRequestUserForCompany');
    Route::post('cancel-request-by-company', 'Api\RequestUserController@cancelRequestByCompany');
    Route::post('registry-request-pickup-user', 'Api\ResponseForUserController@registryRequestPickupUser');
    Route::get('company-request-history', 'Api\ResponseForUserController@companyRequestHistory');
    Route::post('company-approve', 'Api\ResponseForUserController@companyApprove');
    Route::post('read-request', 'Api\CompanyReadRequestController@readRequest');
    Route::post('count-unread-request', 'Api\CompanyReadRequestController@countUnreadRequest');
    Route::post('count-unapproved-request', 'Api\ResponseForUserController@countUnapprovedRequest');
    Route::post('expire-request-by-company', 'Api\RequestUserController@expireRequestByCompany');
});

// Api User and Company
Route::group(['prefix' => 'v1', 'middleware' => 'api'], function () {
    Route::post("read-notify", "Api\NotifyController@readNotify");
    Route::post("get-list-notify", 'Api\NotifyController@getListNotify');
    Route::post('registry-notify', 'Api\FCMTokenController@registryNotify');
    Route::post('logout', 'Api\FCMTokenController@logoutDevice');
});
