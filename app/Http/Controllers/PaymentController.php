<?php

namespace App\Http\Controllers;

use App\PaymentTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Display view page
     */
    public function index()
    {
        return view('make-payment');
    }

    /**
     * Make payment
     * @param \Illuminate\Http\Request $request
     */
    public function payment(Request $request)
    {
        // validation
        $this->validate($request, [
            'phone_number' => 'required|integer|starts_with:254',
            'amount' => 'required|integer',
        ]);
        // perform stk push
        $response_code = $this->mpesaSTKPush($request->amount, $request->phone_number);

        if ($response_code == 0) {
            return redirect('/')->with('success', 'Type in your Mpesa PIN in your mobile phone');
        } else {
            return redirect('/')->with('error', 'STK Push failed try again with correct credentials');
        }
    }

    /**
     * View payments done
     */
    public function transactions()
    {
        $payment_transactions = PaymentTransaction::all();

        return view('payment-transactions', ['payment_transactions' => $payment_transactions]);
    }

    /**
     * mpesa callback (used by Mpesa API)
     * 
     * @param \Illuminate\Http\Request $request
     */
    public function mpesaCallback(Request $request)
    {
        // save mpesa transaction
        $callback_content = json_decode($request->getContent());
        // ResultCode 0 (paid)
        if ($callback_content->Body->stkCallback->ResultCode == 0) {
            $callback_items = $callback_content->Body->stkCallback->CallbackMetadata->Item;
            //save mpesa transaction
            $payment_transaction = new PaymentTransaction();
            $payment_transaction->amount = $callback_items[0]->Value;
            $payment_transaction->receipt_number = $callback_items[1]->Value;
            $payment_transaction->transaction_date = $callback_items[3]->Value;
            $payment_transaction->phone_number = $callback_items[4]->Value;
            $payment_transaction->save();
        }
        return response()->json([
            'ResponseCode' => 0,
            'ResponseDescription' => "success"
        ], 200);
    }

    /**
     * Generate Mpesa access token
     */
    public function generateAccessToken()
    {
        $consumer_key = config('app.mpesa_consumer_key');
        $consumer_secret = config('app.mpesa_consumer_secret');
        $credentials = base64_encode($consumer_key . ":" . $consumer_secret);

        $url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curl_response = curl_exec($curl);
        $access_token = json_decode($curl_response);

        return $access_token->access_token;
    }

    /**
     * Lipa na mpesa password
     */
    public function lipaNaMpesaPassword()
    {
        $timestamp = Carbon::rawParse('now')->format('YmdHms');
        $passkey = config('app.mpesa_passkey');
        $lipa_na_mpesa_password = base64_encode(config('app.mpesa_business_shortcode') . $passkey . $timestamp);
        return $lipa_na_mpesa_password;
    }

    /**
     * perform STK push 
     * 
     * @param int $amount
     * @param int $phone_number
     * 
     * @return int $response_code
     */
    public function mpesaSTKPush(int $amount, int $phone_number)
    {
        $stk_push_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $stk_push_callback_url = config('app.mpesa_callback_url') . '/callback-payment';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $stk_push_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Bearer ' . $this->generateAccessToken()));
        $curl_post_data = [
            'BusinessShortCode' => config('app.mpesa_business_shortcode'),
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone_number,
            'PartyB' => config('app.mpesa_business_shortcode'),
            'PhoneNumber' => $phone_number,
            'CallBackURL' => $stk_push_callback_url,
            'AccountReference' => "payment test",
            'TransactionDesc' => "STKPush test"
        ];
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);
        //get the response code
        $curl_data = json_decode($curl_response);
        $response_code =  $curl_data->ResponseCode;
        return $response_code;
    }
}
