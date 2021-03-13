<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use PaytmWallet;
use paytm\checksum\PaytmChecksumLibrary;

class PaymentController extends Controller
{
    public function order(Request $request) {
      $payment = PaytmWallet::with('receive');
      $payment->prepare([
        'order' => rand(12345,93877887),
        'user' => "sumit6387",
        'mobile_number' => "6387577904",
        'email' => 'sumit@gmail.com',
        'amount' => 20,
        'callback_url' => url('/payment/status')
      ]);
      return $payment->receive();
    }


    public function paymentCallback()    //check status and update in database
    {   
      $transaction = PaytmWallet::with('receive');
      
      dd($transaction);
      $response = $transaction->response(); // To get raw response as array
      //Check out response parameters sent by paytm here -> http://paywithpaytm.com/developer/paytm_api_doc?target=interpreting-response-sent-by-paytm

      
      if($transaction->isSuccessful()){
        //Transaction Successful
      }else if($transaction->isFailed()){
        //Transaction Failed
      }else if($transaction->isOpen()){
        //Transaction Open/Processing
      }
      $transaction->getResponseMessage(); //Get Response Message If Available
      //get important parameters via public methods
      $transaction->getOrderId(); // Get order id
      $transaction->getTransactionId(); // Get transaction id
    }
}
