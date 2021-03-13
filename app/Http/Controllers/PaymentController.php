<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use PaytmWallet;
use Illuminate\Support\Facades\Redirect;
// use paytm\paytmchecksum\PaytmChecksum;
use Validator;

class PaymentController extends Controller
{
    public function order(Request $request) {
        $order_id = Str::random(12);
        $transaction = new Transaction();
        $transaction->user_id = $request->user_id;
        $transaction->reciept_id = Str::random(10);
        $transaction->amount = $request->amount;
        $transaction->description = "Adding Money To Your Account.";
        $transaction->payment_id = $order_id;
        $transaction->action = "C";
        $transaction->save();
        $payment = PaytmWallet::with('receive');
        $payment->prepare([
          'order' => $order_id, // your order id taken from cart
          'user' =>$request->user_id, // your user id
          'mobile_number' => $request->mobile_no, // your customer mobile no
          'email' => 'utkarshyadav6387@gmail.com', // your user email address
          'amount' => $request->amount, // amount will be paid in INR.
          'callback_url' => url('/payment/status') // callback URL
        ]);
        return $payment->receive();
  }


    public function paymentCallback(Request $request)    //check status and update in database
    {   
        $transaction = PaytmWallet::with('receive');
        $response = $transaction->response(); 
        $order_id = $transaction->getOrderId();
        $transaction_update = Transaction::where('payment_id',$order_id)->get()->first();
        $transaction_id = $transaction->getTransactionId(); // Get transaction id
        if($transaction->isSuccessful()){
          $transaction_update->razorpay_id = $transaction_id;
          $transaction_update->payment_done = 1;
          $transaction_update->save();
          return Redirect::to(url('/success'));
        }else if($transaction->isFailed()){
          $transaction_update->razorpay_id = $transaction_id;
          $transaction_update->save();
          return Redirect::to(url('/error'));
        }else if($transaction->isOpen()){
          return Redirect::to(url('/'));
        }
    }

    // public function order(Request $request){
    //   $mid = env('PAYTM_MERCHANT_ID');
    //   $paytmParams = array();
    //   $order =strtotime(date('y-m-d')). rand(000000,9999999);
    //   $paytmParams["body"] = array(
    //       "requestType"   => "Payment",
    //       "mid"           => $mid,
    //       "websiteName"   => "WEBSTAGING",
    //       "orderId"       => $order,
    //       "callbackUrl"   => url('/payment/status'),
    //       "txnAmount"     => array(
    //           "value"     => $request->amount,
    //           "currency"  => "INR",
    //       ),
    //       "userInfo"      => array(
    //           "custId"    => auth()->user()->id,
    //       ),
    //   );
      
    //   /*
    //   * Generate checksum by parameters we have in body
    //   * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
    //   */
    //   $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $mid);
    //   $paytmParams["head"] = array(
    //       "signature"    => $checksum,
    //       "channelId" =>  env('PAYTM_CHANNEL')
    //   );
      
    //   $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
      
    //   /* for Staging */
    //   $url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=".$mid."&orderId=".$order;
    //   return $url;
      
    //   /* for Production */
    //   // $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=YOUR_MID_HERE&orderId=ORDERID_98765";
    //   $ch = curl_init($url);
    //   curl_setopt($ch, CURLOPT_POST, 1);
    //   curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    //   curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); 
    //   $response = curl_exec($ch);
    //   return $response;
    // }
}
