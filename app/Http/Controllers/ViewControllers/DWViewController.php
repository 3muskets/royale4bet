<?php

namespace App\Http\Controllers\ViewControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\DWController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentGatewayController;
use Auth;
use DB;
use Log;

class DWViewController extends Controller
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
        $data = DWController::getList();

        return view('dw-list')->with('data',$data);
    }

    public function return(Request $request)
    {
        return view('deposit-return-page');
    }

    public function transfer(Request $request)
    {
        $data = DWController::transfer($request);

        return view('transfer');
    }

    public function deposit(Request $request)
    {
        $url = $_SERVER['REQUEST_URI'];

        $type = 'd';

        if(strpos($url, 'status') == true)
        {
            $data = DWController::getList($type);

            return view('d-list')->with('data',$data);
        }
        else if(strpos($url, 'crypto'))
        {
            return view('d-crypto');
        }
        else if(strpos($url, 'bank'))
        {

            $memberId = Auth::id();

            $db = DB::select("
                        SELECT wallet_address
                        FROM member
                        WHERE id = ?
                        ",[$memberId]
                    );


            $walletAddr = $db[0]->wallet_address;

            $currency = UserController::getCurrency();
            $regCd = UserController::getRegCd();
            $bankInfo = UserController::getAdminBankInfo();

            $promoList = UserController::getPromoList();


            return view('deposit')->with(['walletAddr'=>$walletAddr,'currency'=>$currency, 'regCd'=>$regCd,'bankInfo'=>$bankInfo,'promoList'=> $promoList]);
        }
        else if(strpos($url, 'card'))
        {
            return view('d-card');
        }

        $currency = UserController::getCurrency();
        $regCd = UserController::getRegCd();

        return view('d-new')->with(['currency'=>$currency, 'regCd'=>$regCd]);
    }

    public function withdraw(Request $request)
    {
        $url = $_SERVER['REQUEST_URI'];

        $type = 'w';

        if(strpos($url, 'status') == true)
        {
            $data = DWController::getList($type);

            return view('w-list')->with('data',$data);
        }
        else if(strpos($url, 'crypto') == true)
        {
            return view('w-crypto');
        }
        else if(strpos($url, 'bank') == true)
        {
            $data = DWController::getBankList();

            return view('w-bank')->with(['data' => $data]);
        }
        else
        {

            $currency = UserController::getCurrency();
            $regCd = UserController::getRegCd();
            $bankInfo = UserController::getBankDetails();

            return view('withdraw')->with(['currency'=>$currency, 'regCd'=>$regCd,'bankInfo'=>$bankInfo]);
        }

        $currency = UserController::getCurrency();
        $regCd = UserController::getRegCd();
        
        return view('w-new')->with(['currency'=>$currency, 'regCd'=>$regCd]);
    }


    public function getWalletAddress(Request $request)
    {
        return DWController::getWalletAddress($request);
    }


    public function create(Request $request)
    {
        return DWController::create($request);
    }

    public function createCrypto(Request $request)
    {
        return DWController::createCrypto($request);
    }

    public function getCryptoRate(Request $request)
    {
        return CryptoController::getRate();
    }

    public function cancel(Request $request)
    {
        return DWController::cancel($request);
    }
}
