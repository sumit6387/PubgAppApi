<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeedBack;
use App\Models\ContactUs;
use App\Models\NewsLetter;
class WebController extends Controller
{
    public function index(){
        $data['feedbacks'] = FeedBack::select(['feedback.*','users.profile_img as img'])->orderby('feedback.id' , 'desc')->join('users','feedback.user_id','=','users.id')->take(5)->get();
        return view('index',$data);
    }

    public function success(){
        return view('success');
    }

    public function contact(Request $request){
        $valid = Validator::make($request->all() , ["name" => 'required' , "subject" => "required" , "message" => "required","email" => "required"]);
        if($valid->passes()){
            $new = new ContactUs();
            $new->name = $request->name;
            $new->subject = $request->subject;
            $new->email = $request->email;
            $new->message = $request->message;
            $new->save();
          return response()->json([
              'status' => true
          ]);
        } else{
          return response()->json([
              'status' => false,
              "msg" => $valid->errors()->all()
          ]);
        }
      }

      public function newsletter(Request $request){
        $valid = Validator::make($request->all() , ["email" => "required" ]);
        if($valid->passes()){
          $newsletter = new NewsLetter();
          $newsletter->email = $request->email;
          $newsletter->save();
          return response()->json([
            'status' => true
          ]);
        }else{
          return response()->json([
            'status' => false,
            "msg" => $valid->errors()->all()
          ]);
        }
      }
}
