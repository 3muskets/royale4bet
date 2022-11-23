<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Helper;

use Auth;
use App;
use Log;
use Lang;

class DNController extends Controller
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

    public static function payment($txnId)
    {
        DB::beginTransaction();

        try
        {
            $secretKey = env('PAYMENTGATEWAY_SECRETKEY');
            $memberId =  Auth::id();
            $custName = Auth::user()->username; // hardcode
            
            $clientId = env('DOITNOW_CLIENT_ID');
            $url= env('DOITNOW_PAYMENT_URL').'/payment';
            $secret= env('DOITNOW_CLIENT_SECRET');
            $returnUrl = 'http://dittogaming.com/api/doitnow/return';
            $callbackUrl = 'http://dittogaming.com/api/doitnow/callback';

            $data = DB::select("
                        SELECT id,amount
                        FROM member_dw
                        WHERE member_id = ? 
                        AND status = 'p'
                        AND id = ?
                        ORDER BY created_at
                        LIMIT 1",[$memberId,$txnId]);

            if(sizeof($data) != 0)
            {
                $amount = $data[0]->amount;
            }

            $hash = $clientId.$txnId.$custName.$amount.$returnUrl.$callbackUrl.$secret;

            
            $data = [
                'clientId' => $clientId
                ,'transactionId' => $txnId
                ,'custName' => $custName
                ,'amount' => $amount
                ,'returnUrl' => $returnUrl
                ,'callbackUrl' => $callbackUrl
                ,'hashVal' => hash('sha256', $hash)
                ,'url' => $url
                    ];

            return $data;
        }
        catch(\Exception $e)
        {
            DB::rollback();

            log::debug($e);
            return ['status' => 0];;
        }
    }

    public static function checkPaymentAPI(Request $request)
    {
        try 
        {
            $url = env('DOITNOW_PAYMENT_URL').'/check_payment';
            $clientId = env('DOITNOW_CLIENT_ID');
            $secret = env('DOITNOW_CLIENT_SECRET');

            // return  $url;

            // $txnId = $request['txn_id'];
            $txnId = 1000021;

            //db get memeber real name
            $custName = 'LEEMINWEN';

            $hash = hash('sha256', $clientId.$txnId.$custName.$secret);

            $data = [
                'status' => 1
                ,'url' => $url
                ,'clientId' => $clientId
                ,'transactionId' => $txnId
                ,'custName' => $custName
                ,'hashVal' => $hash
                    ];

            $url = $url.'?clientId='.$clientId.'&transactionId='.$txnId.'&custName='.$custName.'&hashVal='.$hash;

            $response = Helper::getData($url);
            $response = json_decode($response,true);
            return $response;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return '';
        }
    }

    public function return(Request $request)
    {
        try 
        {
            log::debug('return');
            log::debug($request->header());
            log::debug($request);
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            
        }
    }

    public function callback(Request $request)
    {
        try 
        {
            log::debug('callback');

            log::debug($request);
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            
        }
    }
}
