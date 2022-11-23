@extends('layouts.app')

@section('head')

<script src="{{ asset('js/qrcode.js') }}"></script>
<script type="text/javascript">

    $(document).ready(function() 
    {
        prepareLocale();
            
        $("#mainForm").attr("enabled",1);

        $("#mainForm").on('submit',(function(e){
            e.preventDefault();
            submitMainForm();
        }));


        $("#mainForm-crypto").attr("enabled",1);

        $("#mainForm-crypto").on('submit',(function(e){
            e.preventDefault();
            submitMainFormCrypto();
        }));

        $("#withdrawal-method").on('change', function()
        {
            $('.tabs').hide();
            $("#"+this.value+"-form").show();
        });

        $('.btn-wrap button').each(function()
        {
            $(this).click(function()
            {
                var wAmt = $(this).attr("data-value");
                var amt = +$("#amount").val();

                if(amt == '')
                {
                    amt = 0;
                }
                
                amt = +wAmt + amt;
                $("#amount").val(amt);
            });
        });

    });

    function prepareLocale() 
    {
        locale['info'] = "{!! __('common.modal.info') !!}";
        locale['success'] = "{!! __('common.modal.success') !!}";
        locale['error'] = "{!! __('common.modal.error') !!}";
    }

    function submitMainForm()
    {   
        if($("#mainForm").attr("enabled") == 0)
        {
            return;
        }

        $("#mainForm").attr("enabled",0);

        
        $.ajax({
            url: "/ajax/dw/create",
            type: "POST",
            data:  new FormData($("#mainForm")[0]),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data)
            {
                // console.log(data);

                $("#mainForm").attr("enabled",1);

                if(data.status == 1)
                {
                    alert(locale['success']);
                    window.location.href = "/my_profile/withdraw/new?status";
                }
                else
                {
                    var obj = JSON.parse(data);
                    
                    var html = "";

                    if(Array.isArray(obj.error))
                    {
                        for(var i = 0; i < obj.error.length; i++)
                        {
                            html += "-" + obj.error[i] + "\n";
                        }
                    }
                    else
                    {
                        html = obj.error;
                    }

                    alert(html);
                }
            },
            error: function(){}             
        }); 
    }



    function submitMainFormCrypto()
    {   
        if($("#mainForm-crypto").attr("enabled") == 0)
        {
            return;
        }

        $("#mainForm-crypto").attr("enabled",0);

        
        $.ajax({
            url: "/ajax/dw/crypto-create",
            type: "POST",
            data:  new FormData($("#mainForm-crypto")[0]),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data)
            {
                // console.log(data);

                $("#mainForm-crypto").attr("enabled",1);

                var obj = JSON.parse(data);

                if(obj.status == 1)
                {
                    showSuccess();
                }
                else
                {
                    var html = "";

                    if(Array.isArray(obj.error))
                    {
                        for(var i = 0; i < obj.error.length; i++)
                        {
                            html += "-" + obj.error[i] + "\n";
                        }
                    }
                    else
                    {
                        html = obj.error;
                    }

                    alert(html);
                }
            },
            error: function(){}             
        }); 
    }
</script>

