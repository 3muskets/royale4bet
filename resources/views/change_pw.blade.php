@extends('my_profile')

@section('head')
<script src="{{ asset('js/app.js') }}"></script>
<script type="text/javascript">

	$(document).ready(function()
	{
		prepareLocale();
		enableMainForm();

		$("#mainForm").on('submit',(function(e){
		        e.preventDefault();
		        submitMainForm();
		    }));
		
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
	        url: "/ajax/profile/change_pw",
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

	                $('input[type="password"]').val('');
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
                <div data-tab="change-password" class="section-tab tab-focus selected">
                    <form method="POST" id="mainForm">
                        @csrf
                        <div class="form-container">
                            <div class="form-group">
                                <div class="form-element">
                                    <label>{{ __('app.change_pw.originalpw')}} *</label>
                                    <div class="input-wrapper">
                                        <input name="original_pw" class="ember-text-field" type="password">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-element">
                                    <label>{{ __('app.change_pw.newpw')}} *</label>
                                    <div class="input-wrapper">
                                        <input name="new_password" class="ember-text-field" type="password">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-element">
                                    <label>{{ __('app.change_pw.confirmpw')}} *</label>
                                    <div class="input-wrapper">
                                        <input name="confirm_password" type="password">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-btn">
                            <button class="btn btn1" type="submit">{{ __('app.change_pw.button.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection