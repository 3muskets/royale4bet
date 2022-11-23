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

class FGController extends Controller
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

    public static function getGame($type)
    {
        try
        {
            $userId =  Auth::id();
            $userName =  Auth::user()->username;
            
            $hostName = env('FG_HOSTNAME');
            $merchantCode = env('FG_MERCHANTCODE');
            $merchantToken = env('FG_MERCHANTTOKEN');

            $method = 'auth';

            $url = $hostName.$method;

            $header = ['merchant-code:'.$merchantCode
                        ,'merchant-token:'.$merchantToken
                    ];
            
            $data = ['user' => 
                        [
                            'id' => $userId
                            ,'name' => $userName
                        ]
                    ,'game' =>
                        [
                            'id' => intval($type)
                        ]
                    ];
           
            $response = Helper::postData($url,$data,$header);
            $response = json_decode($response);

            if($response->{'status'} == 0)
            {
                return '';
            }

            $iframe = $response->{'launch_url'};
            $fgId = $response->{'member_id'};

            self::updateFGUsers($fgId);

            return $iframe;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function updateFGUsers($fgId)
    {
        try
        {
            $userId =  Auth::id();

            DB::insert('
                INSERT INTO fg_users (member_id,fg_id)
                VALUES (?,?)
                ON DUPLICATE KEY UPDATE
                    fg_id = ?'
                ,[  $userId
                    ,$fgId
                    ,$fgId]);
        }
        catch(\Exception $e)
        {
            
        }
    }
}
