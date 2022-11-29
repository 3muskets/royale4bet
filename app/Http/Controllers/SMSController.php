<?php

namespace App\Http\Controllers;

use Twilio\Jwt\ClientToken;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use DB;
use Log;

class SMSController extends Controller
{
    //

    public function store(Request $request)
    {
    	DB::beginTransaction();

    	try
    	{
    		$mobile = $request->input('mobile');
    		$code = rand(1000, 9999);

    		$request['code'] = $code;

    		DB::insert("INSERT INTO sms_verification(mobile,code,created_at) VALUES(?,?,NOW())",[$mobile,$code]);

    		DB::commit();

    		self::sendSms($request);
    	}
    	catch(\Exception $e)
    	{
    		DB::rollback();

    		log::debug($e);
    		return [];
    	}
    }

    public function sendSms($request)
 	{
     	$accountSid = config('app.twilio')['TWILIO_ACCOUNT_SID'];
     	$authToken = config('app.twilio')['TWILIO_AUTH_TOKEN'];

    	try
     	{
     		$mobile = '+60'.$request->mobile;

     		$client = new Client(['auth' => [$accountSid, $authToken]]);
     		$result = $client->post('https://api.twilio.com/2010-04-01/Accounts/'.$accountSid.'/Messages.json',
     					[
     						'form_params' => 
     							[
     								'Body' => 'Thanks for being part of us at RoyalE4bet. Your OTP is: '. $request->code, //set message body
     								'To' => $mobile,
     								'From' => '+19453455956' //we get this number from twilio
     							]
 						]);

     		return $result;
     	}
     	catch (Exception $e)
     	{
     		echo "Error: " . $e->getMessage();
     	}
    }
}
