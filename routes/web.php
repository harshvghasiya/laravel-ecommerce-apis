<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['domain' => 'ee.resto-api.test'], function () {
    Route::get('/', function () {
        return "Hello,!";
    });
});

Route::get('/', function () {
    return view('app');
});

Route::get('{any}', function () {
    return redirect('/');
})->where('any', '.*');
