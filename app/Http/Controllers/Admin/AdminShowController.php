<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tournament;
use App\Models\Transaction;
use App\Models\Withdraw;
use App\Models\UserName;
use App\Models\ContactUs;

class AdminShowController extends Controller
{
    public function index(){
        $completedTournament = Tournament::where('completed',1)->get()->count();
        $canceledTournament = Tournament::where('cancel',1)->get()->count();
        $totalUsers = User::get()->count();
        $totalTransaction = Transaction::where('razorpay_id',"!=",null)->where('payment_done',1)->get()->count();
        $users = User::orderby('id','desc')->take(5)->get();
        $data = array('completedTournament' => $completedTournament , 'canceledTournament' =>$canceledTournament , 'totalUsers' => $totalUsers , 'totalTransaction' => $totalTransaction , 'users' => $users);
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function showTournaments(){
        $tournaments = Tournament::where(['completed'=> 0 ,'cancel'=> 0])->get();
        if($tournaments->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $tournaments
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'Add Tournaments'
            ]);
        }
    }

    public function getDetail(Request $request){
        $tournament = Tournament::select(['room_id','password'])->where('tournament_id',$request->tournament_id)->get()->first();
        if($tournament){
            return response()->json([
                'status' => true,
                'data' => $tournament
            ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' => "No Data Found!!"
            ]);
        }
    }

    public function complete(Request $request){
        $tournament = Tournament::where('tournament_id',$request->tournament_id)->get()->first();
        if($tournament->count() > 0){
            if($tournament->joined_user != null){
                $arr = explode(',',$tournament->joined_user);
                $data = array();
                for ($i=0; $i < count($arr); $i++) { 
                    $user = User::select('name')->where('id',$arr[$i])->get()->first();
                    $data1 = UserName::select('pubg_username','pubg_user_id','user_id')->where(['user_id' => $arr[$i] , 'tournament_id' => $request->tournament_id])->get()->first();
                    $user->id = $data1->user_id;
                    $user->pubg_username = $data1->pubg_username;
                    $user->pubg_user_id = $data1->pubg_user_id;
                    array_push($data,$user);
                }
                return response()->json([
                    'status' => true,
                    'data' => $data,
                    'type' => $tournament->type
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'data' => 'You have No Users To complete the Tournament'
                ]);
            }
        }
    }

    public function users(){
        $user = User::orderby('id' , 'desc')->paginate(10);
        return response()->json([
            'status' => true,
            'data' => $user
        ]);
    }

    public function withdraw(){
        $withdraw_record = Withdraw::where('completed',0)->get();
        if($withdraw_record->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $withdraw_record
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'No Records'
            ]);
        }
    }

    public function tournamentsHistory(){
        $data = Tournament::where('completed',1)->orwhere('cancel',1)->get();
        if($data->count() > 0){
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }
    }

    public function contact(Request $request){
        $contacts = ContactUs::orderby('id','desc')->take(20)->get();
        if($contacts->count()){
            return response()->json([
                'status' => true,
                'data' => $contacts
            ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' => "No Data Found!!"
            ]);
        }
    }
}
