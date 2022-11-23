@extends('my_profile')

@section('head')
<script src="{{ asset('js/qrcode.js') }}"></script>
<script type="text/javascript">

	$(document).ready(function() 
	{
		prepareLocale();
		
		//maintain
		// $('form').hide();
  //       $('.maintenance').show()

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
	            	val = utils.formatMoney(val*obj);
	            	$("#rate").html('= '+val+' KRW');
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

	function copyQr() 
	{
		var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(document.getElementById("qrstring").innerHTML).select();
        var res = document.execCommand('copy');
        $temp.remove();
	}

	function showSuccess() 
	{
        $('form').hide();
        $('.success-msg').show()
    }

	function submitMainForm()
	{   
	    if($("#mainForm").attr("enabled") == 0)
	    {
	        return;
	    }

	    $("#mainForm").attr("enabled",0);

	    if ($('#step1').is(":hidden"))
	    {
	    	showSuccess();
	    }
	    
	    $.ajax({
	        url: "/ajax/dw/crypto-create",
	        type: "POST",
	        data:  new FormData($("#mainForm")[0]),
	        contentType: false,
	        cache: false,
	        processData:false,
	        success: function(data)
	        {
	            $("#mainForm").attr("enabled",1);

	            var obj = JSON.parse(data);

	            if(obj.status == 1)
	            {
	            	new QRCode(document.getElementById("qrcode"), {
							text: obj.address,
							width: 100,
							height: 100,
							colorDark : "#000000",
							colorLight : "#ffffff",
						});

	            	$("#qrstring").html(obj.address);
	            	$("#crypto_currency").html(obj.token_amount+" USDT = "+obj.amount+" KRW");

	                $("#step1").hide();
	                $("#step2").show();
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
	        <span class="span-title"></span>
	    </div>
	    <div class="profile-container">
	        <div class="section-body">
	            <div id="content-top-bar" class="tab-container clearfix">
	            </div>
	            <div data-tab="deposit" class="deposit-container section-tab tab-focus">
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
	                    	<div data-url="/my_profile/deposit/new?crypto" class="tab-inner-button-item selected">
	                            <span>{{__('app.dw.new.paymenttype.crypto') }}</span>
	                        </div>
	                        <div data-url="/my_profile/deposit/new" class="tab-inner-button-item">
	                            <span>{{__('app.dw.new.paymenttype.cash') }}</span>
	                        </div>
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

                        <div class="success-msg" style="display: none;">
                            <p>{{__('app.dw.new.crypt.deposit.success1') }}</p>
                            <br>
                            <p>{{__('app.dw.new.crypt.deposit.success2') }}</p>
                            <br>
                            <!-- <p>{{__('app.dw.new.crypt.deposit.success3') }}</p>
                            <br> -->
                            <p>{{__('app.dw.new.crypt.deposit.success4') }}</p>
                        </div>

                        <!-- <div class="maintenance">
		                    Page Maintenance!
		                </div> -->

	                    <form method="POST" id="mainForm">
	                        <div id="step1" class="form-container">

	                        	<input type="hidden" value="d" name="type">
	                        	<input type="hidden" value="crypto" name="payment_type">

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
	                                    <label>{{__('app.dw.new.amountnumber') }} *</label>
	                                    <div class="input-wrapper"> 
	                                    	<input id="amount" name="amount" type="text">
	                                    </div>
	                                    <div id="rate"> = 0.00 KRW</div>
	                                </div>
	                            </div>
	                            <div class="form-btn">
	                                <button class="btn btn1" type="submit">{{ __('app.dw.new.button.request') }}</button>
	                            </div>
	                        </div>

	                        <div id="step2" style="display: none;text-align: center;">
	                            <div id="crypto_currency"></div>
	                            <input type="hidden" id="copy_text" value="">
	                            <div style="margin: 20px 0;">
	                                <div>
	                                    {{ __('app.dw.new.scantopay') }}
	                                </div>
	                                <div id="qrcode" class="qr"
	                                    style="background-color: white;padding: 5px; width: 138px; height: 138px;margin:10px auto;">
	                                </div>
	                                <div id="qrstring"></div>
	                                <a href="javascript:void(0)" onclick="copyQr()">{{ __('app.dw.new.copyaddress') }}</a>
	                            </div>
	                            <div style="margin-bottom: 20px;">{{__('app.dw.new.text') }}</div>
	                            <div class="form-btn">
	                                <button class="btn btn1" style="display: block;margin: auto;"
	                                    type="submit">{{__('app.dw.new.button.confirm') }}</button>
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