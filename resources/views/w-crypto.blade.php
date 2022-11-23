@extends('my_profile')

@section('head')
<script type="text/javascript">

	$(document).ready(function() 
	{
		prepareLocale();

        //maintain
        // $('form').hide();
        // $('.maintenance').show()

        //onkeyup rate
        $("#amount").keyup(function () {
            var val = $(this).val();
            val = val.replace(/[^0-9\.]/g, '');

            $.ajax({
            url: "/ajax/dw/crypto-rate",
            type: "GET",
            success: function(data)
            {
                var obj = JSON.parse(data);

                if(obj != '')
                {
                    val = utils.formatMoney(val/obj);
                    $("#rate").html('= '+val+' USDT');
                }
                else
                {
                    alert('Get Crypto Rate Error!');
                }
            },
            error: function(){}             
            });
        });

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
	        url: "/ajax/dw/crypto-create",
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

    function showSuccess() 
    {
        $('form').hide();
        $('.success-msg').show()
    }
</script>
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
                <div data-tab="withdraw" class="withdraw-container section-tab tab-focus">
                    <div class="tab-inner-container clearfix">
                        <div data-url="/my_profile/withdraw/new?crypto" class="tab-inner-item section-inner-tab selected">
                            <span>{{__('app.dw.new.w_status') }}</span>
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
                            <div data-url="/my_profile/withdraw/new?crypto" class="tab-inner-button-item selected">
                                <span>{{__('app.dw.new.paymenttype.crypto') }}</span>
                            </div>
                            <div data-url="/my_profile/withdraw/new" class="tab-inner-button-item">
                                <span>{{__('app.dw.new.paymenttype.cash') }}</span>
                            </div>
<!--                             <div data-url="/my_profile/withdraw/new?bank" class="tab-inner-button-item">
                                <span>{{__('app.dw.new.paymenttype.bank') }}</span>
                            </div> -->
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
                        <div class="success-msg" style="display: none;">
                            <p>{{__('app.dw.new.crypt.withdraw.success1') }}</p>
                            <br>
                            <p>{{__('app.dw.new.crypt.withdraw.success2') }}</p>
                            <br>
                            <p>{{__('app.dw.new.crypt.withdraw.success3') }}</p>
                            <br>
                            <p>{{__('app.dw.new.crypt.withdraw.success4') }}</p>
                        </div>

                        <!-- <div class="maintenance">
                            Page Maintenance!
                        </div> -->

                        <form method="POST" id="mainForm">
                            @csrf
                            <div style="max-width: 475px;padding-top: 10px;overflow: visible;margin: 0 auto;" class="form-container">

                            	<input type="hidden" value="w" name="type">
                            	<input type="hidden" value="crpyto" name="payment_type">

                                <div class="form-group">
                                    <div class="form-element">
                                        <label>{{__('app.dw.new.crypto_currency') }} *</label>
                                        <div class="input-wrapper select">
                                            <select name="crypto_currency">
                                                <option value="usdt">USDT</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-element">
                                        <label>{{__('app.dw.new.amount') }} *</label>
                                        <div class="input-wrapper"> 
                                        	<input id="amount" name="amount" type="text">
                                        </div>
                                        <div id="rate">= 0.00 USDT</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-element">
                                        <label>{{__('app.dw.new.wallet_add') }} *</label>
                                        <div class="input-wrapper">
                                            <input name="address" type="text">
                                        </div>
                                        <div>{{__('app.dw.new.wallet_add.digits') }}</div>
                                    </div>
                                </div>
                                <div class="form-btn">
                                    <button class="btn btn1" type="submit">{{__('app.dw.new.button.request') }}</button>
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