<style type="text/css">
    .card
    {
        border: none;
        background: transparent;
    }
    .card-header
    {
        background: linear-gradient(180deg,#131228,#140133);
    }
    .card-header span
    {
        -webkit-mask: linear-gradient(-60deg,#000 30%,#0005,#000 70%) right/300% 100%;
        animation: shimmer 2.5s infinite;
        font-weight: bold;
    }
    .btn-submit
    {
        width: 100%;
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
    .withdrawbox-v2
    {
        background: #23214a;
        border: 2px solid #9ee2fe;
        border-radius: 20px;
        box-shadow: inset 0 0 15px #77a5eb;
    }
    
    select,input
    {
        width: 100%;
        background: black !important;
        border-radius: 10px !important;
        box-shadow: none !important;
        outline: none !important;
        color: white !important;
        padding: 5px 10px !important;

    }

    select,input::placeholder
    {
        color: #bcbcbc !important;
        text-align: center;
    }
    button:focus
    {
        box-shadow: none !important;
        outline: 0 !important;
    }
    .btn-wrap
    {
        display: flex;
    }
    .btn-wrap button
    {
        width: 33%;
        margin: 1%;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 12px 10px;
        background: #3e3a8e;
        border-radius: 5px;
        border: 1px solid transparent;
        line-height: normal;
        height: auto;
        opacity: 1;
        color: #fff;
    }
    .btn-wrap button:focus
    {
        box-shadow: none !important;
        outline: 0 !important;
    }
    .btn-wrap button:hover
    {
        filter: brightness(1.2);
    }
</style>

@endsection

@section('content')

<div class="card" style="background: transparent;">

    <div class="card-header mb-0">
        <i class="fa fa-money" style="padding: 7px;"></i> <span>Withdrawal</span>
    </div>

    <div class="card-body">
        <div class="row"> 
            <div class="col-12 col-md-4">
                <div class="card">

                    <div class="card-body withdrawbox-v2" style="position: relative;">

                        <div class="form-group text-center">
                            <label>Withdrawal Method:</label>
                            <select class="" id="withdrawal-method">
                                <option value="bank">Bank Transfer</option>
                                <option value="crypto">Crypto</option>
                            </select>
                        </div>

                        <div class="tabs py-2" id="bank-form" style="">
                            <div class="card">
                                <div class="card-body p-0">
                                    <form method="POST" id="mainForm">

                                        <input type="hidden" value="w" name="type">
                                        <input type="hidden" value="b" name="payment_type">


                                        <div class="form-group">
                                            <label for="bank"style="color:#ffffff;">Bank</label>
                                            <input type="text" value="{{$bankInfo[0]->bank}}" name="bank">
                                        </div>

                                        <div class="form-group">
                                            <label for="acc_no" style="color:#ffffff;">Account Number</label>
                                            <input type="text" value="{{$bankInfo[0]->acc_no}}" name="acc_no">
                                        </div>

                                        <div class="form-group">
                                            <label for="acc_name" style="color:#ffffff;">Account Name</label>
                                            <input type="text" value="{{$bankInfo[0]->name}}" name="acc_name">
                                        </div>

                                        <div class="form-group" style="padding-top: 5px;text-align:center;">
                                            <input class="" id="amount" name="amount" type="number" placeholder="Min. withdrawal: 50">
                                            <div class="btn-wrap mt-2">
                                                <button class="btn btn-amount" data-value="50" type="button">50</button>
                                                <button class="btn btn-amount" data-value="100" type="button">100</button>
                                                <button class="btn btn-amount" data-value="200" type="button">200</button>
                                            </div>
                                            <div class="btn-wrap">
                                                <button class="btn btn-amount" data-value="300" type="button">300</button>
                                                <button class="btn btn-amount" data-value="500" type="button">500</button>
                                                <button class="btn btn-amount" data-value="1000" type="button">1000</button>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <button class="btn btn-submit link-fill" type="submit" style="font-size:16px;">
                                            Withdraw
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- <div class="container py-2">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-9 col-lg-10" >

            <h1 style="color:#ffffff;text-align: center;">Withdraw</h1>
            <div class="card" style="background: transparent;">
                <div class="card-body withdrawbox" style="">
                    
                        <div class="card-body" style="padding-bottom:0px;">
                            <div class="row justify-content-center">

                                <div class="col-md-12 col-lg-8">

                                        <div class="form-group" style="padding-top: 5px;text-align: center;">
                                            <label style="color:#ffffff;font-size:16px;font-size:25px;">Withdraw Option </label>
                                        </div>

                                </div>
                            </div>   
                            <div class="row justify-content-center">



                                <div class="col-md-12 col-lg-4">

                                    <div class="form-group">
                                        <div id="deposit-bank" class="tab-inner-button-item tab-selected" style="padding:5px;" onclick="showTab(1);">
                                            <span style="color:#ffffff;font-size:16px;" >Bank</span>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-12 col-lg-4">

                                    <div class="form-group">
                                        <div id="deposit-crypto" class="tab-inner-button-item" style="padding:5px;" onclick="showTab(2);">
                                            <span style="color:#ffffff;font-size:16px;">Crypto Payment</span>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <hr style="background: rgba(155,225,255,.96);">

                        <div class="card-body" id="bank-form">    

                            <form method="POST" id="mainForm">
                                <input type="hidden" value="w" name="type">
                                <input type="hidden" value="b" name="payment_type">
                                <input type="hidden" value="{{$bankInfo[0]->bank}}" name="bank">
                                <input type="hidden" value="{{$bankInfo[0]->acc_no}}" name="acc_no">
                                <input type="hidden" value="{{$bankInfo[0]->name}}" name="acc_name">
                                <div class="row justify-content-center">

                                        <div class="col-md-12 col-lg-4">

                                            <div class="form-group" style="padding-top: 5px;text-align:center;">
                                                <label style="color:#ffffff;font-size:16px;">Amount </label>
                                                <label style="color:red;">* </label>
                                                <input id="amount" name="amount" type="text" >
                                            </div>

                                        </div>
                                </div>

                                <div class="row justify-content-center">

                                        <div class="col-md-12 col-lg-4 ">

                                            <div class="form-group" style="padding-top: 5px;text-align:center;">
                                                <label style="color:#ffffff;font-size:16px;">Currency</label>
                                                <label style="color:red;">* </label>
                                                 <input id="currency" class="ember-text-field" type="text" value="{{ $currency }}" disabled>
                                            </div>

                                        </div>
      
                                </div>     
 
                                <div class="row justify-content-center">

                                        <div class="col-12 col-md-6 col-lg-2 ">

                                            <div class="form-group">
                                                <button class="btn btn-submit link-fill" type="submit" style="font-size:16px;">
                                                Withdraw
                                                </button>

                                            </div>

                                        </div>
                                </div>
                            </form>                        
                        </div>


                        <div class="card-body" id="crypto-form" style="display:none;">    

                            <form method="POST" id="mainForm-crypto">
                            <input type="hidden" value="w" name="type">
                            <input type="hidden" value="c" name="payment_type">
                            <div class="row justify-content-center">

                                    <div class="col-md-12 col-lg-4">

                                        <div class="form-group" style="padding-top: 5px;text-align:center;">
                                            <label style="color:#ffffff;font-size:16px;">{{__('app.dw.new.crypto_currency') }} </label>
                                            <label style="color:red;">* </label>
                                            <select name="crypto_currency">
                                                <option value="usdt">USDT</option>
                                            </select>
                                        </div>

                                    </div>
                            </div>

                            <div class="row justify-content-center">

                                    <div class="col-md-12 col-lg-4 ">

                                        <div class="form-group" style="padding-top: 5px;text-align:center;">
                                            <label style="color:#ffffff;font-size:16px;">{{__('app.dw.new.amount') }} </label>
                                            <label style="color:red;">* </label>
                                            <input id="amount" name="amount" type="text" >
                                            <div id="rate">= 0.00 USDT</div>
                                        </div>

                                    </div>
  
                            </div>     
                            <div class="row justify-content-center">

                                    <div class="col-md-12 col-lg-4 ">

                                        <div class="form-group" style="padding-top: 5px;text-align:center;">
                                            <label style="font-size:16px;">{{__('app.dw.new.wallet_add') }}</label>
                                            <label style="color:red;">* </label>
                                            <input name="address" type="text">
                                            <div>{{__('app.dw.new.wallet_add.digits') }}</div>
                                        </div>

                                    </div>
                            </div>  
                            <div class="row justify-content-center">

                                    <div class="col-12 col-md-6 col-lg-2">

                                        <div class="form-group">
                                            <button class="btn btn-submit link-fill" type="submit" style="font-size:16px;">
                                            Withdraw
                                            </button>
                                        </div>

                                    </div>
                            </div> 
                            </form>                       
                        </div>


                    
                </div>
            </div>
        </div>
    </div>   

</div> -->
@endsection


