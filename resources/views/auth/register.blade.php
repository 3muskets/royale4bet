@extends('layouts.app')

@section('head')
<script type="text/javascript">

    $(document).ready(function()
    {
        var refCode = utils.getParameterByName("ref_code");

        if(refCode != undefined && refCode != null && refCode != '')
        {
            $("#refcode").val(refCode);
        }

        $(".dropdown-item").click(function()
        {
            $("#currency").val(this.innerHTML);
        });
    });

</script>

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


    .register-icon
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
        padding-right: 15px !important;
    }

    .btn-login,.btn-submit
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
    .btn-login:hover,.btn-submit:hover
    {
        color: #fff;
        background: linear-gradient(180deg,#79c1f4,#4300d2);
    }

    .form-container div
    {
        width: 80%;
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

    .invalid-feedback
    {
        text-align: right;
        color: #0edfd2;
    }
    .checkbox
    {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        position: relative;
        display: block;
    }
    .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 20px;
        width: 20px;
        background-color: transparent;
        border: 1px solid #ffffff;
        border-radius: 4px;
        cursor: pointer;
    }
    #checktnc:checked ~ .checkmark {
        background-color: #000000;
    }
    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    #checktnc:checked ~ .checkmark:after 
    {
        display: block;
    }
    .checkbox .checkmark:after 
    {
        left: 6px;
        top: 1px;
        width: 6px;
        height: 13px;
        border: solid white;
        border-width: 0 2px 2px 0;
        -webkit-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        transform: rotate(45deg);
    }
  
    .-x-input-icon {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        border-radius: 3px;
        position: relative;
    }
    .-x-input-icon .-icon 
    {
        position: absolute;
        left: 18px;
        /* color: #fbc844; */
        color:#d82d8e;
        top: 16px;
    }
    .-x-input-icon .form-control {
        width: 100%;
        padding-left: 40px !important;
    }
</style>
@endsection

@section('content')
<div class="container py-2">
    <div class="row justify-content-center">
        <div class="col-11 col-sm-10 col-md-9 col-lg-6 col-xl-5 p-0" >
            <div class="card pt-2" style="background: transparent;">

                <div class="card-body profileBox" style="">
                    <form method="POST" action="{{ route('register') }}" aria-label="{{ __('Register') }}">
                        @csrf

                        <div style="color:#363636">
                            {{-- <img class="w-100" style="padding:0 20px;" src="/images/app/logo-black.png"> --}}

                            <div style="text-decoration:underline;padding:20px 0;color:#ddd;font-size:large;">
                                {{ __('app.register.register') }}
                            </div>

                            <img class="w-50" src="/images/auth/register-header2.png">
                        </div>

                        <div class="form-container">

                            <div class="form-group -x-input-icon">
                                <img data-src="/images/auth/icon-username.png" class="-icon" alt="username" src="/images/auth/icon-username.png" width="18">
                                <input id="username" type="username" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" required placeholder="{{ __('app.register.username') }}">

                                @if ($errors->has('username'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group -x-input-icon">
                                <img data-src="/images/auth/icon-password.png" class="-icon" alt="password" src="/images/auth/icon-password.png" width="18">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required placeholder="{{ __('app.register.password') }}">

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group -x-input-icon">
                                <img data-src="/images/auth/icon-password-confirm.png" class="-icon" alt="confirm-passowrd" src="/images/auth/icon-password-confirm.png" width="18">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="{{ __('app.register.confirm_password') }}">
                            </div>


                            {{-- <div class="form-group row" style="display:inline-flex">
                                <input id="captcha" type="captcha" class="form-control{{ $errors->has('captcha') ? ' is-invalid' : '' }}" name="captcha" required placeholder="{{ __('app.register.captcha') }}" style="width:50%">

                                <div class="captcha" style="width:50%;padding-left:10%;">{!! captcha_img() !!}</div>

                                @if ($errors->has('captcha'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('captcha') }}</strong>
                                    </span>
                                @endif
                            </div> 

                          <div class="form-group">
                                <input id="name" type="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required placeholder="{{ __('app.register.name') }}">

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div> --}}

                            <div class="form-group -x-input-icon">
                                <img data-src="/images/auth/icon-mobile.png" class="-icon" alt="mobile" src="/images/auth/icon-mobile.png" width="18">
            
                                <input id="mobile" type="mobile" class="form-control{{ $errors->has('mobile') ? ' is-invalid' : '' }}" name="mobile" required placeholder="{{ __('app.register.mobile') }}">
                                
                                @if ($errors->has('mobile'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('mobile') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group -x-input-icon">
                                <i class="fa fa-envelope -icon" style="font-size:16px;"></i>
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" required placeholder="{{ __('app.register.email') }}">

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group -x-input-icon">
                                <i class="fa fa-user-plus -icon" style="font-size:16px;"></i>
                                <input id="refcode" type="text" class="form-control{{ $errors->has('refcode') ? ' is-invalid' : '' }}" name="refcode" placeholder="{{ __('app.register.refcode') }}" value="{{ old('refcode') }}">

                                @if ($errors->has('refcode'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('refcode') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group text-left">
                                <label class="checkbox">
                                    <input type="checkbox" id="checktnc" name="checktnc" style="opacity: 0;" required>
                                    <span class="checkmark"></span>
                                    &nbsp;&nbsp;&nbsp;
                                    <span style="color:#ddd">
                                        {{ __('app.register.tnc1') }}<a href="/tnc">{{ __('app.register.tnc') }}</a>&<a href="/privacy_policy">{{ __('app.register.privacypolicy') }}</a>{{ __('app.register.tnc2') }}
                                    </span>
                                </label>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-submit link-fill">
                                    {{ __('app.register.button.register') }}
                                </button>
                            </div>

                            <div class="form-group row">
                                <div style="color:#ddd;">
                                    {{ __('app.register.existing') }}
                                    <a href="/login">{{ __('app.register.login') }}</a>
                                </div>
                            </div>

                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
</div>


                        
@endsection
