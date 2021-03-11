<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\ShowController;


Route::post('/register' , [LoginController::Class , 'register']); //name , mobile_no,password,ref_by(optional)
Route::post('/login' , [LoginController::Class , 'login']); //mobile_no , password 

Route::group(["middleware" => 'auth:sanctum','api'],function(){
    Route::get('/user',[UserController::class , 'user']);
    Route::post('/joinTournament' , [UserController::class , 'joinTournament']); //tournament_id,pubg_username , pubg_userid
    Route::post('/addFeedback' , [UserController::class , 'addFeedback']); //title,description
    Route::post('/withdraw',[WithdrawController::class , 'withdraw']); //mode , upi_id,paytm_no,acount_no,ifsc_code,name
    Route::get('/showtournament/{page}', [ShowController::class , 'showTournaments']);
    Route::get('/allTransactions' , [ShowController::class , 'allTransactions']);
    Route::get('/referAndEarn' , [ShowController::class , 'referAndEarn']);
    Route::post('/tournamentDetail' , [ShowController::class , 'tournamentDetail']); //tournament_id
    Route::get('/usernames/{id}' , [ShowController::class , 'showUsername']); //tournament_id
    Route::get('/history/{type}',[ShowController::class , 'history']);//game,type ex live, past
});

Route::fallback(function(){
    return response()->json([
        'status' => false,
        'msg' => 'Route Not Found.'
    ]);
});