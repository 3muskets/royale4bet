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
                    window.location.href = "/history";
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
    select,input
    {
        box-shadow: none !important;
        outline: none !important;
    }

    select,input::placeholder
    {
        color: #000 !important;
    }

    input::placeholder
    {
        color: #bcbcbc !important;
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
    Withdrawal
</div>

<div class="w-100 p-2">
    <div style="background:white;border-radius:5px">
        <div class="py-4 px-2">
            <div class="container-fluid">

                <form style="" method="POST" id="mainForm">

                    <input type="hidden" value="w" name="type">
                    <input type="hidden" value="b" name="payment_type">
                    <input type="hidden" value="{{$bankInfo[0]->bank}}" name="bank">
                    <input type="hidden" value="{{$bankInfo[0]->name}}" name="acc_name">
                    <input type="hidden" value="{{$bankInfo[0]->acc_no}}" name="acc_no">

<!--                     <div class="form-group row" style="align-items: center">
                        <label class="col-sm-2 col-form-label">Withdrawal Channel:</label>
                        <div class="col-sm-10">
                            <div style="display: flex">
                                <div class="mr-2 bank-option selected" style="cursor: pointer; padding: 5px;">
                                    <img src="/images/payment/maybank.png" style="width: 70px;">
                                </div>

                                <div class="mr-2 bank-option" style="cursor: pointer;">
                                    <img src="/images/payment/cimb.png" style="width: 70px;">
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <div class="form-group row" style="align-items: center">
                        <label class="col-sm-2 col-form-label">Bank Details:</label>

                        <div class="col-sm-3" style="position: relative;">
                            <input class="" type="text" value="{{$bankInfo[0]->bank}}" disabled style="width: 100%">
   
                        </div>

                    </div>

                    <div class="form-group row" style="align-items: center">
                        <label class="col-sm-2 col-form-label"></label>
                        <div class="col-sm-3" style="position: relative;">
                            <input class="" type="text" value="{{$bankInfo[0]->name}}" disabled style="width: 100%">
    
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center">
                        <label class="col-sm-2 col-form-label"></label>
                        <div class="col-sm-3" style="position: relative;">
                            <input class="" type="text" value="{{$bankInfo[0]->acc_no}}" disabled  style="width: 100%">
          
                        </div>
                    </div>

                    <div class="form-group row" style="align-items: center">
                        <label class="col-sm-2 col-form-label">Amount:</label>
                        <div class="col-sm-3">
                            <input name="amount" type="number" placeholder="Max. withdrawal: 50,000" style="width: 100%">
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-3">
                            <button class="btn btn-submit" type="submit" style="font-size:14px;">
                                Withdraw
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div style="background:white;border-radius:5px; margin-top: 10px;">
        <div class="py-4 px-2">
            <div class="container-fluid">

                <ul id="notice">
                    <i class='fa fa-exclamation-triangle' style="color: #dd214c; font-size: 14px; padding: 10px 0;"> Important Notice</i>
                    <li>
                        Kindly check with our 24/7 LIVECHAT if your transaction is pending for more than 10 minutes.
                    </li>
                    <li>
                        Withdrawal bank account name must match with registered full name, member is not allow withdrawal to 3rd party bank account.
                    </li>
                    <li>
                        Please make sure your turnover requirement has been achieved before making a withdrawal transaction to avoid inconvenience.
                    </li>
                    <li>
                        If there is any discrepancy or you may have any other further withdrawal inquiries, kindly contact our 24/7 LIVECHAT. Thank you.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- <div class="card" style="background: transparent;">

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
</div> -->
@endsection


