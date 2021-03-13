<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebController;

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

Route::get('/', [WebController::class , 'index']);
Route::get('/order',[PaymentController::class , 'order']); //user_id , amount,mobile_no
Route::get('/success',[WebController::class , 'success']);
Route::view('/error','error');
Route::post('/payment/status',[PaymentController::class , 'paymentCallback']);
Route::post('/contact' , [WebController::class , 'contact']);
Route::post('/newsletter' , [WebController::class , "newsletter"]);
