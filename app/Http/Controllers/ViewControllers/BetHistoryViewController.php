<?php

namespace App\Http\Controllers\ViewControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BetHistoryController;

use DB;
use Log;

class BetHistoryViewController extends Controller
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
        $prdId = $request->input('prd_id');
        
        if($prdId != null)
        {
            return view('bet_details');
        }
        
        return view('bet_history');
    }

    public function getProducts(Request $request)
    {
        $data = BetHistoryController::getProducts($request);

        return $data;
    }

    public function getDetails(Request $request)
    {
        $data = BetHistoryController::getBetHistory($request);

        return $data;
    }
}
