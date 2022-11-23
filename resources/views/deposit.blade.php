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
	        submitMainForm(1);
	    }));



        $("#mainForm2").attr("enabled",1);

        $("#mainForm2").on('submit',(function(e){
            e.preventDefault();
            submitMainForm(2);
        }));

        $("#wallet-address").on('submit',(function(e){
            e.preventDefault();
            getWalletAddress();
        }));

        <!--- doit now --->
        $("#mainForm3").attr("enabled",1);

        $("#mainForm3").on('submit',(function(e){
            e.preventDefault();
            submitMainForm(3);
        }));


        if("{{ $walletAddr }}" != '')
        {

            new QRCode(document.getElementById("qrcode"), {
                    text: "{{ $walletAddr }}",
                    width: 100,
                    height: 100,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                });

            $("#qrstring").html("{{ $walletAddr }}"); 

            $("#bfr-request").hide();        
        }
        else
        {
            $("#bfr-request").show();  
            $("#aft-request").hide();
        }

        $("#deposit-method").on('change', function()
        {
            $('.tabs').hide();
            $("#"+this.value+"-form").show();
            $(".amount").val('');
        });

        $('.btn-wrap button').each(function()
        {
            $(this).click(function()
            {
                var wAmt = $(this).attr("data-value");
                var amt = +$(".amount").val();

                if(amt == '')
                {
                    amt = 0;
                }
                
                amt = +wAmt + amt;
                $(".amount").val(amt);
            });
        });
	});


    function prepareLocale() 
    {
        locale['info'] = "{!! __('common.modal.info') !!}";
        locale['success'] = "{!! __('common.modal.success') !!}";
        locale['error'] = "{!! __('common.modal.error') !!}";
    }

    function getWalletAddress()
    {
        var data = [];

        //hide get wallet button
        $('#wallet-btn').hide();

        $.ajax({
            type: "GET",
            url: "/ajax/getwalletaddress",
            data: data,
            success: function(data)
            {

                new QRCode(document.getElementById("qrcode"), {
                        text: data,
                        width: 100,
                        height: 100,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                    });

                $("#qrstring").html(data); 

                $("#bfr-request").hide();  
                $("#aft-request").show();
            }
        });
    }


	function submitMainForm(type)
	{ 
        var data;

        if(type == 1)
        {
            if($("#mainForm").attr("enabled") == 0)
            {
                return;
            }

            $("#mainForm").attr("enabled",0);

            data = new FormData($("#mainForm")[0]);
        }
        else if(type == 2)
        {
            if($("#mainForm2").attr("enabled") == 0)
            {
                return;
            }

            $("#mainForm2").attr("enabled",0); 

            data = new FormData($("#mainForm2")[0]);           
        }
        else if(type == 3)
        {
            if($("#mainForm3").attr("enabled") == 0)
            {
                return;
            }

            $("#mainForm3").attr("enabled",0); 

            data = new FormData($("#mainForm3")[0]);           
        }

        
        $.ajax({
            url: "/ajax/dw/create",
            type: "POST",
            data:  data,
            contentType: false,
            cache: false,
            processData:false,
            success: function(data)
            {
                $("#mainForm").attr("enabled",1);
                $("#mainForm2").attr("enabled",1);
                $("#mainForm3").attr("enabled",1);

                if(data.status == 1)
                {
                    if(data.payment_type == 'd' || data.payment_type == 'f')
                    {
                        var details = data.txn_details;
                        var container = document.getElementById('payment-form');

                        container.action = details.url;

                        delete details.status;
                        delete details.url;

                        var text = document.createElement('div');
                        text.class = "form-container";
                        text.id = "doitnow-container";

                        var textHtml = '';

                        var user = [];

                        for (var k in details) 
                        {
                            if (details.hasOwnProperty(k)) 
                            {
                                textHtml = textHtml+"<input type='hidden' value='"+details[k]+"' name='"+k+"'>"
                            }
                        }

                        text.innerHTML = textHtml;

                        container.appendChild(text);

                        document.forms["payment-form"].submit();
                    }
                    else
                    {
                        alert(locale['success']);
                        window.location.href = "/my_profile/deposit/new?status";                        
                    }
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



    function copyQr() 
    {
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(document.getElementById("qrstring").innerHTML).select();
        var res = document.execCommand('copy');
        $temp.remove();

         alert("Copy Success");

    }
</script>

<style type="text/css">
        
    .tab-inner-button-item
    {
        display: flex;
	    flex-shrink: 0;
	    text-align: center;
	    border: 1px solid white;
	    border-radius: 5px;
	    cursor: pointer;
	    border-color: #aaddf4;
	    color :white;
        width: 30%;
        margin-left: auto;
        margin-right: auto;
        transition: 0.5s;
    }
    .tab-inner-button-item span
    {
        width: 100%;
        align-self: center;
    }
    .tab-selected
    {
        background:linear-gradient(90deg,#eb77cf .97%,#8271f2);
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

    .depositbox
    {
        background-color: #140133;
        border-radius:10px;
        border: 4px solid rgba(155,225,255,.96);
        box-shadow: inset 0 20px 20px -20px #77a5eb;
    }
    .depositbox-v2
    {
        background: #23214a;
        border: 2px solid #9ee2fe;
        border-radius: 20px;
        box-shadow: inset 0 0 15px #77a5eb;
    }
    select,input
    {
        width: 100%;
/*        border: 2px solid #77a5eb !important;*/
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

    input::placeholder
    {
        color: #bcbcbc !important;
        text-align: center;
    }

    #mainForm input
    {
        text-align: center;
    }

    #qrstring
    {
        color:#ffffff;
        font-size:14px; 
        margin: 10px auto;
        width: 138px;
    }

    #qrcode
    {
        background-color: white;
        padding: 18px; 
        width: 138px; 
        height: 138px;
        margin:10px auto;
    }

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
    .button-tabs
    {
        background: linear-gradient(180deg,rgba(43,67,129,.96),rgba(31,10,90,.96));
        color: white;
        font-size: 12px;
        border-radius: 8px;
        padding: 10px 15px;
        margin: 0 15px;
        min-width: 120px;
        text-align: center;
    }
    #deposit-option
    {
        display: flex;
        width: 100%;
        transition: 0.5s;
        margin: 15px 0;
        padding: 0 15px;
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
    button:focus
    {
        box-shadow: none !important;
        outline: 0 !important;
    }
    @media(max-width: 767.98px)
    {
        #deposit-option
        {
            padding: 0;
        }

        #mainForm input
        {
            width: 100%;
        }
    }
</style>

@endsection

@section('content')

<div class="card" style="background: transparent;">

    <div class="card-header mb-0">
        <i class="fa fa-money" style="padding: 7px;"></i> <span>DEPOSIT</span>
    </div>

    <div class="card-body">

        <!--- list --->
<!--         <div class="d-flex">

            <div class="button-tabs">
                <i class="fa fa-money"></i> Withdraw
            </div>
            <div class="button-tabs">
                <i class="fa fa-history"></i> Deposit List
            </div>
            <div class="button-tabs">
                <i class="fa fa-history"></i> Withdraw List
            </div>

        </div>

        <div class="w-100 my-4" style="height: 2px; background: linear-gradient(90deg, rgba(25,54,177,0) 0%, rgba(43,67,129,.96) 50%, rgba(25,54,177,0) 100%)">
        </div> -->

        <!---- deposit method --->
        <div class="row mt-4"> 
            <div class="col-12 col-md-4">
                <div class="card">

                    <div class="card-body" style="position: relative;">

                        <div class="form-group">
                            <label>Deposit Method:</label>
                            <select class="" id="deposit-method">
                                <option value="f2f">FIAT2FIAT</option>
                                <option value="crypto">Crypto</option>
                                @if (sizeof($bankInfo) > 0)
                                    <option value="bank">Bank Transfer</option>
                                @endif
                                <option value="doitnow">Doit Now</option>
                            </select>
                        </div>

                        <div class="tabs py-4" id="f2f-form" style="display: block;">
                            <div class="card">
                                <div class="card-body p-4 depositbox-v2">
                                    <form method="POST" id="mainForm">
                                        <input type="hidden" value="d" name="type">
                                        <input type="hidden" value="f" name="payment_type">

                                        <div class="form-group">
                                            <input class="amount" name="amount" type="number" placeholder="Min. deposit: 50">
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

                                        
                                        <div class="form-group" style="padding-top:5px;">
                                            <div class="ml-2">
                                              <p>Promotion Option:</p>
                                              @foreach($promoList as $p)
                                              <input type="radio" name="promo_id" value="{{$promoList[0]->promo_id}}" style="width:13px;">
                                              <label>{{ $p->promo_name }}</label>
                                              @endforeach
                                              <input type="radio" id="nopromo" name="promo_id" value="" style="width:13px;" checked>
                                              <label for="nopromo">No, thanks.</label>
                                            </div>
                                        </div>

                                        

                                        <div class="form-group">
                                            <button class="btn btn-submit link-fill" type="submit" style="font-size:16px;">
                                            Deposit
                                            </button>

                                        </div>
                                    </form>
                                </div>
                            </div>                   
                        </div>

                        <div class="tabs py-4" id="crypto-form" style="display: none;">
                            <div class="card">
                                <div class="card-body p-4 depositbox-v2">
                                    <div id="bfr-request">
                                        <div style="color:#ffffff;font-size:14px;">Please Press Request Button to request the wallet address</div>
                                        <br>
                                        <div class="form-btn">
                                            <button class="btn-submit" type="submit" id="wallet-btn" onclick="getWalletAddress();">Request</button>
                                        </div>
                                    </div>

                                    <input type="hidden" id="copy_text" value="">
                                    <div id="aft-request" class="" style="text-align: center;">
                                        <div class="form-group">
                                            <div style="color:#ffffff;font-size:14px;">Chain Type :USDT TRC20</div>
                                            <div style="color:#ffffff;font-size:14px;">
                                                Scan To Pay
                                            </div>
                                            <div id="qrcode" class="qr">
                                            </div>
                                            <div id="qrstring"></div>
                                            <a href="javascript:void(0)" style="font-size:16px;" onclick="copyQr()">{{ __('app.dw.new.copyaddress') }}</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="tabs py-4" id="bank-form" style="display: none;">
                            <div class="card">
                                <div class="card-body p-4 depositbox-v2">
                                    <form method="POST" id="mainForm2">
                                        <input type="hidden" value="d" name="type">
                                        <input type="hidden" value="b" name="payment_type">
                                        <input type="hidden" value="{{$bankInfo[0]->id}}" name="admin_bank_id">

                                        <div class="form-group">
                                            <div class="d-flex" style="align-items: center;">
                                                
                                                <div class="ml-2">
                                                    <div id="bank"style="color:#ffffff;">Deposit Bank: {{$bankInfo[0]->bank}}</div>
                                                    <input type="hidden" value="{{$bankInfo[0]->bank}}" name="bank">

                                                    <div id="acc_no" style="color:#ffffff;">Deposit Account Number: {{$bankInfo[0]->acc_no}}</div>
                                                    <input type="hidden" value="{{$bankInfo[0]->acc_no}}" name="acc_no">

                                                    <div id="acc_name" style="color:#ffffff;">Deposit Account Name: {{$bankInfo[0]->name}}</div>
                                                    <input type="hidden" value="{{$bankInfo[0]->name}}" name="acc_name">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group" style="padding-top: 5px;text-align:center;">
                                            <input class="amount" name="amount" type="number" placeholder="Min. deposit: 50">
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

                                        <div class="form-group" style="padding-top:5px;">
                                            <div class="ml-2">
                                              <p>Promotion Option:</p>
                                              @foreach($promoList as $p)
                                              <input type="radio" name="promo_id" value="{{$promoList[0]->promo_id}}" style="width:13px;">
                                              <label>{{ $p->promo_name }}</label>
                                              @endforeach
                                              <input type="radio" id="nopromo" name="promo_id" value="" style="width:13px;" checked>
                                              <label for="nopromo">No, thanks.</label>
                                            </div>
                                        </div>

                                        <div class="form-group" style="padding-top: 5px;text-align:center;">
                                            <input id="currency" class="ember-text-field text-center" type="text" value="Currency: MYR" disabled>
                                        </div>
                                        <div>
                                              <label for="img">Select image:</label>
                                              <input type="file" id="img" name="img" accept="image/*">
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn-submit link-fill" type="submit" style="font-size:16px;">
                                            Deposit
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="tabs py-4" id="doitnow-form" style="display: none;">
                            <div class="card">
                                <div class="card-body p-4 depositbox-v2">
                                    <form method="POST" id="mainForm3">
                                        <input type="hidden" value="d" name="type">
                                        <input type="hidden" value="d" name="payment_type">

                                        <div class="form-group">
                                            <input class="amount" name="amount" type="number" placeholder="Min. deposit: 50">
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

                                        <div class="form-group" style="padding-top:5px;">
                                            <div class="ml-2">
                                              <p>Promotion Option:</p>
                                              @foreach($promoList as $p)
                                              <input type="radio" name="promo_id" value="{{$promoList[0]->promo_id}}" style="width:13px;">
                                              <label>{{ $p->promo_name }}</label>
                                              @endforeach
                                              <input type="radio" id="nopromo" name="promo_id" value="" style="width:13px;" checked>
                                              <label for="nopromo">No, thanks.</label>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <button class="btn btn-submit link-fill" type="submit" style="font-size:16px;">
                                            Deposit
                                            </button>

                                        </div>
                                    </form>
                                </div>
                            </div>                   
                        </div>

                        <form method="POST" name="doitnowPayment" id="payment-form">
                            @csrf
                            <div class="form-container" id="doitnow-container">
                            </div>
                            <div class="form-btn">
                                <button class="btn btn1"  style="display: none" type="submit">{{__('app.dw.new.button.request') }}</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>



@endsection


