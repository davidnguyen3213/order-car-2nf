<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::group(['namespace' => 'Admin'], function() {
    Route::get('/', 'UserController@index')->name('user.index');

    // Authentication Routes...
    Route::group(['namespace' => 'Auth'], function() {
        Route::get('login', 'LoginAdminController@showLoginForm')->name('login');
        Route::post('login', 'LoginAdminController@login');
        Route::any('logout', 'LoginAdminController@logout')->name('logout');

    });

    Route::group(['prefix' => 'admin'], function () {
        // User routes
        Route::get('/user', 'UserController@index')->name('user.index');
        Route::post('/user', 'UserController@index')->name('user.index');
        Route::post('/user/store', 'UserController@store')->name('user.store');
        Route::post('/user/delete/{user_id}', 'UserController@delete')->name('user.delete');
        Route::get('/user/edit/{user_id}', 'UserController@showEditForm')->name('user.edit');

        // Company routes
        Route::get('/company', 'CompaniesController@index')->name('company.index');
        Route::post('/company', 'CompaniesController@index')->name('company.index');
        Route::post('/company/store', 'CompaniesController@store')->name('company.store');
        Route::post('/company/delete/{user_id}', 'CompaniesController@delete')->name('company.delete');
        Route::get('/company/edit/{user_id}', 'CompaniesController@showEditForm')->name('company.edit');

        // request route
        Route::get('/requestuser', 'RequestUserController@index')->name('requestuser.index');
        Route::post('/requestuser', 'RequestUserController@index')->name('requestuser.index');
        Route::post('/requestusercsv', 'RequestUserController@exportCsv')->name('requestuser.exportCsv');
        Route::post('/requestuser/delete', 'RequestUserController@delete')->name('requestuser.delete');
        Route::post("/requestuser/getListResponseCompany", 'RequestUserController@getListResponseCompany');

        // Unregistered Companies routes
        Route::get('/unregisteredCompany', 'UnregisteredCompaniesController@index')->name('unregisteredCompany.index');
        Route::post('/unregisteredCompany', 'UnregisteredCompaniesController@index')->name('unregisteredCompany.index');
        Route::post('/unregisteredCompany/store', 'UnregisteredCompaniesController@store')->name('unregisteredCompany.store');
        Route::post('/unregisteredCompany/delete/{unregistered_company_id}', 'UnregisteredCompaniesController@delete')->name('unregisteredCompany.delete');
        Route::get('/unregisteredCompany/edit/{unregistered_company_id}', 'UnregisteredCompaniesController@showEditForm')->name('unregisteredCompany.edit');
        
        // Push notify routes
        Route::get('/notify','NotifyController@index')->name('notify.index');
        Route::post('/notify', 'NotifyController@index')->name('notify.index');
        Route::post('/notify/store', 'NotifyController@store')->name('notify.store');
    });
});
