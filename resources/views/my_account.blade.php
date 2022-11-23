@extends('layouts.app')

@section('head')
<script type="text/javascript">
	$(document).ready(function()
	{
		prepareLocale();

		$('.fa-edit').click(function()
		{
			$('#modal-bank').modal('show');
		});

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

        $.ajax({
            url: "/ajax/profile/bank_info",
            type: "POST",
            data:  new FormData($("#mainForm")[0]),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data)
            {
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
</script>
<style>
	.card
	{
		border: none;
		background: transparent;
	}
	.card-header
	{
		background: linear-gradient(180deg,#131228,#140133);

	}
	.ditto-card-body
    {
        background: #23214a;
        border: 2px solid #9ee2fe;
        border-radius: 20px;
        box-shadow: inset 0 0 15px #77a5eb;
    }
    .cred
    {
    	color: #c7c7c7;
    	font-weight: bolder;
    	font-size: 12px;
    }
    form label
    {
    	font-size: 13px;
    }
    select,input
    {
        width: 100%;
        background: black !important;
        border-radius: 10px !important;
        box-shadow: none !important;
        outline: none !important;
        color: white !important;
        padding: 5px 10px !important;

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
    .btn-submit
    {
        width: 100%;
        height: 40px;
        color: #ffffff;
        margin: 0;
        padding: 0;
        border-radius: 8px;
        border:0;
        background: linear-gradient(180deg,rgba(43,67,129,.96),rgba(31,10,90,.96));

    }
    .btn-submit:hover
    {
        color: #ffffff;
        background: linear-gradient(180deg,#79c1f4,#4300d2);
    }
</style>
@endsection

@section('content')

<div class="card">
	<div class="card-header mb-0">
		<i class="fa fa-info" style="background: #6008c7; padding: 7px; clip-path: circle();"></i> Account Info
	</div> 
	<div class="card-body">

		<div class="row">

			<div class="col-12 col-md-4">
				<div class="card p-1" style="height: 100%">
					<div class="card-body ditto-card-body">

						<form>
						  	<div class="form-group mb-4">
						    	<label for=""><i class="fa fa-envelope"></i> Email Address</label>
						    	<div class="cred" id="profile-email">{{ $email }}</div>
						  	</div>

						  	<div class="form-group mb-4">
						    	<label for=""><i class="fa fa-user"></i> Username</label>
						    	<div class="cred" id="profile-usename">{{ $username }}</div>
						  	</div>

						  	<div class="form-group mb-4">
						    	<label for=""><i class="fa fa-id-card-o"></i> Full Name</label>
						    	<div class="cred" id="profile-fullname">{{ $first_name }} {{ $last_name }}</div>
						  	</div>

						  	<div class="form-group mb-4">
						    	<label for=""><i class="fa fa-phone"></i> Contact No.</label>
						    	<div class="cred" id="profile-usename">{{ $mobile }}</div>
						  	</div>
						</form>
					</div>
				</div>
			</div>

			<div class="col-12 col-md-4">
				<div class="card p-1" style="height: 100%">
					<div class="card-body ditto-card-body" style="position: relative">

						<form>
						  	<div class="form-group mb-4">
						    	<label for=""><i class="fa fa-info-circle"></i> Banking Details</label>
						    	<div id="banking-details">
						    		<div class="cred" id="bank-name">
						    			@if($bank)
						    				{{$bank}}
						    			@else
						    				-
						    			@endif
						    		</div>
						    		<div class="cred" id="bank-acc">
						    			{{$bank_acc}}
						    		</div>
						    		<div class="cred" id="bank-owner">
						    			{{$bank_acc_name}}
						    		</div>
						    	</div>
						  	</div>
<!-- 
						  	<div class="form-group mb-4">
						    	<label for=""><i class="fa fa-globe"></i> Bank Country</label>
						    	<div class="cred" id="banking-coutnry">Malaysia</div>
						  	</div> -->
						</form>

						<i class="fa fa-edit fa-2x" style="position: absolute;bottom: 2%; right: 2%; cursor: pointer"></i>
					</div>
				</div>
			</div>

		</div>

	</div>

</div>

<div class="modal fade" role="dialog" id="modal-bank">
	<div class="modal-dialog">
		<div class="modal-content" style="background: transparent;">
			<div class="modal-body ditto-card-body">
				<p><i class="fa fa-info" style="background: #6008c7; padding: 7px; clip-path: circle();"></i> Bank Details</p>
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

				  	<div class="form-group">
					   	<button type="submit" class="btn btn-submit">Submit</button>
				  	</div>
				</form>
			</div>
		</div>
	</div>
</div>

@endsection