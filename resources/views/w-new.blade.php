@extends('my_profile')

@section('head')

<script type="text/javascript">
	
	var date = utils.getCurrentDateTimeWithoutDay();

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
	                window.location.href = "/my_profile/withdraw/new?status";
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

<style>
	#main-table
	{
		font-size: 12px;
	}
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
	            <div data-tab="withdraw" class="withdraw-container section-tab tab-focus">
	                <div class="tab-inner-container clearfix">
	                    <div data-url="/my_profile/withdraw/new?crypto" class="tab-inner-item section-inner-tab selected">
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
	                    	<div data-url="/my_profile/withdraw/new?crypto" class="tab-inner-button-item ">
	                            <span>{{__('app.dw.new.paymenttype.crypto') }}</span>
	                        </div>
	                        <div data-url="/my_profile/withdraw/new" class="tab-inner-button-item selected">
	                            <span>{{__('app.dw.new.paymenttype.cash') }}</span>
	                        </div>
<!-- 	                        <div data-url="/my_profile/withdraw/new?bank" class="tab-inner-button-item ">
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

	                    <form method="POST" id="mainForm">
	                    	@csrf
	                        <div style="max-width: 475px;padding-top: 10px;overflow: visible;margin: 0 auto;" class="form-container">

	                        	<input id="type" type="hidden" value="w" name="type">
	                        	<input type="hidden" value="c" name="payment_type">

	                            <div class="form-group">
	                                <div class="form-element">
	                                    <label>{{__('app.dw.new.amount') }} *</label>
	                                    <div class="input-wrapper">
	                                    	<input name="amount" id="amount" type="text">
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="form-group">
	                                <div class="form-element">
	                                    <label>{{__('app.dw.new.currency') }} *</label>
	                                    <div class="input-wrapper"> 
	                                    	<input name="currency" type="text" value="{{ $currency }}" readonly>
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="form-group">
	                                <div class="form-element">
	                                    <label>{{__('app.dw.new.ag_code') }} *</label>
	                                    <div class="input-wrapper"> 
	                                    	<input name="reg_cd" type="text" value="{{ $regCd }}" readonly>
	                                    </div>
	                                </div>
	                            </div>
<!-- 	                            <div class="form-group">
	                                <div class="form-element">
	                                    <label>{{__('app.dw.new.w_pin') }} *</label>
	                                    <div class="input-wrapper"> 
	                                    	<input name="w_pin" type="text">
	                                    </div>
	                                </div>
	                            </div> -->
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
