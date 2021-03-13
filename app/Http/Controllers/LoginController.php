<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Functions\AllFunction;

class LoginController extends Controller
{
    public function register(Request $request){
        $valid = Validator::make($request->all(),["name" => "required" , "mobile_no" => "required" , "password" => "required"]);
        if($valid->passes()){
            $exist = User::where('mobile_no',$request->mobile_no)->get()->first();
            if($exist){
                return response()->json([
                    'status' => false,
                    'msg' => "You Are Already Registered."
                ]);
            }
            $new_user = new User();
            $new_user->name = $request->name;
            $new_user->mobile_no = $request->mobile_no;
            $new_user->password = Hash::make($request->password);
            $new_user->profile_img = 'https://ui-avatars.com/api/?name='.$request->name;
            $permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $checkref = new AllFunction();
            $referal_code = $checkref->generate_string($permitted_chars, 5);
            $new_user->referal_code = $referal_code;
            if($request->ref_by){
                $code = $checkref->referCode($request->ref_by);
                if($code){
                    $new_user->ref_by = $request->ref_by;
                }
            }
            if($new_user->save()){
                return response()->json([
                    'status' => true,
                    'msg' => "User Registered Successfully!!"
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "Something Went Wrong!!"
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
        $valid = Validator::make($request->all(),["mobile_no" => "required" , "password" => "required"]);
        if($valid->passes()){
            $user = User::where('mobile_no',$request->mobile_no)->get()->first();
            if($user){
                if(Hash::check($request->password , $user->password)){
                    $token = $user->createToken('my-app-token')->plainTextToken;
                    return response()->json([
                        'status' => true,
                        'token' => $token
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'msg' => "Enter Correct Password!!"
                    ]);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => 'Enter registered mobile no!!'
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
