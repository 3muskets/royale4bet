@extends('layouts.app')

@section('head')

<script type="text/javascript">
	
	$(document).ready(function() 
	{
		
		
	    $("#mainForm").attr("enabled",1);

	    $("#mainForm").on('submit',(function(e){
	        e.preventDefault();
	        submitMainForm();
	    }));
	});


	function submitMainForm()
	{ 
		data = {};
		data['amount'] = $("#amount").val();
		data['currency'] =$("#currency").val();


		location.href = '/orderPaymentf2f?amount='+data['amount']+'&currency='+data['currency'];



	}
</script>
@endsection

@section('content')
<div style="position: absolute;left:40%;top:30%;">
	<h1 style="color:white;">Payment Gateway</h1>

	<form method="POST" id="mainForm">
	    <div id="step1" class="form-container">

	    	<input type="hidden" value="d" name="type">
	    	<input type="hidden" value="crypto" name="payment_type">

	        <div class="form-group">
	            <div class="form-element">
	                <label style="color:white;">Currency</label>
	                <div class="input-wrapper select" style="font-size:16px;">
	                    <select id="currency" style="width:165px;">
	                        <option value="MYR">MYR</option>
	                        <option value="CNY">CNY</option>
	                        <option value="THB">THB</option>
	                    </select>
	                </div>
	            </div>
	        </div>
	        <div class="form-group">
	            <div class="form-element">
	                <label style="color:white;">Amount</label>
	                <div class="input-wrapper"> 
	                	<input id="amount" name="amount" type="text">
	                </div>
	            </div>
	        </div>
	        <div class="form-btn">
	            <button class="btn btn1" type="submit">Submit</button>
	        </div>
	    </div>
	</form>
</div>
@endsection


