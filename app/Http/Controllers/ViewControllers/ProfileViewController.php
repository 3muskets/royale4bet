<?php

namespace App\Http\Controllers\ViewControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;

class ProfileViewController extends Controller
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

    public function index()
    {
        $details = UserController::getMemberDetails();
        $bankInfo = UserController::getBankDetails();

        return view('my_account')->with([
                                            'username' =>$details[0]->username
                                            ,'first_name'=>$details[0]->first_name
                                            ,'last_name'=>$details[0]->last_name
                                            ,'mobile'=>$details[0]->mobile
                                            ,'email'=>$details[0]->email
                                            ,'birthdate'=>$details[0]->birthdate
                                            ,'bank'=> $bankInfo[0]->bank
                                            ,'bank_acc'=>$bankInfo[0]->acc_no
                                            ,'bank_acc_name'=>$bankInfo[0]->name
                                        ]);
    }

    public function changePW()
    {
        return view('change_pw');
    }


    public function referral()
    {
        return view('referral');
    }

    public function bankInfo()
    {
        $details = UserController::getBankDetails();

        return view('bank_info')->with(['bank_name'=>$details[0]->bank, 'bank_acc'=>$details[0]->acc_no, 'bank_add'=>$details[0]->address]);
    }
}