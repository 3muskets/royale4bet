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

        $('.bank-option').click(function()
        {
            $('.bank-option').removeClass('selected');
            this.className += " selected";

            $("#bank").val($(this).attr("data-id"));
            $("#acc_name").val($(this).attr("data-name"));
            $("#acc_no").val($(this).attr("data-acc"));
            $("#amount").attr("placeholder", "Min:"+$(this).attr("data-min")+" | Max:"+$(this).attr("data-max"));
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
                        window.location.href = "/history";                        
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
    select,input
    {
        box-shadow: none !important;
        outline: none !important;
    }

    select,input::placeholder
    {
        color: #000 !important;
        text-align: center;
    }

    input::placeholder
    {
        color: #bcbcbc !important;
        text-align: center;
    }

    label
    {
        color: #000;
    }
    button:focus
    {
        box-shadow: none !important;
        outline: 0 !important;
    }

    .page-title
    {
        background:#27273F;
        font-size:16px;
        font-weight: bold;
    }
    
    .bank-option
    {
        display: flex;
        align-items: center;
    }

    .bank-option.selected
    {
        border: 1px solid black;
    }

    .btn-submit
    {
        color: #ffffff;
        margin: 0;
        padding: 0;
        border-radius: 5px;
        border:0;
        background: #CF2029;
        padding: 5px;
        width: 100%;
    }
    #notice
    {
        list-style-type: none;
    }
    #notice li
    {
        color: darkgrey;
    }
    ul li::before 
    {
        content: "\2022";
        color: #dd214c;
        font-weight: bold;
        display: inline-block; 
        width: 1em;
        margin-left: -1em;
    }
</style>

@endsection

@section('content')

<div class="w-100 page-title p-2">
    Deposit
</div>

<div class="w-100 p-2">
    <div style="background:white;border-radius:5px">
        <div class="py-4 px-2">
            <div class="container-fluid">
                <form>
                    <div class="form-group row" style="align-items: center">
                        <label class="col-sm-2 col-form-label">Deposit Method:</label>
                        <div class="col-sm-10">
                            <select class="" id="deposit-method">
                                
                                @if (sizeof($bankInfo) > 0)
                                <option value="bank">Bank Transfer</option>
                                @endif
                                <option value="ob">Quick Pay</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div id="bank-form" style="">
                    <form style="" method="POST" id="mainForm">

                        <input type="hidden" value="d" name="type">
                        <input type="hidden" value="b" name="payment_type">
                        <input type="hidden" value="{{$bankInfo[0]->bank_id}}" id="bank" name="admin_bank_id">

                        <div class="form-group row" style="align-items: center">
                            <label class="col-sm-2 col-form-label">Deposit Channel:</label>
                            <div class="col-sm-10">
                                <div style="display: flex">
                                    @foreach($bankInfo as $b)
                                        @if($loop->index == 0)
                                        <div class="mr-2 bank-option selected" style="cursor: pointer; padding: 5px;" data-id="{{$b->bank_id}}" data-name="{{$b->name}}" data-acc="{{$b->acc_no}}" data-min="{{$b->min_deposit_amt}}" data-max="{{$b->max_deposit_amt}}">
                                            <img src="/images/payment/{{$b->bank_img}}.png" style="width: 70px;">
                                        </div>
                                        @else
                                        <div class="mr-2 bank-option" style="cursor: pointer; padding: 5px;" data-id="{{$b->bank_id}}" data-name="{{$b->name}}" data-acc="{{$b->acc_no}}" data-min="{{$b->min_deposit_amt}}" data-max="{{$b->max_deposit_amt}}">
                                            <img src="/images/payment/{{$b->bank_img}}.png" style="width: 70px;">
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="form-group row" style="align-items: center">
                            <label class="col-sm-2 col-form-label">Bank Details:</label>
                            <div class="col-sm-3" style="position: relative;">
                                <input class="" type="text" value="{{$bankInfo[0]->name}}" id="acc_name" name="acc_name" disabled style="width: 100%">
                                <i class="fa fa-copy" id="fa-copy-bank" style="color: #000; position: absolute; top: 50%; right: 10%; transform: translateY(-50%); cursor: pointer"></i>
                            </div>

                            <div class="col-sm-3" style="position: relative;">
                                <input class="" type="text" value="{{$bankInfo[0]->acc_no}}" id="acc_no" name="acc_no" disabled  style="width: 100%">
                                <i class="fa fa-copy" id="fa-copy-bankacc" style="color: #000; position: absolute; top: 50%; right: 10%; transform: translateY(-50%); cursor: pointer"></i>
                            </div>
                        </div>

                        <div class="form-group row" style="align-items: center">
                            <label class="col-sm-2 col-form-label">Amount:</label>
                            <div class="col-sm-3">
                                <input name="amount" type="number" id="amount" placeholder="Min:{{$bankInfo[0]->min_deposit_amt}} | Max:{{$bankInfo[0]->max_deposit_amt}}" style="width:100%;">
                            </div>
                        </div>

                        <div class="form-group row" style="align-items: center">
                            <label class="col-sm-2 col-form-label">Promotion:</label>
                            <div class="col-sm-10">
                                <select class="" id="promo_id">
                                    <option value="" style="width:13px;">No, thanks</option>
                                    @foreach($promoList as $p)
                                    <option value="{{$p->promo_id}}" style="width:13px;">{{ $p->promo_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row" style="align-items: center">
                            <label class="col-sm-2 col-form-label">Upload Receipt:</label>
                            <div class="col-sm-10">
                                <input type="file" id="img" name="img" accept="image/*" style='color: #000'>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-3">
                                <button class="btn btn-submit" type="submit" style="font-size:14px;">
                                    Deposit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="online-banking">
                </div>

            </div>
        </div>
    </div>

    <div style="background:white;border-radius:5px; margin-top: 10px;">
        <div class="py-4 px-2">
            <div class="container-fluid">

                <ul id="notice">
                    <i class='fa fa-exclamation-triangle' style="color: #dd214c; font-size: 14px; padding: 10px 0;"> Important Notice</i>
                    <li>
                        Always check for the latest active deposit bank details before making a deposit.
                    </li>
                    <li>
                        For using deposit option "Bank Transfer", Please make the transfer before submit the transaction to avoid the transaction is delay.
                    </li>
                    <li>
                        Please DO NOT fill "ROYALE" # or any sensitive words related to gambling as reference/remark in your online transfer transaction.
                    </li>
                    <li>
                        Please take note that 1x turnover is required for all deposits made before any withdrawal can be processed.
                    </li>
                    <li>
                        Depositor’s ACCOUNT NAME must match with registered full name. We do not encourage transaction made using 3rd party/company account.
                    </li>
                    <li>
                        Kindly check with our 24/7 LIVECHAT if your deposit amount has been deducted from bank account but not receive the credit or the transaction is showing pending/reject.
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection


