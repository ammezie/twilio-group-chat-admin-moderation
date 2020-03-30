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

Route::post('/token', 'TokenController@generate');
Route::post('/members/{username}/ban', 'MemberController@ban');
Route::post('/members/{username}/unban', 'MemberController@unban');
