<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Functions\AllFunction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Admin;
use Validator;
use Session;

class AdminLoginController extends Controller
{
    public function register(Request $request){
        $valid = Validator::make($request->all(),["name" => "required" , "email" => "required" , "password" => "required"]);
        if($valid->passes()){
            $new_admin = new Admin();
            $new_admin->name = $request->name;
            $new_admin->email = $request->email;
            $new_admin->password = Hash::make($request->password);
            if($new_admin->save()){
                return response()->json([
                    'status' => true,
                    'msg' => "Admin Registered Successfully."
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "Something Went Wrong. Try Again!!"
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
    }

    public function login(Request $request){
        $valid = Validator::make($request->all() , ["email" => "required" , "password" => "required"]);
        if($valid->passes()){
            $admin = Admin::select(['email','name','password','id'])->where('email' ,$request->email)->get()->first();
            if($admin){
                if(Hash::check($request->password , $admin->password)){
                    Session::put('email',$request->email);
                    return response()->json([
                        'status' => true,
                        'data' => $admin
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => 'Enter Correct Password.'
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Enter Correct Email.'
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
    }
}
