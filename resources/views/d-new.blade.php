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
                    window.location.href = "/my_profile/deposit/new?status";
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
</style>

@endsection

@section('details')
<div class="body">
    <div class="account-info section" data-section="profile">
        <div class="title">
            <span class="span-title"></span>
        </div>
        <div class="profile-container">
            <div class="section-body">
                <div id="content-top-bar" class="tab-container clearfix">
                </div>
                <div data-tab="deposit" class="deposit-container section-tab tab-focus selected">
                    <div class="tab-inner-container clearfix">
                        <div data-url="/my_profile/deposit/new?crypto" class="tab-inner-item section-inner-tab selected">
                            <span>{{__('app.dw.new.d_methods') }}</span>
                        </div>
                        <div data-url="/my_profile/deposit/new?status" class="tab-inner-item section-inner-tab">
                            <span>{{__('app.dw.new.d_status') }}</span>
                        </div>
                    </div>
                    <script>
                        $('.tab-inner-container > div').on('click', function () {
                            location.href = $(this).attr('data-url');
                        })
                    </script>
                    <div class="deposit-container-item">
                        <div class="tab-inner-button-container">
                            <div data-url="/my_profile/deposit/new?crypto" class="tab-inner-button-item ">
                                <span>{{__('app.dw.new.paymenttype.crypto') }}</span>
                            </div>
                            <div data-url="/my_profile/deposit/new" class="tab-inner-button-item selected">
                                <span>{{__('app.dw.new.paymenttype.cash') }}</span>
                            </div>
<!--                             <div data-url="/my_profile/deposit/new?card" class="tab-inner-button-item ">
                                <span>{{__('app.dw.new.paymenttype.card') }}</span>
                            </div> -->
                        </div>
                        <hr style="background: white;margin: 20px 10px;">
                        <script>
                            (function () {
                                $('.tab-inner-button-container > .tab-inner-button-item').on('click',
                                    function () {
                                        location.href = $(this).attr('data-url');
                                    })
                            })()
                        </script>
                        <!-- <div class="success-msg" style="display: none;">
                            <p>Dear Player.</p>
                            <br>
                            <p>Your account Top-up request has been accepted! Contact your agent to complete
                                the
                                process.</p>
                            <br>
                            <p>Check“ DEPOSIT STATUS” in your wallet to follow up.</p>
                            <br>
                            <p>E-mail us to: 123123@abc.com for any other quires.</p>
                        </div> -->
                        <form method="POST" id="mainForm">
                            @csrf
                            <div class="form-container">

                                <input type="hidden" value="d" name="type">
                                <input type="hidden" value="c" name="payment_type">

                                <div class="form-group">
                                    <div class="form-element">
                                        <label>{{__('app.dw.new.amount') }} *</label>
                                        <div class="input-wrapper">
                                            <input name="amount" id="amount" class="ember-text-field" type="text">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-element">
                                        <label>{{__('app.dw.new.currency') }} *</label>
                                        <div class="input-wrapper  ">
                                            <input name="currency" class="ember-text-field" type="text" value="{{ $currency }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-element">
                                        <label>{{__('app.dw.new.ag_code') }} *</label>
                                        <div class="input-wrapper">
                                            <input name="reg_cd" class="ember-text-field" type="text" value="{{ $regCd }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-btn">
                                <button class="btn btn1" type="submit">{{__('app.dw.new.button.request') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
