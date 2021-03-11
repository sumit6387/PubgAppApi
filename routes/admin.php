<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;

Route::post('/register',[AdminLoginController::class , 'register']);
Route::post('/login',[AdminLoginController::class , 'login']);


Route::fallback(function(){
    return response()->json([
        'status' => false,
        'msg' => 'Route Not Found'
    ]);
});



?>