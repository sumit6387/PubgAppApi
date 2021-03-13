<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
class WebController extends Controller
{
    public function index(){
        $data['feedbacks'] = Feedback::select(['feedback.*','users.profile_img as img'])->orderby('feedback.id' , 'desc')->join('users','feedback.user_id','=','users.id')->take(5)->get();
        return view('index',$data);
    }
}
