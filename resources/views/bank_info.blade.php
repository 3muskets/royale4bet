@extends('my_profile')

@section('head')
<script src="{{ asset('js/app.js') }}"></script>
<script type="text/javascript">

	$(document).ready(function()
	{
		prepareLocale();

		$("#mainForm").on('submit',(function(e){
		        e.preventDefault();
		        submitMainForm();
		    }));

		if($("#bank-name").val() == "")
		{
			$("#bank_info").hide();
		}
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
	            //console.log($("#mainForm")[0]);

	    $.ajax({
	        url: "/ajax/profile/bank_info",
	        type: "POST",
	        data:  new FormData($("#mainForm")[0]),
	        contentType: false,
	        cache: false,
	        processData:false,
	        success: function(data)
	        {
	            //console.log(data);

	            var obj = JSON.parse(data);

	            if(obj.status == 1)
	            {
	                utils.showModal(locale['info'],locale['success'],obj.status,enableMainForm);

	                location.reload();

	            }
	            else
	            {
	            	var html = "";

	            	for(var i = 0; i < obj.error.length; i++)
	            	{
	            		html += "-" + obj.error[i] + "<br>";
	            	}
	            	
	                utils.showModal(locale['error'],html,obj.status,enableMainForm);
	            }
	        },
	        error: function(){}             
	    }); 
	}

	function enableMainForm()
	{
	    $("#mainForm").attr("enabled",1);
	}

</script>

<style>
	label
	{
		color: #c92985;
		font-size: 10px;
		margin-bottom: 0;
		padding-left: .5rem;
		font-weight: 700;
	}
	input
    {
        background: transparent !important;
        border: 0 !important;;
        border-bottom: 1px solid #dcdcdc !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        outline: none !important;
        color: #363636 !important;
    }
    small
    {
        color: #c92985;
        font-weight: 700;
    }
    input::placeholder
    {
        color: #bcbcbc !important;
    }
    .required-field
    {
    	color: #00fff0;
    }
    .btn-submit
    {
        width: 100px;
        height:30px;
        color: #000000;
        font-size: 12px;
        background: transparent;
        margin: 0;
        padding: 0;
        border: 2px solid #d82c8e;
    }
</style>
@endsection

@section('details')
<div class="container pl-0">
	<div class="card profile-card">
		<div class="card-header profile-header">
			{{ __('app.bankinfo.title') }}
		</div>

		<div class="card-body profile-body">
			<form method="POST" id="mainForm">
            @csrf
            	<div class="row justify-content-center">
            		<div class="col-md-4" id="bank_info">

		            	<div class="form-group">
		            		<label for="bank-name">{{ __('app.bankinfo.oldbankname') }}</label>
		            		<input type="text"  class="form-control form-control-sm" id="bank-name" name="bank-name" placeholder="{{ __('app.bankinfo.oldbankname') }}" value="{{$bank_name}}" disabled>
		            	</div>

		            	<div class="form-group">
		            		<label for="bank-add">{{ __('app.bankinfo.bankadd') }}</label>
		            		<input type="text"  class="form-control form-control-sm" id="bank-add" name="bank-add" placeholder="{{ __('app.bankinfo.bankadd') }}" value="{{$bank_add}}" disabled>
		            	</div>

		            	<div class="form-group">
		            		<label for="bank-acc">{{ __('app.bankinfo.oldbankacc') }}</label>
		            		<input type="text"  class="form-control form-control-sm" id="bank-acc" name="bank-acc" placeholder="{{ __('app.bankinfo.oldbankacc') }}" value="{{$bank_acc}}" disabled>
		            	</div>

		            </div>

		            <div class="col-md-6">

		            	<div class="form-group row" style="margin-top:1.25rem;margin-bottom:2rem;">
    					    <label for="new-bank-name" class="col-md-1 col-form-label col-form-label-sm text-md-left">
    					        <img class="label-icon" src="/images/profile/icon-bank-name.png">
    					        <small class="d-md-none" style="padding-top:3px;padding-left:2px;">{{ __('app.bankinfo.newbankname') }}</small>
    					    </label>

    					    <div class="col-md text-md-left">
    					    	<small class="d-none d-lg-block" style="position: absolute; top:-15px; left:24px;">{{ __('app.bankinfo.newbankname') }}</small>
    					        <input id="new-bank-name" type="text" name="new-bank-name" class="form-control form-control-sm" placeholder="{{ __('app.bankinfo.newbankname') }}" required autofocus>
    					    </div>
    					</div>

    					<div class="form-group row" style="margin-top:1.25rem;margin-bottom:2rem;">
    					    <label for="new-bank-add" class="col-md-1 col-form-label col-form-label-sm text-md-left">
    					        <img class="label-icon" src="/images/profile/icon-branch.png">
    					        <small class="d-md-none" style="padding-top:3px;padding-left:2px;">{{ __('app.bankinfo.newbankadd') }}</small>
    					    </label>

    					    <div class="col-md text-md-left">
    					    	<small class="d-none d-lg-block" style="position: absolute; top:-15px; left:24px;">{{ __('app.bankinfo.newbankadd') }}</small>
    					        <input id="new-bank-add" type="text" name="new-bank-add" class="form-control form-control-sm" placeholder="{{ __('app.bankinfo.newbankadd') }}" required>
    					    </div>
    					</div>

    					<div class="form-group row" style="margin-top:1.25rem;margin-bottom:2rem;">
    					    <label for="new-bank-acc" class="col-md-1 col-form-label col-form-label-sm text-md-left">
    					        <img class="label-icon" src="/images/profile/icon-acct-no.png">
    					        <small class="d-md-none" style="padding-top:3px;padding-left:2px;">{{ __('app.bankinfo.newbankacc') }}</small>
    					    </label>

    					    <div class="col-md text-md-left">
    					    	<small class="d-none d-lg-block" style="position: absolute; top:-15px; left:24px;">{{ __('app.bankinfo.newbankacc') }}</small>
    					        <input id="new-bank-acc" type="text" name="new-bank-acc" class="form-control form-control-sm" placeholder="{{ __('app.bankinfo.newbankacc') }}" required>
    					    </div>
    					</div>

    					<div class="form-group row" style="margin-top:1.25rem;margin-bottom:2rem;">
    					    <label for="new-bank-acc-confirm" class="col-md-1 col-form-label col-form-label-sm text-md-left">
    					        <img class="label-icon" src="/images/profile/icon-bank-acct-confirm.png">
    					        <small class="d-md-none" style="padding-top:3px;padding-left:2px;">{{ __('app.bankinfo.confirmnewbankacc') }}</small>
    					    </label>

    					    <div class="col-md text-md-left">
    					    	<small class="d-none d-lg-block" style="position: absolute; top:-15px; left:24px;">{{ __('app.bankinfo.confirmnewbankacc') }}</small>
    					        <input id="new-bank-acc-confirm" type="text" name="new-bank-acc-confirm" class="form-control form-control-sm" placeholder="{{ __('app.bankinfo.confirmnewbankacc') }}" required>
    					    </div>
    					</div>

		            	<div class="form-group text-center mt-5">
		            		<button id="btnSubmit" type="submit" class="btn btn-sm link-fill link-fill-register btn-submit" data-style="expand-right">
					        	{{ __('app.bankinfo.button.save') }}
					        </button>
		            	</div>

		            </div>


		        </div>
            </form>
		</div>
	</div>
</div>
@endsection