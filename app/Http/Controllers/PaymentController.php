<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use PaytmWallet;
use paytm\checksum\PaytmChecksumLibrary;
use Validator;

class PaymentController extends Controller
{
//     public function order(){
//       $mid = env('PAYTM_MERCHANT_ID');
//       $order = rand(00000,99999);
//       $body = '{"mid":"","orderId":"'.$order.'"}';

// /**
// * Generate checksum by parameters we have in body
// * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
// */
//         $paytmChecksum = PaytmChecksum::generateSignature($body, 'YOUR_MERCHANT_KEY');  
//         return sprintf("generateSignature Returns: %s\n", $paytmChecksum);
//     }










    public function order(Request $request) {
      $valid = Validator::make($request->all(),["amount" => "required",]);
      if($valid->passes()){
        $order_id = Str::random(10);
        $transaction = new Transaction();
        $transaction->user_id = auth()->user()->id;
        $transaction->reciept_id = Str::random(12);
        $transaction->amount = $request->amount;
        $transaction->description = "Adding Money To Your Account.";
        $transaction->payment_id = $order_id;
        $transaction->action = "C";
        $transaction->save();
        $payment = PaytmWallet::with('receive');
        $payment->prepare([
          'order' => $order_id, // your order id taken from cart
          'user' =>auth()->user()->id, // your user id
          'mobile_number' => auth()->user()->mobile_no, // your customer mobile no
          'email' => 'utkarshyadav6387@gmail.com', // your user email address
          'amount' => $request->amount, // amount will be paid in INR.
          'callback_url' => url('/payment/status') // callback URL
        ]);
        $output = array("CHECKSUMHASH"=> $payment->receive()['checkSum'] , 'REQUEST_TYPE'=>$payment->receive()['params']['REQUEST_TYPE'] , "MID" => $payment->receive()['params']['MID'] , "ORDER_ID"=>$payment->receive()['params']['ORDER_ID'],"CUST_ID"=>$payment->receive()['params']['CUST_ID'],"INDUSTRY_TYPE_ID"=>$payment->receive()['params']['INDUSTRY_TYPE_ID'],"CHANNEL_ID"=>$payment->receive()['params']['CHANNEL_ID'],"TXN_AMOUNT"=>$payment->receive()['params']['TXN_AMOUNT'],"WEBSITE"=>$payment->receive()['params']['WEBSITE'],"CALLBACK_URL"=>$payment->receive()['params']['CALLBACK_URL'],"MOBILE_NO"=>$payment->receive()['params']['MOBILE_NO'],"EMAIL"=>$payment->receive()['params']['EMAIL'],"CHECKSUMHASH"=>$payment->receive()['checkSum']);
        return response()->json([
          'status' => true,
          'data' => $output
          ]);
          }else{
            return response()->json([
              'status' => false,
              'msg' => $valid->errors()->all() 
            ]);
          }
    }


    public function paymentCallback(Request $request)    //check status and update in database
    {   
     $url = 'https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction';
     
     $response = Http::post($url, [
          "Mid" => $request->MID,
          "OrderId" => $request->ORDER_ID,
          "CHECKSUMHASH"=> $request->CHECKSUMHASH,
          "REQUEST_TYPE"=> $request->REQUEST_TYPE,
          "CUST_ID"=> $request->CUST_ID,
          "INDUSTRY_TYPE_ID"=>$request->INDUSTRY_TYPE_ID,
          "CHANNEL_ID"=> $request->CHANNEL_ID,
          "TXN_AMOUNT"=> $request->TXN_AMOUNT,
          "WEBSITE"=> $request->WEBSITE,
          "CALLBACK_URL"=> $request->CALLBACK_URL,
          "MOBILE_NO"=> $request->MOBILE_NO,
          "EMAIL"=> $request->EMAIL
          ]);
          $transaction = PaytmWallet::with('receive');
          return $transaction;
        $response = $transaction->response(); // To get raw response as array
        //Check out response parameters sent by paytm here -> http://paywithpaytm.com/developer/paytm_api_doc?target=interpreting-response-sent-by-paytm
        if($transaction->isSuccessful()){
          // dd("success");
        }else if($transaction->isFailed()){
          // dd("failled");
        }else if($transaction->isOpen()){
          // dd("pending");
        }
        print_r($transaction->getResponseMessage()); //Get Response Message If Available
        //get important parameters via public methods
        // print_r($transaction->getOrderId()); // Get order id

        print_r($transaction->getTransactionId()); // Get transaction id
    }
}
