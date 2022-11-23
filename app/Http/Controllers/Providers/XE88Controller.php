<?php

namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;
use App\Http\Controllers\Provider;
use App\Http\Controllers\UserController;
use Log;
use DateTime;
use Auth;
use App;
use DES;
use URL;

class XE88Controller extends Controller
{
    public static function mapLocale()
    {
        $locale = array(
                    'en'  => 'en_US'
                    ,'zh-cn'  => 'zh-Hans'
                    ,'ar'  => 'en'
                );

        return $locale[App::getLocale()];
    }

    public static function getGameList()
    {
        try
        {
            $db = DB::select('
                SELECT 1007 as prd_id, id as game_id,game_type,game_name,game_name_cn,game_type 
                FROM xe88_games');

            foreach ($db as $d)
            {
                $d->img_url = URL::to('/')."/images/slots/xe88/en/".$d->game_id.".png";
                $d->img_url_cn = URL::to('/')."/images/slots/xe88/cn/".$d->game_id.".png";
            }

            return $db;
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function getGame($gameId=6)
    {
        try 
        {
            if(!self::createPlayer())
            {
                return ['status'=>0,'error'=>'CREATE_USER_FAILED'];
            }

            $url = env("XE88_API_URL");
            $agentId = env("XE88_AGENT_ID");
            $username = env("XE88_PREFIX").'_'.Auth::user()->username;
            $password = md5(env("XE88_PLAYER_PASSWORD"));
            $signaturekey = env("XE88_SIGNATURE_KEY");
            $gameUrl = env("XE88_GAME_URL");

            if ($gameId==NULL) 
            {
                return ['status'=>0,'error'=>'EMPTY_GAME_ID'];
            }
            else
            {
                $db = DB::select("SELECT id
                                FROM xe88_games
                                WHERE id = ?"
                                ,[$gameId]);

                if (sizeof($db) == 0) 
                {
                    return ['status'=>0,'error'=>'INVALID_GAME_ID'];
                }
                else
                {
                    $gameUrl = $gameUrl.'?language=En&gameid='.$gameId.'&userid='.$username.'&userpwd='.$password;

                    return ['status'=>1,'iframe'=>$gameUrl];
                }
            }

            return $gameUrl;

            $requestbody = '{"agentid":"'.$agentId.'","account":"'.$username.'","password":"'.$password.'"}';

            $hashdata = hash_hmac("sha256", $requestbody, $signaturekey, true);

            $hash = base64_encode($hashdata);

            $headerstring = 'hashkey: ' . $hash;

            $headers = [
                $headerstring
            ];

            $url = $url.'player/create';

            $response = Helper::postData($url,$requestbody,$headers);
            $response = json_decode($response,true);

            if (isset($response['code']) && ($response['code'] == 0 || $response['code'] == 31))
            {
                return true;
            }
            else
            {
                return false;
            }
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return false;
        }
    }

    public static function createPlayer()
    {
        try 
        {
            $url = env("XE88_API_URL");
            $agentId = env("XE88_AGENT_ID");
            $username = env("XE88_PREFIX").'_'.Auth::user()->username;
            $password = env("XE88_PLAYER_PASSWORD");
            $signaturekey = env("XE88_SIGNATURE_KEY");

            $requestbody = '{"agentid":"'.$agentId.'","account":"'.$username.'","password":"'.$password.'"}';

            $hashdata = hash_hmac("sha256", $requestbody, $signaturekey, true);

            $hash = base64_encode($hashdata);

            $headerstring = 'hashkey: ' . $hash;

            $headers = [
                $headerstring
            ];

            $url = $url.'player/create';

            $response = Helper::postData($url,$requestbody,$headers);
            $response = json_decode($response,true);

            if (isset($response['code']) && ($response['code'] == 0 || $response['code'] == 31))
            {
                return true;
            }
            else
            {
                return false;
            }
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return false;
        }
    }

    public static function updatePlayer()
    {
        try 
        {
            $url = env("XE88_API_URL");
            $agentId = env("XE88_AGENT_ID");
            $username = env("XE88_PREFIX").'_'.Auth::user()->username;
            $password = env("XE88_PLAYER_PASSWORD");
            $signaturekey = env("XE88_SIGNATURE_KEY");

            $requestbody = '{"agentid":"'.$agentId.'","account":"'.$username.'","password":"'.$password.'"}';

            $hashdata = hash_hmac("sha256", $requestbody, $signaturekey, true);

            $hash = base64_encode($hashdata);

            $headerstring = 'hashkey: ' . $hash;

            $headers = [
                $headerstring
            ];

            $url = $url.'player/update';

            $response = Helper::postData($url,$requestbody,$headers);
            $response = json_decode($response,true);

            return $response;

            if (isset($response['code']) && ($response['code'] == 0 || $response['code'] == 31))
            {
                return true;
            }
            else
            {
                return false;
            }
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return false;
        }
    }

    public static function playerInfo()
    {
        try 
        {
            $url = env("XE88_API_URL");
            $agentId = env("XE88_AGENT_ID");
            $username = env("XE88_PREFIX").'_'.Auth::user()->username;
            $password = env("XE88_PLAYER_PASSWORD");
            $signaturekey = env("XE88_SIGNATURE_KEY");

            $requestbody = '{"agentid":"'.$agentId.'","account":"'.$username.'"}';

            $hashdata = hash_hmac("sha256", $requestbody, $signaturekey, true);

            $hash = base64_encode($hashdata);

            $headerstring = 'hashkey: ' . $hash;

            $headers = [
                $headerstring
            ];

            $url = $url.'player/info';

            $response = Helper::postData($url,$requestbody,$headers);
            $response = json_decode($response,true);

            return $response;

            if (isset($response['code']) && ($response['code'] == 0))
            {
                return ['status' => 1
                        ,'balance' => $response['result']['balance']];
            }
            else
            {
                return ['status' => 0, 'error' => 'PLAYER_INFO_ERROR'];
            }
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['status' => 0, 'error' => 'INTERNAL_ERROR'];
        }
    }

    public static function deposit()
    {
        try 
        {
            $url = env("XE88_API_URL");
            $agentId = env("XE88_AGENT_ID");
            $username = env("XE88_PREFIX").'_'.Auth::user()->username;
            $password = env("XE88_PLAYER_PASSWORD");
            $signaturekey = env("XE88_SIGNATURE_KEY");

            $balance = DB::select("SELECT available 
                                FROM member_credit
                                WHERE member_id = ?"
                                ,[Auth::id()]);

            if (sizeof($balance) != 0 && $balance[0]->available != 0) 
            {
                $balance = $balance[0]->available;
            }
            else
            {
                DB::rollback();

                return ['status' => 0, 'error_code' => 'INSUFFICIENT_BALANCE'];
            }

            $requestbody = '{"agentid":"'.$agentId.'","account":"'.$username.'","amount":"'.'10'.'"}';

            $hashdata = hash_hmac("sha256", $requestbody, $signaturekey, true);

            $hash = base64_encode($hashdata);

            $headerstring = 'hashkey: ' . $hash;

            $headers = [
                $headerstring
            ];

            $url = $url.'player/deposit';

            $response = Helper::postData($url,$requestbody,$headers);
            $response = json_decode($response,true);

            if (isset($response['code']) && ($response['code'] == 0))
            {
                DB::update("UPDATE member_credit
                            SET available = available - ?
                            WHERE member_id = ?
                            "
                            ,[$response['result']['amount'],Auth::id()]);
                
                DB::commit();

                return ['status' => 1, 'balance' => $response['result']['currentplayerbalance']];
            }
            else
            {
                DB::rollback();

                return ['status' => 0, 'error' => 'DEPOSIT_FAILED'];
            }
        } 
        catch (Exception $e) 
        {
            DB::rollback();

            log::debug($e);
            return ['status' => 0, 'error' => 'INTERNAL_ERROR'];
        }
    }

    public static function withdraw()
    {
        DB::beginTransaction();
        try 
        {
            $url = env("XE88_API_URL");
            $agentId = env("XE88_AGENT_ID");
            $username = env("XE88_PREFIX").'_'.Auth::user()->username;
            $password = env("XE88_PLAYER_PASSWORD");
            $signaturekey = env("XE88_SIGNATURE_KEY");

            $balance = self::playerInfo();
            if ($balance['status'] != 1) 
            {
                DB::rollback();

                return $balance;
            }
            else
            {
                $balance = $balance['balance'];
            }

            $requestbody = '{"agentid":"'.$agentId.'","account":"'.$username.'","amount":'.$balance.'}';

            $hashdata = hash_hmac("sha256", $requestbody, $signaturekey, true);

            $hash = base64_encode($hashdata);

            $headerstring = 'hashkey: ' . $hash;

            $headers = [
                $headerstring
            ];

            $url = $url.'player/withdraw';

            $response = Helper::postData($url,$requestbody,$headers);
            $response = json_decode($response,true);

            if (isset($response['code']) && ($response['code'] == 0))
            {
                DB::update("UPDATE member_credit
                            SET available = available + ?
                            WHERE member_id = ?
                            "
                            ,[$response['result']['amount'],Auth::id()]);

                DB::commit();

                return ['status' => 1, 'balance' => $response['result']['currentplayerbalance']];
            }
            else
            {
                DB::rollback();

                return ['status' => 0, 'error' => 'WITHDRAW_FAILED'];
            }
        } 
        catch (Exception $e) 
        {
            DB::rollback();

            log::debug($e);
            return ['status' => 0, 'error' => 'INTERNAL_ERROR'];
        }
    }
}