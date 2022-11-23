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
use DOMDocument;
use URL;

class PTController extends Controller
{
    public static function mapLocale()
    {
        $locale = array(
                    'en'  => 'en'
                    ,'zh-cn'  => 'zh-Hans'
                    ,'ar'  => 'en'
                );

        return $locale[App::getLocale()];
    }

    public static function getGameList()
    {
        try
        {
            $db = DB::select("
                SELECT id,game_code,game_name,game_name_cn,game_type,image as 'image_en'
                FROM pt_games");

            return $db;
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function getGame($gameCode='gtsje', $isMobile=false)
    {
        try 
        {
            $url = env('PLAYTECH_LAUNCH_URL');
            $key = env('PLAYTECH_SECRET_KEY');
            $brandId = env('PLAYTECH_BRAND_ID');
            $backUrl = URL::to('/');
            $requestId = Helper::generateUniqueId(32);
            $hash = md5($key);
            $platform = (!$isMobile)?'web':'mobile';
            $lang = self::mapLocale();
            $userId = Auth::id();
            $token = Helper::generateUniqueId();

            if (!Auth::id()) 
            {
                return ['status'=>0,'error'=>'INVALID_USER'];
            }

            if ($gameCode==NULL) 
            {
                return ['status'=>0,'error'=>'EMPTY_GAME_CODE'];
            }
            else
            {
                $data = ['brandId'=>$brandId
                    ,'gameCode'=>$gameCode
                    ,'token'=>$token
                    ,'platform'=>$platform
                    ,'language'=>$lang
                    ,'playerId'=>$userId
                    ,'backUrl'=>$backUrl
                    ,'mode'=>1
                ];

                $convData = self::convertRawData($data);
                $convData = str_replace($key,"",$convData);
                $url = $url.'?'.$convData;

                DB::insert('
                INSERT INTO pt_users_token(member_id,token)
                VALUES (?,?)
                ON duplicate key UPDATE
                    token = ?'
                ,[  $userId
                    ,$token
                    ,$token]);

                log::debug($url);
 
                $response = Helper::getData($url);

                return $response;
            }
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['status'=>0,'error'=>'INTERNAL_ERROR'];
        }
    }

    public static function convertRawData($data)
    {
        try 
        {
            $rawData = '';
            $secret = env('PLAYTECH_SECRET_KEY');
            ksort($data);

            foreach ($data as $key => $value) 
            {
                // $rawKey = strtolower($key);
                $rawValue = $value;

                if ($rawData != '') 
                {
                    $rawData = $rawData.'&';
                }

                $rawData = $rawData.$key.'='.$rawValue;
            }

            return $rawData.$secret;
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return '';
        }   
    }

}