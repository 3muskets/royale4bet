<?php

namespace App\Http\Controllers\ViewControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\Providers\PPController;
use App\Http\Controllers\Providers\HABAController;
use App\Http\Controllers\Providers\WMController;
use App\Http\Controllers\Providers\AASController;
use App\Http\Controllers\Providers\JokerController;
use App\Http\Controllers\Providers\XE88Controller;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Providers;
use Auth;
use DB;
use Log;

class LobbyViewController extends Controller
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
        /*
        2 - HABA
        3 - PP
        4 - WM
        */

        $gameId   = $request->input('gameId');

        if ($gameId == Providers::Ps9PPSlot
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
            $gameList = AASController::getSlotGameList($gameId);
            return view('slots')->with('gameList', $gameList);
        }
        else if ($gameId == Providers::CQ9) 
        {
            $gameList = GSController::getSlotGameList($gameId);
            return view('slots')->with('gameList', $gameList);
        }
        else if ($gameId == Providers::Joker)
        {
            $gameList = JokerController::getGame($gameId);
            return view('slots')->with('gameList', $gameList);
        }
        else if ($gameId == Providers::XE88)
        {
            $gameList = XE88Controller::getGameList($gameId);
            return view('slots')->with('gameList', $gameList);
        }


        // if ($gameId == 2) 
        // {
        //     $gameList = HABAController::getGameList();
        //     return view('lobby.haba')->with('gameList', $gameList);
        // }
        // else if($gameId == 3)
        // {
        //     $gameList = PPController::getGameList();
        //     return view('lobby.pp')->with('gameList', $gameList);
        // }
        // else if($gameId == 4)
        // {
        //     $gameList = WMController::getGameList();
        //     return view('lobby.wm')->with('gameList', $gameList);
        // }
    }
}
