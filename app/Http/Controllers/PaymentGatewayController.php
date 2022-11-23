<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Helper;

use Auth;
use App;
use Log;
use Lang;

class PaymentGatewayController extends Controller
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

    public static function orderPaymentf2f($txnId,$email='')
    {

        try
        {
            $memberId =  Auth::id();
            $currency = env('CURRENCY');
            $merchantID =env('PAYMENTGATEWAY_MERCHANT_ID');
            $secretKey = env('PAYMENTGATEWAY_SECRETKEY');

            $date = date('Y/m/d h:i:s a', time()); 
            $date = (int) filter_var($date, FILTER_SANITIZE_NUMBER_INT); 
            $paramEncrypt= [];
            
            //decrypt txnId 
            // $txnId = self::decrypt($txnId,$secretKey);

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


            $paramEncrypt['merchantId'] = $merchantID;
            $paramEncrypt['currencyCode'] = self::encrypt(isset($currency)?$currency:"MYR",$secretKey);
            $paramEncrypt['merchantOrderNo'] = self::encrypt($txnId,$secretKey);
            $paramEncrypt['orderAmount'] = self::encrypt(isset($amount)? number_format($amount, 2):"2",$secretKey);
            $paramEncrypt['memberId'] = self::encrypt($memberId,$secretKey);
            $paramEncrypt['email'] = self::encrypt(isset($_POST['email'])?$_POST['email']:"",$secretKey);


            $paramEncrypt['notifyUrl'] = env('PAYMENTGATEWAY_NOTIFYURL');
            $paramEncrypt['returnUrl'] = env('PAYMENTGATEWAY_RETURNURL');

            $signature = self::sign($paramEncrypt);

            $paramEncrypt['url'] = env('PAYMAMENTGATEWAY_F2F');
            $paramEncrypt['sign'] = $signature;
                 
            return $paramEncrypt;
        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            return [];
        }


    }



    public static function orderPaymentCyrto()
    {

        $merchantID =env('PAYMENTGATEWAY_MERCHANT_ID');
        $secretKey = env('PAYMENTGATEWAY_SECRETKEY');

        $url = env('PAYMAMENTGATEWAY_CW');

        $date = date('Y/m/d h:i:s a', time()); 
        $date = (int) filter_var($date, FILTER_SANITIZE_NUMBER_INT); 

        $params = [];

        $params['merchantId'] = $merchantID;
        $params['chainTypeId'] = self::encrypt(isset($_POST['chainTypeId'])?$_POST['chainTypeId']:"4", $secretKey);

        $signature = self::sign($params);

        $params['sign'] = $signature;

        $header = array('Content-Type: application/json');
            
        $response = Helper::postData($url,$params,$header);

        $response = json_decode($response);
        $newParms = [];



        $newParms['ChainTypeId'] = $response->{'ChainTypeId'};

        $newParms['WalletAddress'] = $response->{'WalletAddress'};

        $newParms['Success'] = $response->{'Success'};
        
/*        $newParms['depositHash'] = self::encrypt(1000,$secretKey);
        $newParms['withdrawalHash'] = self::encrypt(1000,$secretKey);
        $newParms['orderAmount'] = self::encrypt(1000,$secretKey);
        $newParms['merchantId'] = 2;
        $newParms['status'] = 1;

        $newParms['sign'] = $signature;*/
     


        return $newParms['WalletAddress'];


    }

    public static function sign($params)
    { 
         
         ksort($params); 
         $string = ""; 
         foreach($params as $value){ 
         $string .= $value; 
         } 
         
         $sha1Encrypt = sha1($string); 
         $encryptedstring = md5($sha1Encrypt); 
        
        return $encryptedstring; 
    }



    public static function encrypt($data, $secret) 
    { 
         //Take first 8 bytes of $key and append them to the end of $key. 
         $subkey = substr($secret, 0, 8); 
         // Encrypt data 
         $encData = openssl_encrypt($data, "DES-EDE3-CBC", $secret, OPENSSL_RAW_DATA,$subkey); 

         return bin2hex($encData); 
    }


    public static function decrypt($data, $secret) 
    { 

        //Take first 8 bytes of $key and append them to the end of $key. 
        $subkey = substr($secret, 0, 8); 
     
        // decrypt data 
        $decData = openssl_decrypt(hex2bin($data), "DES-EDE3-CBC", $secret, OPENSSL_RAW_DATA, $subkey);

        return $decData; 
    }
   


}
