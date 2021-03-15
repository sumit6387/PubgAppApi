<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Functions\AllFunction;
use App\Models\Tournament;
use App\Models\History;
use App\Models\User;
use App\Models\Result;
use Validator;

class AdminController extends Controller
{
    public function user(Request $req){
        return Admin::where('email',$req->email)->get();
    }

    public function addTournament(Request $request){
        $valid = Validator::make($request->all(),[
            'prize_pool' => 'required',
            'winning' => 'required',
            'per_kill' => 'required',
            'entry_fee' => 'required',
            'tournament_name' => 'required',
            'img' => 'required',
            'type' => 'required',
            'maps' => 'required',
            'max_user_participated' => 'required',
            'tournament_start_date' => 'required',
            'tournament_start_time' => 'required',
        ]);
        if($valid->passes()){
            try{
                $new_tournament = new Tournament();
                $new_tournament->prize_pool = $request->prize_pool;
                $new_tournament->winning = $request->winning;
                $new_tournament->per_kill = $request->per_kill;
                $new_tournament->entry_fee = $request->entry_fee;
                $new_tournament->type = $request->type;
                $new_tournament->maps = $request->maps;
                $new_tournament->tournament_name = $request->tournament_name;
                $new_tournament->img = $request->img;
                $new_tournament->max_user_participated = $request->max_user_participated;
                $new_tournament->tournament_name = $request->tournament_name;
                $new_tournament->tournament_start_date = $request->tournament_start_date;
                $new_tournament->tournament_start_time = date('h:i a', strtotime($request->tournament_start_time));
                if($new_tournament->save()){
                    return response()->json([
                        'status' => true,
                        'msg' => 'Tournament Registered',
                        'url' => "tournament.html"
                    ]);
                }else{
                    return response()->json([
                        'status' => true,
                        'msg' => 'Some problemm occur! Try Again'
                    ]);
                }
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

    public function deleteTournament(Request $request){
        $tournament = Tournament::where('tournament_id',$request->tournament_id)->get()->first();
        if($tournament){
            $users = explode(',',$tournament->joined_user);
                if($tournament->joined_user != null){
                    foreach ($users as $key => $value) {
                        $user = User::where('id' , $value)->get()->first();
                        $user->wallet_amount = $user->wallet_amount + $tournament->entry_fee;
                        $user->save();
                        $notification = new AllFunction();
                        // send all user notification for cancelling who can participated in tournament  the match
                        $notification->sendNotification(array('id' => $value , 'title' => 'Match Canceled' ,'msg' => $tournament->tournament_name." canceled by Admin.",'icon'=> 'gamepad'));
                    }
                }
                History::where('tournament_id',$request->tournament_id)->update(['status' => 'past']);
                Tournament::where('tournament_id',$request->tournament_id)->update(['cancel' => 1]);
                return response()->json([
                    'status' => true,
                    'data' => 'Tournament Canceled'
                ]);
        }else{
            return response()->json([
                'status'=> false,
                'data'=> 'Something Went Wrong'
            ]);
        }
    }

    public function updateIdPassword(Request $request){
        $valid = Validator::make($request->all(),['tournament_id' => 'required','user_id' => 'required' , 'password' => 'required']);
        if($valid->passes()){
            // updating the room id and password
            $tournament = Tournament::where('tournament_id' , $request->tournament_id)->update(['room_id' => $request->user_id,'password' => $request->password]);
            $tournamentNotification = Tournament::where('tournament_id' , $request->tournament_id)->get()->first();
            if($tournamentNotification->joined_user != null){
                $user = explode(',',$tournamentNotification->joined_user);
                $notifi = new AllFunction();
                for ($i=0; $i < count($user); $i++) { 
                    $notifi->sendNotification(array("id" => $user[$i] , 'title' => "RoomID && Password" , "msg" => "RoomID and Password Updated.",'icon'=> 'gamepad'));
                    
                }
            }
            if($tournament){
                return response()->json([
                    'status' => true,
                    'msg' => 'UserId And Password Added',
                    'url' => "tournament.html"
                ]);
            }else{
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


    public function UpdateTournamentComplete(Request $req){
        $valid = Validator::make($req->all(),['tournament_id'=> 'required' , 'results' => 'required']);
        if($valid->passes()){
            // complete the tournament
            $result = new Result();
            $result->tournament_id = $req->tournament_id;
            $result->results = json_encode($req->results);
            $winner = $req->results;
            $prize  = new AllFunction();
            foreach ($winner as $key => $value) {
                //distributing prize
                $prize->prizeDistribution($value['user_id'],$value['kill'],$value['winner'],$req->tournament_id);
                if($value['winner'] == 1){
                    $result->winner_id = $value['user_id'];
                }
            }
            $result->save();
            History::where('tournament_id',$req->tournament_id)->update(['status' => 'past']);
            $data = Tournament::where('tournament_id',$req->tournament_id)->update(['completed' => 1]);
            if($data){
                return response()->json([
                    'status' => true,
                    'msg' => 'status updated'
                ]);
            }else{
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

    public function addmoney(Request $request){
        $valid = Validator::make($request->all() , ["mobile_no"=>"required" , "amount" => "required"]);
        if($valid->passes()){
            if($request->amount < 0){
                return response()->json([
                    'status' => false,
                    'msg' => "Enter Valid Amount."
                ]);
            }
            $user = User::where('mobile_no',$request->mobile_no)->get()->first();
            if($user){
                $user->wallet_amount = $user->wallet_amount + $request->amount;
                $user->save();
                return response()->json([
                    'status' => true,
                    'msg' => "Amount Added To User Account."
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'msg' => "Enter Registered Mobile No."
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'mag' => $valid->errors()->all()
            ]);
        }
    }
}
