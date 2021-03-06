<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Validator;
use App\Functions\AllFunction;
use Razorpay\Api\Api;
use Exception;

class PaymentController extends Controller
{
    private $razorpayId = "rzp_live_I2trrAOkwjhXkK"; //test keys
    private $razorpayKey = "8kBmczPTuvtO8vYjCMqZe8XR"; // test keys

    public function createPaymentOrder(Request $request){
        $valid = Validator::make($request->all(), ['amount' => 'required']);
        if($valid->passes()){
          try{
              if($request->amount < 10){
                  return response()->json([
                      'status' => false,
                      'msg' => "You can't add less then 10 RS"
                  ]);
              }
            //   creating the order for money transfer
                $api = new Api($this->razorpayId, $this->razorpayKey);
                $reciept_id = Str::random(20);
                $order = $api->order->create(array(
                    'receipt' => $reciept_id,
                    'amount' => $request->amount * 100,
                    'currency' => 'INR'
                    )
                );
                $newTransaction = new Transaction();
                $newTransaction->user_id = auth()->user()->id;
                $newTransaction->reciept_id = $reciept_id;
                $newTransaction->amount = $request->amount;
                $newTransaction->description = "Adding Amount";
                $newTransaction->action = "C";
                $newTransaction->payment_id = $order['id'];
                $newTransaction->save();

                return response()->json([
                    'status' => true,
                    'razorpayID' => $this->razorpayId,
                    'orderID' => $order['id'],
                    'amount' => $request->amount *100,
                    'email' => "utkarshyadav6387@gmail.com",
                    'userID' => auth()->user()->id,
                    'contact' => auth()->user()->mobile_no,
                    'name' => auth()->user()->name
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

    public function paymentComplete(Request $request){
      $valid = Validator::make($request->all(), [
          'razorpay_payment_id' => 'required',
          'razorpay_order_id' => 'required',
          'razorpay_signature' => 'required'
      ]);

      if($valid->passes()){
          // verifying the payment is completed or not
          $completeStatus = $this->verifySignature($request->razorpay_payment_id,$request->razorpay_order_id,$request->razorpay_signature);
       
          if($completeStatus){
              $transaction = Transaction::where('payment_id' , $request->razorpay_order_id)->get()->first();
              $transaction->payment_done  = 1;
              $transaction->razorpay_id  = $request->razorpay_payment_id;
              $transaction->save();
              $user = User::where('id' , auth()->user()->id)->get()->first();
              $user->wallet_amount = $user->wallet_amount + $transaction->amount;
              $user->first_time_payment = 1;
              $user->save();
              $notifi = new AllFunction();
              // sending the notification to the user
              $notifi->sendNotification(array('id' => auth()->user()->id ,'title' => 'Money Added' , 'msg' => $transaction->amount.'is added to your account','icon'=> 'money'));
              $noOfTransaction = Transaction::where(['user_id'=>auth()->user()->id])->where('razorpay_id','!=',null)->get();
              if($noOfTransaction->count() == 0 && $user->ref_by != null){
                  //adding 50% amount on first transaction of users  this is for refer and earn
                  $users = User::where('refferal_code',$user->ref_by)->get()->first();
                  $users->wallet_amount = $users->wallet_amount + ($transaction->amount * 50) / 100;
                  // sending the notification to the user
                  $notifi->sendNotification(array('id' => $users->user_id ,'title' => 'Reffering Added' , 'msg' => 'Your '.($transaction->amount * 50 / 100).' money of reffering in your account','icon'=> 'money'));
                  $newTransaction = new Transaction();
                  $newTransaction->user_id = $users->user_id;
                  $newTransaction->reciept_id = Str::random(12);
                  $newTransaction->amount = ($transaction->amount * 50)/100;
                  $newTransaction->description = "Referal Friend Added Money You Get 5rs of your friends added money first time";
                  $newTransaction->action = "C";
                  $newTransaction->payment_done = 1;
                  $newTransaction->payment_id = Str::random(20);
                  $newTransaction->save();
                  $users->save();
                  $user_info = User::where('id' , auth()->user()->id)->get()->first();
                  $user_info->first_time_payment = 1;
                  $user_info->save();

              }

              return response()->json([
                  'status' => true,
                  'msg' => 'Payment Success'
              ]);

          }else{
              return response()->json([
                  'status' => false,
                  'msg' => 'Something went wrong'
              ]);
          }
      }else{
          return response()->json([
              'status' => false,
              'msg' => $valid->errors()->all()
          ]);
      }
  }

  private function verifySignature($razorpay_payment_id, $razorpay_order_id,$signature){
      // verifying the payment through this function
     try{
      $api = new Api($this->razorpayId, $this->razorpayKey);
      $attributes  = array('razorpay_signature'  => $signature,  'razorpay_payment_id'  => $razorpay_payment_id ,  'razorpay_order_id' => $razorpay_order_id);
      $order  = $api->utility->verifyPaymentSignature($attributes);
      return true;
    }catch(Exception $e){
        return false;
    }
  }
    
}
