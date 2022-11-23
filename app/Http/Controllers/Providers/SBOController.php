<?php
namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Provider;
use Auth;
use App;

class SBOController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public static function mapLocale()
    {
        $locale = array(
                    'en'  => 'en'
                    ,'zh-cn'  => 'zh-cn'
                    ,'th'  => 'th-th'
                );

        return $locale[App::getLocale()];
    }

    public function createAgent(Request $request)
    {
        try 
        {
            $url = env("SBO_API_URL")."web-root/restricted/agent/register-agent.aspx";
            $header = array("Content-Type: application/json");

            $data['CompanyKey'] = env("SBO_COMPANY_KEY");
            $data['Username'] = env("SBO_AGENT");
            $data['Password'] = env("SBO_PASSWORD");
            $data['Currency'] = env("CURRENCY");
            $data['Min'] = env("SBO_AGENT_MIN");
            $data['Max'] = env("SBO_AGENT_MAX");
            $data['MaxPerMatch'] = env("SBO_AGENT_MAXPERMATCH");
            $data['ServerId'] = Helper::generateRandomString(15);
            $data['CasinoTableLimit'] = 4; //1->low//2->medium//3->high

            $response = Helper::postData($url,$data,$header);
            return $response;
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
        }
    }

    public static function auth()
    {
        try 
        {
            $username = Auth::user()->username;
            $url = env("SBO_API_URL")."web-root/restricted/player/register-player.aspx";
            $header = array("Content-Type: application/json");

            $data['CompanyKey'] = env("SBO_COMPANY_KEY");
            $data['Username'] = $username;
            $data['Agent'] = env("SBO_AGENT");
            $data['ServerId'] = Helper::generateRandomString(15);

            $response = Helper::postData($url,$data,$header);
            $response = json_decode($response,true);

            if ($response['error']['id'] == 0 ||$response['error']['id'] == 4103) 
            {
                return true;
            }

            return false;
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return false;
        }
    }

    public static function login()
    {
        try 
        {
            $username = Auth::user()->username;
            //check status
            $status = Auth::user()->status;
            
            if ($status != 'a') 
            {
                return 'Inactive Member!';
            }
            
            $url = env("SBO_API_URL")."web-root/restricted/player/login.aspx";
            $header = array("Content-Type: application/json");
            $lang = self::mapLocale();
            $oddstyle = env("SBO_IFRAME_ODDSTYLE");
            $theme = env("SBO_IFRAME_THEME");
            $oddsmode = env("SBO_IFRAME_ODDSMODE");

            if (!self::auth()) 
            {
                return ['status' => 0,
                                'error' => 'CREATE_USER_FAIL'];
            }

            $data['CompanyKey'] = env("SBO_COMPANY_KEY");
            $data['Username'] = $username;
            $data['Portfolio'] = 'SportsBook'; //sportbook
            $data['serverId'] = Helper::generateRandomString(15);

            $response = Helper::postData($url,$data,$header);
            $response = json_decode($response,true);

            log::debug($response);

            if (!isset($response['url'])) 
            {
                $response = ['status' => 0,
                                'error' => $response['error']['msg']
                            ];
            }
            else
            {
                $response = ['status' => 1,
                            'iframe' => 'https:'.$response['url'].'&lang='.$lang.'&theme='.$theme.'&oddstyle='.$oddstyle.'&oddsmode='.$oddsmode.'&device='
                        ];
            }

            return $response;
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
        }
    }

    // public function updateStatus(Request $request)
    // {
    //     try 
    //     {
    //         $request = (isset($request['param']))?json_decode($request['param'],true):NULL;

    //         $url = env("SBO_API_URL")."player/update-player-status.aspx";
    //         $header = array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");
    //         $data = [];

    //         if ($request != NULL) 
    //         {
    //             $data['CompanyKey'] = env("SBO_COMPANY_KEY");
    //             $data['Username'] = $db[0]->prefix.$request['Username'];
    //             $data['status'] = $request['statud'];
    //             $data['serverId'] = $request['serverId'];
    //         }

    //         $response = self::postData($url,$data,$header);

    //         $response = json_decode($response,true);
            
    //         return $response;
    //     } 
    //     catch (Exception $e) 
    //     {
    //         Log::debug($e);
    //     }
    // }

    // public static function getBetList(Request $request)
    // {
    //     try
    //     {
    //         $request = (isset($request['param']))?json_decode($request['param'],true):NULL;

    //         $url = env('SBO_API_URL').'report/get-bet-list.aspx';
    //         $header = array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");

    //         $data = [];

    //         if ($request != NULL) 
    //         {
    //             $data['CompanyKey'] = env("SBO_COMPANY_KEY");
    //             $data['serverId'] = env("SBO_SERVER_ID");
    //             $data['Username'] = $db[0]->prefix.$request['Username'];
    //             $data['startDate'] = $request['startDate'];
    //             $data['endDate'] = $request['endDate'];
    //             $data['language'] = $request['language'];
    //         }

    //         $response = self::postData($url,$data,$header);

    //         $response = json_decode($response,true);
            
    //         return $response;
    //     }
    //     catch(\Exception $e)
    //     {
    //         log::debug($e);
    //     }
    // }

    // public static function postData($url,$data,$header = '')
    // {
    //     try
    //     {
    //         $curl = curl_init();

    //         if (is_array($data))
    //         {
    //             $data = json_encode($data);
    //         }

    //         curl_setopt_array($curl, array(
    //           CURLOPT_URL => $url,
    //           CURLOPT_RETURNTRANSFER => true,
    //           CURLOPT_ENCODING => "",
    //           CURLOPT_MAXREDIRS => 10,
    //           CURLOPT_TIMEOUT => 0,
    //           CURLOPT_FOLLOWLOCATION => true,
    //           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //           CURLOPT_CUSTOMREQUEST => "POST",
    //           CURLOPT_POSTFIELDS => "param=".$data,
    //           CURLOPT_HTTPHEADER => $header,
    //         ));

    //         $response = curl_exec($curl);

    //         curl_close($curl);
    //         return $response;
    //     }
    //     catch(\Exception $e)
    //     {
    //         Log::debug($e);
    //         return '';
    //     }
    // }

    public static function isIPAllow($ag_code, $ip)
    {   
        $is_allow = false;

        $db = db::SELECT('SELECT ip FROM ip_whitelist WHERE ag_code=? AND ip=?',[$ag_code, $ip]);

        if(sizeof($db) > 0)
        {
            $is_allow = true;
        }

        return $is_allow;
    }
}
