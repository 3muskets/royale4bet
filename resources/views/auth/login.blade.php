@extends('layouts.app')

@section('head')

<style>
    .card
    {
        text-align: center;
        border: 0;
        color: white;
        font-weight: 700;
    }
    
    .profileBox
    {
        border-radius:10px;
        border: 3px solid rgba(155,225,255,.96);
        box-shadow: inset 0 20px 20px -20px #77a5eb;
    }

    .login-icon
    {
        width: 20px;
        height: 20px;
    }

    input
    {
        border: 2px solid #77a5eb !important;
        background: black !important;
        border-radius: 10px !important;
        box-shadow: none !important;
        outline: none !important;
        color: white !important;
        padding: 10px 15px !important;

        font-size:16px !important;

    }

    input::placeholder
    {
        color: #bcbcbc !important;
        text-align: center;
    }

    .btn-login
    {
        width: 100%;
        max-width: 250px;
        height: 40px;
        color: #ffffff;
        margin: 0;
        padding: 0;
        border-radius: 8px;
        border: 0;

        background: linear-gradient(180deg,rgba(43,67,129,.96),rgba(31,10,90,.96));

    }
    .btn-login:hover
    {
        color: #fff;
        background: linear-gradient(180deg,#79c1f4,#4300d2);
    }

    .invalid-feedback
    {
        /*text-align: right;*/
    }

    .form-container div
    {
        width: 90%;
        margin-left: auto;
        margin-right: auto;
    }
    .form-container a
    {
        color: #eb77cf;
    }
    .captcha img
    {
        width: 100%;
        height: auto;
    }

    .btn-submit
    {
        width: 100%;
        max-width: 200px;
        height: 40px;
        color: #ffffff;
        margin: 0;
        padding: 0;
        border-radius: 8px;
        border:0;
        background: linear-gradient(180deg,rgba(43,67,129,.96),rgba(31,10,90,.96));

    }
    .btn-submit:hover
    {
        color: #ffffff;
        background: linear-gradient(180deg,#79c1f4,#4300d2);
    }

</style>
@endsection

@section('content')
<div class="container py-2">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-9 col-lg-6 col-xl-5">
            <div class="card" style="background: transparent;">

                <div class="card-body profileBox" style="">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div style="color:white">
                            
                            <div style="padding:20px 0;">
                                {{ __('app.login.login') }}
                            </div>

                            <img class="w-50" src="/images/auth/login-header.png">
                        </div>

                        <div class="form-container">
                            <div class="form-group">
                                <input id="username" type="username" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" required autofocus placeholder="{{ __('app.login.username') }}" autocomplete="off">

                                @if ($errors->has('username'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required placeholder="{{ __('app.login.password') }}">

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-login">
                                    {{ __('app.login.button.login') }}
                                </button>
                            </div>

                            <div class="form-group">
                                <a href="#" class="float-left" data-toggle="modal" data-target="#forgetPass">{{ __('app.login.forgotpw') }}</a>
                                
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="forgetPass" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" style="color:black">{{ __('app.login.forgotpw.title') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card py-2 px-4">
                    <div class="tip text-left" style="color:#363636;">
                        {{ __('app.login.forgotpw.instruction') }}
                    </div>
                </br>
                    <form action="">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="{{ __('app.login.forgotpw.email') }}" style="border:1px solid #363636 !important;background-color: white!important;color:black !important" required>
                        </div>
                        <div class="form-group" style="width: 80%; margin-left:auto; margin-right: auto;">
                            <button class="btn btn-submit" type="submit">
                                {{ __('app.login.forgotpw.send') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
