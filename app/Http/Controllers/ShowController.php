<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tournament;
use App\Models\Transaction;
use App\Models\UserName;
use App\Models\Notification;
use App\Models\User;

class ShowController extends Controller
{
    public function showTournaments($type,$page){
        $tournaments = Tournament::orderby('tournament_id','desc')->where(['completed'=>0 , 'cancel' => 0,'type'=>$type])->get();
        if($tournaments->count()){
            $data = [];
            foreach ($tournaments as $key => $value) {
                array_push($data , $value);
                $key = count($data) -1;
                if($value->joined_user != null){
                   $joined_user = count(explode(",",$value->joined_user));
                }else{
                    $joined_user = 0;
                }
                $data[$key]->joined_user = $joined_user;
            }

            if($page == 1){
                $start_data = 1;
            }else{
                $start_data = $page *10 + 1;
            }
            return response()->json([
                'status' => true,
                'data'  => collect($data)->forPage($start_data ,10)
            ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' => "No Data Found!!"
            ]);
        }
    }

    public function allTransactions(){
        $transaction = Transaction::select('transactions.amount','transactions.description','transactions.action','transactions.created_at')->orderby('id','desc')->where(['user_id'=>auth()->user()->id , 'payment_done' => 1]);
        if($transaction->get()->count()){
            return response()->json([
                'status' => true,
                'data' => $transaction->paginate(10)
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'You have no transactions'
            ]);
        }
    }

    public function referAndEarn(){
        $detail = User::where('id' , auth()->user()->id)->get()->first()->referal_code;
        $users = User::where('ref_by' , $detail)->get();
        $user_payment = User::where(['ref_by'=> $detail,'first_time_payment' => 1])->get();
        $data = array('referal_code'=>$detail,'user_uses_code' =>$users->count(),'first_payment_added' => $user_payment->count());
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function tournamentDetail(Request $req){
        $data = Tournament::where(['tournament_id' => $req->tournament_id])->get()->first();
        if($data){
            if($data->joined_user != null){
                $arr = explode(',',$data->joined_user);
                $data->joined_user = sizeof(explode(',',$data->joined_user));
                for ($i=0; $i < count($arr) ; $i++) { 
                    if($arr[$i] == auth()->user()->id){
                        $data->joinas = true;
                    }
                }
            }else{
                $data->joinas = false;
                $data->joined_user = 0;
            }
            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'Something Went Wrong'
            ]);
        }
    }

    public function showUsername($id){
        $data = Tournament::where('tournament_id',$id)->get()->first();
        $arr = explode(',',$data->joined_user);
        $usernames = array();
        if($data->joined_user != null){
        for ($i=0; $i < sizeof($arr); $i++) { 
            $username = UserName::select(['usernames.pubg_username','users.profile_img as img'])->where(['usernames.user_id' => $arr[$i] , 'usernames.tournament_id' => $id])->join('users','usernames.user_id','=','users.id')->get()->first();
            $username->name = auth()->user()->name;
            array_push($usernames,$username);
        }
        return response()->json([
            'status' => true,
            'data'=> $usernames
        ]);

    }else{
            return response()->json([
                'status' => false,
                'msg' => 'No User Participated'
            ]);
        }
    }

    public function history($type){
        $data = Tournament::select(['tournaments.tournament_name','tournaments.max_user_participated','tournaments.maps','tournaments.entry_fee','tournaments.prize_pool','tournaments.tournament_id','tournaments.joined_user','tournaments.per_kill','tournaments.img','tournaments.tournament_start_time','history.*'])->orderby('tournaments.tournament_id' , 'desc')->where(['history.status'=>$type,'history.user_id' => auth()->user()->id])->join('history','tournaments.tournament_id','=','history.tournament_id');

        if($data->get()->count()){
            return response()->json([
                'status' => true,
                'data' => $data->paginate(10)
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 'You have no history'
            ]);
        }
    }

    public function numberOfNotification(){
        $no_of_notification = Notification::where(['user_id' => auth()->user()->id , 'seen' => 0])->get()->count();
        if($no_of_notification){
            return response()->json([
                'status' => true,
                'data' => $no_of_notification
            ]);
        }else{
            return response()->json([
                'status' => false,
                'data' => 0
            ]);
        }
    }

    public function notification(){
        $notifications  = Notification::orderby('id','desc')->where('user_id',auth()->user()->id);
        if($notifications->get()->count()){
            return response()->json([
                'status'=> true,
                'data' => $notifications->paginate(10)
            ]);
        }else{
            return response()->json([
                'status'=> false,
                'data' => 'You have no notification'
            ]);
        }
    }

    public function updateSeen(){
        $notifi = Notification::where('user_id',auth()->user()->id)->update(['seen' => 1]);
        if($notifi){
            return response()->json([
                'status' => true
            ]);
        }else{
            return response()->json([
                'status' => false
            ]);
        }
    }

    public function pointTableUser(){
        $users = User::orderBy('ptr_reward','desc')->take(20)->get();
        if($users){
            return response()->json([
                'status' =>true,
                'data' => $users
            ]);
        }else{
            return response()->json([
                'status' =>false,
                'data' => "No User Found"
            ]);
        }
        
    }
}
