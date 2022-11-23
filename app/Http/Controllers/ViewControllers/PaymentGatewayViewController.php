<?php

namespace App\Http\Controllers\ViewControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\Helper;
use Auth;
use DB;
use Log;

class PaymentGatewayViewController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        return view('payment-gateway');
    }

    public function orderPaymentf2f(Request $request)
    {

        $currency = 'MYR';
        $txnId = $request->input('txn_id');

        $email = '';

        $memberId = Auth::id();
        
        $paramEncrypt = PaymentGatewayController::orderPaymentf2f($memberId,$txnId,$currency,$email);

        $url = env('PAYMAMENTGATEWAY_F2F');

        return view('payment-gateway-redirect')->with(['url'=>$url,'paramEncrypt'=>$paramEncrypt]);
 
/*
        $paramEncrypt = PaymentGatewayController::orderPaymentCyrto();

        log::Debug($paramEncrypt);*/
    }
}
