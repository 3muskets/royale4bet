@extends('layouts.app')

@section('head')


@endsection
@section('content')
<center class="container" style="padding:50px;">
	<h1 style="color:white;" id="deposit-resp"></h1>

    <div class="form-btn">
        <button class="btn btn1" type="submit" onclick="window.location.href='/'" id="btn-home">Back to Home Page</button>
        <button class="btn btn1" type="submit" onclick="redirectPaymentGateway();" id="btn-redirect">Redirect to Payment Gateway</button>
    </div>
</center>

<script type="text/javascript">

	status = utils.getParameterByName('status');

	

	if(status == 1)
	{
		$("#deposit-resp").html("Deposit Success");
		$("#btn-home").show();
		$("#btn-redirect").hide(); 
	}
	else if(status == 2)
	{
		$("#deposit-resp").html("Wait For Payment"); 
		$("#btn-home").hide();
		$("#btn-redirect").show();
	}
	else if(status == 3)
	{
		$("#deposit-resp").html("Deposit Failed"); 
		$("#btn-home").show();
		$("#btn-redirect").hide();
	}
	else
	{
		$("#deposit-resp").html("Unknown"); 
		$("#btn-home").show();
		$("#btn-redirect").hide();		
	}


	function redirectPaymentGateway() 
	{
		txnId = utils.getParameterByName('txn_id');

		window.location.href = '/orderPaymentf2f?txn_id='+txnId;
	}

</script>

@endsection