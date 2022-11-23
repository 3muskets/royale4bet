<?php

namespace App\Http\Controllers\ViewControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\UserController;
// use App\Http\Controllers\Providers\AASController;
// use App\Http\Controllers\Providers\HABAController;
// use App\Http\Controllers\Providers\WMController;
// use App\Http\Controllers\Providers\PPController;
// use App\Http\Controllers\Providers\FGController;
// use App\Http\Controllers\Providers\GSController;
// use App\Http\Controllers\Providers\MEGAController;
// use App\Http\Controllers\Providers\NOEController;
// use App\Http\Controllers\Providers\PUSSYController;

use App\Http\Controllers\Providers\SBOController;
use App\Http\Controllers\Providers\EVOController;
use App\Http\Controllers\Providers\IBCController;
use App\Http\Controllers\Providers\SAController;
use App\Http\Controllers\Providers\PTController;
use App\Http\Controllers\Providers\JokerController;
use App\Http\Controllers\Providers\XE88Controller;

use App\Http\Controllers\Helper;
use App\Http\Controllers\Providers;
use Auth;
use DB;
use Log;

class GameViewController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $response = Helper::getData('http://stgadmin.royale4bet.com/test');
        // return $response;
        /*
        1 - AAS EVO
        2 - HABA
        3 - PP
        4 - WM
        5 - FG
        */

        $gameId = $request->input('gameId');
        $type = $request->input('type');
        $isMobile = $request->input('isMobile');
        
        $iframe = '';

        // if($gameId == Providers::Gameplay 
        //     ||$gameId == Providers::BBIN
        //     ||$gameId == Providers::IBC
        //     ||$gameId == Providers::ALLBET
        //     ||$gameId == Providers::CQ9
        //     ||$gameId == Providers::WM
        //     ||$gameId == Providers::Joker
        //     ||$gameId == Providers::PSB4D
        //     ||$gameId == Providers::Spade
        //     ||$gameId == Providers::QQKeno
        //     ||$gameId == Providers::CMD
        //     ||$gameId == Providers::M8BET
        //     ||$gameId == Providers::DIGMAAN
        //     ||$gameId == Providers::EBET
        //     ||$gameId == Providers::IA
        //     ||$gameId == Providers::NLIVE22)
        // {
        //     $iframe = GSController::launchGames($gameId,$type,$isMobile);
        // }
        // else if($gameId == Providers::Ps9EVO
        //         || $gameId == Providers::Ps9AG
        //         || $gameId == Providers::Ps9PP
        //         || $gameId == Providers::Ps9OT
        //         || $gameId == Providers::Ps9IA
        //         || $gameId == Providers::Ps9PPSlot
        //         || $gameId == Providers::Ps9Haba
        //         || $gameId == Providers::Ps9Ely
        //         || $gameId == Providers::Ps9QS
        //         || $gameId == Providers::Ps9SG
        //         || $gameId == Providers::Ps9AWS
        //         || $gameId == Providers::Ps9PnG
        //         || $gameId == Providers::Ps9WM
        //         || $gameId == Providers::Ps9Micro
        //         || $gameId == Providers::Ps9Joker
        //         || $gameId == Providers::Ps9OTSlot
        //         || $gameId == Providers::Ps9EvoRtg
        //         || $gameId == Providers::Ps9Netent
        //         || $gameId == Providers::Ps9Booon
        //         || $gameId == Providers::Ps9Playson
        //         || $gameId == Providers::Ps9PS)
        // {
        //     $iframe = AASController::getGame($gameId,$type,$isMobile);
        // }
        // else if($gameId == Providers::FastGame)
        // {
        //     $iframe = FGController::getGame($type);

        //     return redirect($iframe);
        // }
        // else if($gameId == Providers::MEGA)
        // {
        //     $response = MEGAController::launchGames();
        //     $iframe = $response['launch_url'];
        //     $loginId = $response['login_id'];
        // }
        // else if($gameId == Providers::NOE)
        // {
        //     $response = NOEController::launchGames();
        //     $iframe = $response['launch_url'];
        //     $loginId = $response['login_id'];
        //     $password = $response['ft_password'];
        // }
        // else if($gameId == Providers::PUSSY)
        // {
        //     $response = PUSSYController::launchGames();
        //     $iframe = $response['launch_url'];
        //     $loginId = $response['login_id'];
        //     $password = $response['ft_password'];
        // }

        if($gameId == Providers::SBO)
        {
            $response = SBOController::login();
        }
        else if($gameId == Providers::EVO)
        {
            $response = EVOController::getGame();
        }
        else if($gameId == Providers::IBC)
        {
            $response = IBCController::getGame();
        }
        else if($gameId == Providers::SA)
        {
            $response = SAController::getGame($isMobile);
        }
        else if($gameId == Providers::Joker)
        {
            $response = JokerController::openGame($request);
        }
        else if($gameId == Providers::XE88)
        {
            $response = XE88Controller::getGame($type);
        }

        if (isset($response)) 
        {
            if ($response['status'] == 1) 
            {
                $iframe = $response['iframe'];
            }
        }

        // return redirect($iframe);


        if($isMobile == 1)
        {
            return redirect($iframe);
        }
        else
        {
            //bbin slot cant be iframe, will hit error
            // if(($gameId == Providers::BBIN && $type == 2) 
            //     || ($gameId == Providers::BBIN && $type == 5)
            //     || ($gameId == Providers::Joker && $type == 4)
            //     || ($gameId == Providers::PSB4D && $type == 5))
            //     return redirect($iframe);
            // if($gameId == Providers::MEGA)
            //     return view('game')->with(['iframe' => $iframe,
            //                                 'loginId' => $loginId
            //                             ]);
            // if($gameId == Providers::NOE || $gameId == Providers::PUSSY)
            //     return view('game')->with(['iframe' => $iframe,
            //                                 'loginId' => $loginId,
            //                                 'ft_password' => $password
            //                             ]);
            
            return view('game')->with('iframe',$iframe);
        }
    }

    public function openSlotGame(Request $request)
    {
        /*
        1 - AAS EVO
        2 - HABA
        3 - PP
        4 - WM
        5 - FG
        */

        $gameId = $request->input('gameId');
        $type = $request->input('type');
        $isMobile = $request->input('isMobile');
        
        $iframe = '';

        if($gameId == Providers::Gameplay 
            ||$gameId == Providers::BBIN)
        {
            $iframe = GSController::launchGames($gameId,$type,$isMobile);
        }
        else if($gameId == Providers::Ps9EVO
                ||$gameId == Providers::Ps9AG
                ||$gameId == Providers::Ps9PP
                ||$gameId == Providers::Ps9OT
                ||$gameId == Providers::Ps9IA
                ||$gameId == Providers::Ps9PPSlot
                || $gameId == Providers::Ps9Haba
                || $gameId == Providers::Ps9Ely
                || $gameId == Providers::Ps9QS
                || $gameId == Providers::Ps9SG
                || $gameId == Providers::Ps9AWS
                || $gameId == Providers::Ps9PnG
                || $gameId == Providers::Ps9WM
                || $gameId == Providers::Ps9Micro
                || $gameId == Providers::Ps9Joker
                || $gameId == Providers::Ps9OTSlot
                || $gameId == Providers::Ps9EvoRtg
                || $gameId == Providers::Ps9Netent
                || $gameId == Providers::Ps9Booon
                || $gameId == Providers::Ps9Playson
                || $gameId == Providers::Ps9PS)
        {
            $iframe = AASController::getGame($gameId,$type,$isMobile);
        }
        else if($gameId == Providers::PT)
        {
            $iframe = PTController::getGame($gameId,$isMobile);
            return  $iframe;
        }

        // if($gameId == 1 || $gameId == 4)
        // {
        //     // $iframe = AASController::getGame($gameId,$type,$isMobile);
        //     $iframe = GSController::launchGames($type,$isMobile);
        // }
        // else if($gameId == 2)
        // {
        //     $iframe = HABAController::getGame($type);
        // }
        // else if($gameId == 3)
        // {
        //     $iframe = PPController::getGame($type,$isMobile);
        // }
        // else if($gameId == 4)
        // {
        //    $iframe = WMController::getGame($type,$isMobile);
        // }
        // else if($gameId == 5)
        // {
        //     $iframe = FGController::getGame($type);

        //     return redirect($iframe);
        // }

        log::debug($iframe);

        return $iframe;
    }

    public function walletTransfer(Request $request)
    {
        // return $request;

        try 
        {
            $gameId = $request['game_id'];
            $type = $request['type'];
            $amount = $request['amount'];

            if ($gameId == Providers::Gameplay
                ||$gameId == Providers::BBIN) 
            {
                $response = GSController::makeTransfer($gameId,$type,$amount);
            }

            return $response;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return '';
        }
    }
}
