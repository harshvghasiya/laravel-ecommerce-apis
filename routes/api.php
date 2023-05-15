<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::group([ 'prefix' => 'auth' ], function()
{
	Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
	Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

});


// No Auth Routes
Route::get('/product/list', [App\Http\Controllers\Api\ProductController::class, 'productList']);	
Route::get('/product-details', [App\Http\Controllers\Api\ProductController::class, 'productDetails']);	
Route::get('/product-category/list', [App\Http\Controllers\Api\ProductController::class, 'categoryList']);	
Route::get('/product-tag/list', [App\Http\Controllers\Api\ProductController::class, 'tagList']);	
Route::get('/cart-items', [App\Http\Controllers\Api\ProductController::class, 'cartItems']);	
Route::get('/remove-from-cart', [App\Http\Controllers\Api\ProductController::class, 'removeFromCart']);	


// Optional Auth Routes
Route::group([ 'middleware' => 'optional_sanctum'], function()
{
	Route::get('/product/add-to-cart', [App\Http\Controllers\Api\ProductController::class, 'addToCart']);	
	Route::post('/product/update-cart-item', [App\Http\Controllers\Api\ProductController::class, 'updateCart']);	
	
});


// Must Auth Routes
Route::group([ 'middleware' => 'auth:sanctum', 'prefix' => 'auth'], function()
{
	Route::get('/user', [App\Http\Controllers\Api\AuthController::class, 'getUser']);	
	Route::get('/my-info', [App\Http\Controllers\Api\AuthController::class, 'myInfo']);	
	Route::get('/delete-card', [App\Http\Controllers\Api\AuthController::class, 'deleteCard']);	
	Route::post('/save-my-info', [App\Http\Controllers\Api\AuthController::class, 'saveMyInfo']);	
	Route::post('/save-card-info', [App\Http\Controllers\Api\AuthController::class, 'saveCardInfo']);	
	Route::post('/update-password', [App\Http\Controllers\Api\AuthController::class, 'updatePassword']);	
	
	Route::post('/checkout', [App\Http\Controllers\Api\PaymentController::class, 'placeOrder']);	
	Route::get('/my-orders', [App\Http\Controllers\Api\AuthController::class, 'myOrders']);	
	Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);

});



//Admin Apis ( Only Product Module Here, to Add Test Products )
Route::group([ 'prefix' => 'admin'], function()
{
	
	Route::post('/product/store', [App\Http\Controllers\Api\Admin\ProductController::class, 'productStore']);
	Route::post('/product-category/store', [App\Http\Controllers\Api\Admin\ProductController::class, 'categoryStore']);
	Route::post('/product-tag/store', [App\Http\Controllers\Api\Admin\ProductController::class, 'tagStore']);
});
