@extends('layouts.app')

@section('head')
<script type="text/javascript">
	$(document).ready(function()
	{
		prepareLocale();

		$('.edit-profile').click(function()
		{
			$('#modal-profile').modal('show');
		});

		$('.edit-bank').click(function()
		{
			$('#modal-bank').modal('show');
		});

		$("#mainForm").on('submit',(function(e){
		    e.preventDefault();
		    submitMainForm();
		}));

		$("#mainForm2").on('submit',(function(e){
		    e.preventDefault();
		    submitMainForm2();
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

        $.ajax({
            url: "/ajax/profile/bank_info",
            type: "POST",
            data:  new FormData($("#mainForm")[0]),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data)
            {
            	data = JSON.parse(data);
            	
            	if(data.status == 1)
            	{
            		alert(locale['success']);
            		window.location.reload();
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

                    $("#mainForm").attr("enabled",1);

                    alert(html);
                }
            }
        });
	}

	function submitMainForm2()
	{
		if($("#mainForm2").attr("enabled") == 0)
        {
            return;
        }

        $("#mainForm").attr("enabled",0);

        $.ajax({
            url: "/ajax/profile/name",
            type: "POST",
            data:  new FormData($("#mainForm2")[0]),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data)
            {
            	data = JSON.parse(data);
            	
            	if(data.status == 1)
            	{
            		alert(locale['success']);
            		window.location.reload();
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

                    $("#mainForm2").attr("enabled",1);

                    alert(html);
                }
            }
        });
	}
</script>
<style>
	.page-title
	{
		background:#27273F;
		font-size:16px;
		font-weight: bold;
	}

	.field-title
	{
		color:black;
		font-weight: bold;
		font-size:14px;
		margin-bottom: 10px;
	}

	.cred
    {
    	color: black;
    	font-weight: bolder;
    	font-size: 12px;
    	padding: 0 5px;
    }
    form label
    {
    	font-size: 13px;
    	margin: 0;
    	color: #b27272;
    	background: lightgrey;
    	width: 100%;
    	padding: 0 5px;
    	border-radius: 3px;
    }

    select,input::placeholder
    {
        color: #bcbcbc !important;
    }
    button:focus
    {
        box-shadow: none !important;
        outline: 0 !important;
    }
    .btn-edit
    {
        color: #ffffff;
        margin: 0;
        padding: 0;
        border-radius: 5px;
        border:0;
        background: #CF2029;
        padding: 5px;
        min-width: 50px;
    }
    .btn-submit:hover
    {
        color: #ffffff;
        background: linear-gradient(180deg,#79c1f4,#4300d2);
    }
</style>
@endsection

@section('content')

<div class="w-100 page-title p-2">
	Account Info
</div>

<div class="w-100 p-2">
	<div style="background:white;border-radius:5px">
		<div class="py-4 px-2">
			<div class="container-fluid">
				<div class="row">

					<div class="col-12 col-sm-6 col-lg-4 col-xl-3 pb-3">
						<div class="p-2" style="border:1px solid #707070; height: 100%; position: relative; min-height: 250px">
							<span class="field-title">Account details</span>

							<form>
							  	<div class="form-group mb-2">
							    	<label for=""><i class="fa fa-envelope"></i> Email Address</label>
							    	<div class="cred" id="profile-email">{{ $email }}</div>
							  	</div>

							  	<div class="form-group mb-2">
							    	<label for=""><i class="fa fa-user"></i> Username</label>
							    	<div class="cred" id="profile-usename">{{ $username }}</div>
							  	</div>

							  	<div class="form-group mb-2">
							    	<label for=""><i class="fa fa-id-card-o"></i> Full Name</label>
							    	<div class="cred" id="profile-fullname">{{ $fullname }}</div>
							  	</div>

							  	<div class="form-group mb-2">
							    	<label for=""><i class="fa fa-phone"></i> Contact No.</label>
							    	<div class="cred" id="profile-usename">{{ $mobile }}</div>
							  	</div>

						  	  	<div class="form-group mb-0" style="text-align: right;">
						  		   	<button type="button" class="btn btn-edit edit-profile">Edit</button>
						  	  	</div>
							</form>
						</div>
					</div>

					<div class="col-12 col-sm-6 col-lg-4 col-xl-3 pb-3">
						<div class="p-2" style="border:1px solid #707070; height: 100%; position: relative; min-height: 250px">
							<span class="field-title">Banking Details</span>
							
							<form>
								<div class="form-group mb-2">
							    	<label for=""><i class="fa fa-info-circle"></i> Bank</label>
							    	<div class="cred" id="profile-email">
							    		@if($bank)
							    			{{$bank}}
							    		@else
							    			-
							    		@endif
							    	</div>
							  	</div>

							  	<div class="form-group mb-2">
							    	<label for=""><i class="fa fa-info-circle"></i> Bank Account</label>
							    	<div class="cred" id="profile-email">
							    		@if($bank)
							    			{{$bank_acc}}
							    		@else
							    			-
							    		@endif
							    	</div>
							  	</div>

							  	<div class="form-group mb-2">
							    	<label for=""><i class="fa fa-info-circle"></i> Bank Account's Holder</label>
							    	<div class="cred" id="profile-email">
							    		@if($bank)
							    			{{$bank_acc_name}}
							    		@else
							    			-
							    		@endif
							    	</div>
							  	</div>

				  				<div class="form-group mb-0" style="text-align: right; width: fit-content; position: absolute; bottom: 8px; right: 5px;">
						  		   	<button type="button" class="btn btn-edit edit-bank">Edit</button>
						  	  	</div>
							</form>
						</div>

					</div>

				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" role="dialog" id="modal-profile">
	<div class="modal-dialog">
		<div class="modal-content" style="background: white;">
			<div class="modal-body ditto-card-body">
				<p style="color: #000"><i class="fa fa-info" style="background: #6008c7; padding: 7px; clip-path: circle();"></i> Profile Details</p>
				<form id="mainForm2" method="POST">
					@csrf
					<div class="form-group">
					    <label for="fullname">Full Name</label>
					    @if($fullname)
					    <input type="text" class="form-control form-control-sm" name="fullname" value="{{$fullname}}" disabled>
					    @else
					    <input type="text" class="form-control form-control-sm" name="fullname" value="{{$fullname}}">
					    @endif
					    <div class="mt-4">
					    	<span style="font-style:italic; font-size: 12px; color: grey">* Please note that your full name must match your bank account's name to avoid any transaction issues.</span>
					    </div>
				  	</div>

				  	@if($fullname)
				  	<div class="form-group" style="text-align: right">
					   	<button type="submit" class="btn btn-edit" disabled>Submit</button>
				  	</div>
				  	@else
				  	<div class="form-group" style="text-align: right">
					   	<button type="submit" class="btn btn-edit">Submit</button>
				  	</div>
				  	@endif
				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" role="dialog" id="modal-bank">
	<div class="modal-dialog">
		<div class="modal-content" style="background: white;">
			<div class="modal-body ditto-card-body">
				<p style="color: #000"><i class="fa fa-info" style="background: #6008c7; padding: 7px; clip-path: circle();"></i> Bank Details</p>
				<form id="mainForm" method="POST">
					@csrf
					<div class="form-group">
					    <label for="bank">Bank</label>
					    <input type="text" class="form-control form-control-sm" placeholder="{{$bank}}" value="{{$bank}}" name="bank">
				  	</div>

				  	<div class="form-group">
					    <label for="bank">Name</label>
					    @if($bank)
					    	<input type="text" class="form-control form-control-sm" placeholder="{{$bank_acc_name}}" value="{{$bank_acc_name}}" name="bank_acc_name" disabled>
					    @else
					    	<input type="text" class="form-control form-control-sm" placeholder="{{$bank_acc_name}}" value="{{$bank_acc_name}}" name="bank_acc_name">
					    @endif
				  	</div>

				  	<div class="form-group">
					    <label for="bank">Account No.</label>
					    <input type="text" class="form-control form-control-sm" placeholder="{{$bank_acc}}" value="{{$bank_acc}}" name="bank_acc">
				  	</div>

				  	<div class="form-group" style="text-align: right">
					   	<button type="submit" class="btn btn-edit">Submit</button>
				  	</div>
				</form>
			</div>
		</div>
	</div>
</div>

@endsection