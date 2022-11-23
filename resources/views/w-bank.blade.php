@extends('my_profile')

@section('head')
<script type="text/javascript">

    $(document).ready(function() 
    {
        prepareLocale();

        $("#mainForm").attr("enabled",1);

        $("#mainForm").on('submit',(function(e){
            e.preventDefault();
            submitMainForm();
        }));

        utils.formatCurrencyInput($("#amount"));

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

                var obj = JSON.parse(data);

                if(obj.status == 1)
                {
                    alert(locale['success']);
                    window.location.href = "/my_profile/dw";
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
@endsection

@section('details')
<div class="body">
    <div class="account-info section" data-section="profile">
        <div class="title">
            <span></span>
        </div>
        <div class="profile-container">
            <div class="section-body">
                <div id="content-top-bar" class="tab-container clearfix">
                </div>
                <div data-tab="withdraw" class="withdraw-container section-tab tab-focus">
                    <div class="tab-inner-container clearfix">
                        <div data-url="/my_profile/withdraw/new" class="tab-inner-item section-inner-tab selected">
                            <span>{{__('app.dw.new.w_methods') }}</span>
                        </div>
                        <div data-url="/my_profile/withdraw/new?status" class="tab-inner-item section-inner-tab">
                            <span>{{__('app.dw.new.w_status') }}</span>
                        </div>
                    </div>
                    <script>
                        $('.tab-inner-container > div').on('click', function () {
                            location.href = $(this).attr('data-url');
                        })
                    </script>
                    <div id="withdrawmethods" class="withdraw-container-item">
                        <div class="tab-inner-button-container">
                            <div data-url="/my_profile/withdraw/new" class="tab-inner-button-item">
                                <span>{{__('app.dw.new.paymenttype.cash') }}</span>
                            </div>
                            <div data-url="/my_profile/withdraw/new?bank" class="tab-inner-button-item selected">
                                <span>{{__('app.dw.new.paymenttype.bank') }}</span>
                            </div>
                            <div data-url="/my_profile/withdraw/new?crypto" class="tab-inner-button-item">
                                <span>{{__('app.dw.new.paymenttype.crypto') }}</span>
                            </div>
                        </div>
                        <hr style="background: white;margin: 20px 10px;">
                        <script>
                            (function () {
                                $('.tab-inner-button-container > .tab-inner-button-item').on(
                                    'click',
                                    function () {
                                        location.href = $(this).attr('data-url');
                                    })
                            })()
                        </script>
                        <!-- <div class="success-msg" style="display: none;">
                            <p>Dear Player.</p>
                            <br>
                            <p>Your withdrawal request has been accepted! The request will be
                                approved within 12 hours.</p>
                            <br>
                            <p>You will receive a notification message in your registered e-mail
                                address once the transfer initiated.</p>
                            <br>
                            <p>E-mail us to: 123123@abc.com for any other quires.</p>
                        </div> -->
                        <form method="POST" id="mainForm">
                            <style>
                                #banktransfer .banktransfer-container {
                                    display: flex;
                                    flex-wrap: wrap;
                                    justify-content: center;
                                }

                                #banktransfer .banktransfer-container>div:nth-child(1) {
                                    display: flex;
                                    flex-direction: column;
                                    justify-content: center;
                                }

                                #banktransfer .banktransfer-container>div:nth-child(1)>* {
                                    margin: 5px 0;
                                }

                                #banktransfer .banktransfer-container>div:nth-child(2) {
                                    border-right: 2px solid white;
                                    margin: 0 30px;
                                }

                                @media (max-width: 1580px) {
                                    #banktransfer .banktransfer-container>div:nth-child(2) {
                                        display: none;
                                    }
                                }

                                #banktransfer .banktransfer-container>div:not(:nth-child(2)) {
                                    padding: 20px 50px;
                                    border: 1px solid white;
                                    border-radius: 20px;
                                    width: 500px;
                                    margin: 40px 20px;
                                }

                                #banktransfer .banktransfer-container>div:not(:nth-child(2)) * {
                                    color: white;
                                }
                            </style>
                            <div id="banktransfer">
                                <div class="banktransfer-container">
                                    <div id="bank-details-col">
                                        @foreach ($data as $d)
                                        <div class="form-group" style="color:#363636; font-size: 12px;">
                                            <label>{{__('app.dw.new.bankname') }} :</label>
                                                <b>{{ $d->bank }}</b><br>
                                            <label>{{__('app.dw.new.bankacc') }} :</label>
                                                <b>{{ $d->acc_no }}</b><br>
                                            <label>{{__('app.dw.new.bankadd') }} :</label>
                                                <b>{{ $d->address }}</b>
                                         </div>
                                         @endforeach
                                        <a href="/my_profile/bank_info" style="align-self: flex-end;">Edit</a>
                                    </div>
                                    <div></div>
                                    <div class="form-container">
                                        <div class="form-group">
                                            <div class="form-element">
                                                <label>{{__('app.dw.new.amount') }} *</label>
                                                <div class="input-wrapper">
                                                    <input name="amount" type="text">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="form-element">
                                                <label>{{__('app.dw.new.w_pin') }} *</label>
                                                <div class="input-wrapper">
                                                    <input name="w_pin" type="text">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-btn">
                                            <button class="btn btn1" type="submit">{{__('app.dw.new.button.request') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
