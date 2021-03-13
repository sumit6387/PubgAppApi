<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserName;
use App\Models\History;
use App\Models\Feedback;
use App\Functions\AllFunction;
use Illuminate\Support\Str;
use Exception;
use Validator;

class UserController extends Controller
{
    public function user(){
        return auth()->user();
    }

    public function joinTournament(Request $request){
        $valid = Validator::make($request->all(),['pubg_username' => 'required' ,'pubg_userid' =>'required']);
        if($valid->passes()){
        try{
            // user join tournament
            $tournament = Tournament::where('tournament_id',$request->tournament_id)->get()->first();
            $notifi = new AllFunction();
            $arr = explode(',',$tournament->joined_user);
            if(sizeof($arr) == $tournament->max_user_participated){
                return response()->json([
                    'status' => false,
                    'msg' => 'Max Limit exceeded. Join Another Tournament'
                ]);
            }
            $user = User::where('id',auth()->user()->id)->get()->first();
            if($user->wallet_amount < $tournament->entry_fee){
                return response()->json([
                    'status' => false,
                    'msg' => "Insufficient balance"
                ]);
            }
            $joined_user = $tournament->joined_user;
            if($joined_user == null){
                $joined_user = ''.auth()->user()->id;
            }else {
                $arr = explode(',',$joined_user);
                $resp = in_array(auth()->user()->id , $arr);
                if($resp){
                    return response()->json([
                        'status' => false,
                        'msg' => 'You Already Joined The Tournament'
                    ]);
                }
            $joined_user = $joined_user.','.auth()->user()->id;
            }
            $tournament1 = Tournament::where('tournament_id',$request->tournament_id)->update(['joined_user' => $joined_user]);
            $user->wallet_amount = $user->wallet_amount - $tournament->entry_fee;
            $user->update();
            $transaction = new Transaction();
            $transaction->user_id = auth()->user()->id;
            $transaction->reciept_id = Str::random(20);
            $transaction->amount = $tournament->entry_fee;
            $transaction->description = 'For join the '.$tournament->game_type.' Tournament';
            $transaction->payment_id = Str::random(10);
            $transaction->action = 'D';
            $transaction->payment_done = 1;
            $transaction->save();
            $username = new UserName();
            $username->user_id = auth()->user()->id;
            $username->tournament_id = $request->tournament_id;
            $username->pubg_username = $request->pubg_username;
            $username->pubg_user_id = $request->pubg_userid;
            $username->save();
            $history = new History();
            $history->user_id = auth()->user()->id;
            $history->tournament_id = $request->tournament_id;
            $history->status = 'live';
            $history->save();
            $resp = $notifi->sendNotification(array("id" => auth()->user()->id , 'title' => "Join Tournament" , "msg" => "You Joined The ".$tournament->tournament_name." Tournament.",'icon'=> 'gamepad'));
            return response()->json([
                'status' => true,
                'msg' => 'You Joined The Tournament'
            ]);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'msg' => 'Something Went Wrong'
            ]);
        }
    }else{
            return response()->json([
                'status' => false,
                'msg' => $valid->errors()->all()
            ]);
        }
    }
    public function addFeedback(Request $request){
        $valid = Validator::make($request->all() , ["title" => "required" , "description" => "required"]);
        if($valid->passes()){
            $username = User::where('id',auth()->user()->id)->get()->first()->name;
            $feedback = new Feedback();
            $feedback->user_id = auth()->user()->id;
            $feedback->user_name = $username;
            $feedback->title = $request->title;
            $feedback->description = $request->description;
            if($feedback->save()){
                return response()->json([
                    'status' => true,
                    'msg' => "Feedback Submitted"
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "Something Went Wrong"
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
