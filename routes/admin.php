<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminShowController;
use App\Http\Controllers\WithdrawController;

Route::post('/register',[AdminLoginController::class , 'register']);
Route::post('/login',[AdminLoginController::class , 'login']);

Route::group(["middleware" =>"CheckAdmin"],function(){
    Route::post('/addTournament' , [AdminController::class , 'addTournament']);

    Route::post('/updateIdPassword' , [AdminController::class , 'updateIdPassword']);
    Route::post('/delete_tournament',[AdminController::class , 'deleteTournament']);
    Route::post('/user',[AdminController::class,'user']);
    Route::post('/index',[AdminShowController::class , 'index']);
    Route::post('/showTournaments',[AdminShowController::class , 'showTournaments']);
    Route::post('/getDetail' , [AdminShowController::class , 'getDetail']);
    Route::post('/complete' , [AdminShowController::class , 'complete']);
    Route::get('/users',[AdminShowController::class , 'users']);
    Route::get('/withdraw' , [AdminShowController::class , 'withdraw']);
    Route::get('/withdrawDone/{id}' , [WithdrawController::class ,'withdrawDone']);
    Route::get('/tournamentsHistory' , [AdminShowController::class , 'tournamentsHistory']);
    Route::post('/UpdateTournamentComplete' , [AdminController::class ,'UpdateTournamentComplete']);
    Route::get('/contact',[AdminShowController::class , 'contact']);
    Route::post('/addmoney',[AdminController::class , "addmoney"]);
});

Route::fallback(function(){
    return response()->json([
        'status' => false,
        'msg' => 'Route Not Found'
    ]);
});



?>