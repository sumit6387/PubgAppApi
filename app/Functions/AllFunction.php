<?php 
namespace App\Functions;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\Tournament;
use Illuminate\Support\Str;
use Exception;

class AllFunction{
    public function referCode($ref_code){
        $ref = User::where('referal_code',$ref_code)->get()->first();
        if($ref){
            return $ref_code;
        }else{
            return null;
        }    
    }

    function generate_string($input, $strength = 5) {
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
     
        return $random_string;
    }

    public function transaction($rec_id,$amount,$desc){
        try{
         $trans = new Transaction();
         $trans->user_id = auth()->user()->id;
         $trans->reciept_id = $rec_id;
         $trans->amount = $amount;
         $trans->payment_id = Str::random(12);
         $trans->description = $desc;
         $trans->action = 'W';
         $trans->payment_done = 1;
         if($trans->save()){
             return true;
         }else{
             return false;
         }
       }catch(Exception $e){
          return false;
       }
     }

     public function prizeDistribution($id,$kill,$winner,$tournament_id){
        $users = User::where('id',$id)->get()->first();
        $tournament = Tournament::where('tournament_id',$tournament_id)->get()->first();
        $amount = $users->withdrawal_amount;
        $test = 0;
        $winn = 0;
        if($winner == 1 && $tournament->type == 'solo'){
            $amount = $amount + $tournament->winning;
            $test = 1;
            $winn = $winn+$tournament->winning;
        }else if($winner == 1 && $tournament->type == 'duo'){
             $amount = $amount + ($tournament->winning*50)/100;
             $test = 1;
             $winn = $winn+($tournament->winning*50)/100;
        }else if($winner == 1 && $tournament->type == 'squad'){
            $amount = $amount + ($tournament->winning*25)/100;
            $test = 1;
            $winn = $winn+($tournament->winning*25)/100;
        }
        $amount = $amount + ($tournament->per_kill * $kill);
        $winn = $winn+($tournament->per_kill * $kill);
        $users->withdrawal_amount = $amount;
        if($kill > 0){
                 $transaction = new Transaction();
                 $transaction->user_id = $id;
                 $transaction->reciept_id = Str::random(20);
                 $transaction->amount = $winn;
                 if($test == 1 ){
                     $transaction->description = 'For Winning Tournament';
                 }else{
                     $transaction->description = 'For Tournament Reward';
                 }
                 $transaction->payment_id = Str::random(10);
                 $transaction->action = 'C';
                 $transaction->payment_done = 1;
                 $transaction->save();
          }
             if($users->save()){
                 return true;
             }else{
                 return false;
             }
 }

 public function sendNotification($data){
    $notification =new Notification();
    $notification->user_id = $data['id'];
    $notification->title = $data['title'];
    $notification->message = $data['msg'];
    $notification->icon = $data['icon'];
    if($notification->save()){
        return true;
    }else{
        return false;
    } 
}

}

?>