<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Providers;

use Auth;
use App;
use Log;
use Lang;

class GSSController extends Controller
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

    public static function mapProduct()
    {
        $product = array(
                   Providers::LIVEevoC => [1002,2]//Evolution Gaming live casino (2)
                   ,Providers::LIVEabC => [1003,2]//All Bet live casino (2)
                   ,Providers::LIVEbgC => [1004,2]//Big Gaming live casino (2)
                   ,Providers::LIVEsaC => [1005,2]//SA Gaming live casino (2)
                   ,Providers::LIVEppS => [1006,1]//Pragmatic Play slot (1)
                   ,Providers::LIVEpgsS => [1007,1]//PG Soft live slot (1)
                   ,Providers::LIVEcq9S => [1009,1]//CQ9 slot (1)
                   ,Providers::LIVEcq9F => [1009,8]//CQ9 fishing (8)
                   ,Providers::LIVEptS => [1011,1]//Play Tech live casino (1)
                   ,Providers::LIVEptC => [1011,2]//Play Tech live casino (2)
                   ,Providers::LIVEjokerS => [1013,1]//Joker slot (1)
                   ,Providers::LIVEdsS => [1014,1]//Dragon Soft slot (1)
                   ,Providers::LIVEtfS => [1017,1]//TF Gaming slot (1)
                   ,Providers::LIVEtfE => [1017,13]//TF Gaming esport (13)
                   ,Providers::LIVEwmC => [1020,2]//WM Casino live casino (2)
                   ,Providers::LIVEsgC => [1022,2]//Sexy Gaming live casino (2)
                   ,Providers::LIVEkingC => [1038,2]//King 855 live casino (2)
                   ,Providers::LIVEamayaS => [1039,1]//AMAYA slot (1)
                   ,Providers::LIVEhabaS => [1041,1]//Habanero slot (1)
                   ,Providers::LIVEibcSB => [1046,3]//IBC sport book (3)
                   ,Providers::LIVEreevoS => [1048,1]//Reevo     slot (1)
                   ,Providers::LIVEevopS => [1049,1]//EvoPlay   slot (1)
                   ,Providers::LIVEpsS => [1050,1]//PlayStar  slot (1)
                   ,Providers::LIVEdgC => [1052,2]//Dream Gaming  live casino (2)
                   ,Providers::LIVEnexusL => [1053,5]//Nexus 4D  lottery (5)
                   ,Providers::LIVEhkgpL => [1074,5]//HKGP Lottery  lottery (5)
                   ,Providers::LIVEslotxoS => [1075,1]//SlotXo    slot (1)
                   ,Providers::LIVEambP => [1076,7]//AMB Poker     p2p (7)
                   ,Providers::LIVEskyS => [1077,1]//SkyWind   slot (1)
                   ,Providers::LIVEskyC => [1077,2]//SkyWind   live casino (2)
                   ,Providers::LIVEbtiSB => [1081,3]//BTI   sport book (3)
                   ,Providers::LIVEapS => [1084,1]//Advant Play   slot (1)
                   ,Providers::LIVEjdbS => [1085,1]//JDB   slot (1)
                   ,Providers::LIVEjiliS => [1091,1]//Jili  slot (1)
                   ,Providers::LIVEjiliF => [1091,8]//Jili  fishing (8)
                );

        return $product;
    }

    public static function mapLaguage()
    {
        try 
        {
            $locale = array(
                    'en'  => 1
                );

            return $locale[App::getLocale()];
        } 
        catch (Exception $e)
        {
            return 1;
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public static function launchGames($gameId,$isMobile=0)
    {
        try 
        {
            $operatorCode = env('GSS_AGENT_CODE');
            $providerCode = self::mapProduct();
            $providerCode = $providerCode[$gameId];
            $productId = $providerCode[0];
            $gameType = $providerCode[1];
            $secretKey = env('GSS_SECRET_KEY');
            $apiUrl = env('GSS_API_URL');
            $password = env('GSS_MEMBER_PASSWORD');
            $method = '/Seamless/LaunchGame';
            $requestTime = time();
            $language = self::mapLaguage();

            $username = strtolower(Auth::user()->username);

            $md5 = md5($operatorCode.$requestTime.'LaunchGame'.$secretKey);
            $signature = strtoupper($md5);

            $url = $apiUrl.$method.'?OperatorCode='.$operatorCode.'&MemberName='.$username.'&password='.$password.'&ProductID='.$productId.'&GameType='.$gameType.'&LanguageCode='.$language.'&Platform='.$isMobile.'&signature='.$signature;

            $data = [
                    'OperatorCode'=>$operatorCode,
                    'MemberName'=>$username,
                    'password'=>$password,
                    'ProductID'=>$productId,
                    'GameType'=>$gameType,
                    'LanguageCode'=>$language,
                    'Platform'=>$isMobile,
                    'signature'=>$signature
                ];

            log::debug($url);
            log::debug($data);
            

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            log::debug($response);

            if ($response['ErrorCode'] != 0) 
            {
                Log::debug('GSS ERROR:');
                Log::debug($response);
                return $response['ErrorMessage'];
            }

            return $response['Url'];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }


}
