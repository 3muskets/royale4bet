@extends('layouts.app')

@section('head')

<script src="{{ asset('js/qrcode.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function()
	{
        var url = window.location.href;
        url = new URL(url);
        url = url.hostname+"/register";

		var qrcode = new QRCode(document.getElementById("qrcode"), {
		        text: url+"?ref_code={{ auth()->id() }}",
		        width: 100,
		        height: 100,
		        colorDark : "#fefefe",
		        colorLight : "#23214a",
		    });
	})
</script>

<style>
	.card-custom-v2
    {
        background: #23214a;
        border: 2px solid #9ee2fe;
        border-radius: 20px;
        box-shadow: inset 0 0 15px #77a5eb;
    }
    .card
    {
        border: none;
        background: transparent;
    }
    .card-header
    {
        background: linear-gradient(180deg,#131228,#140133);
    }
    .card-header span
    {
        -webkit-mask: linear-gradient(-60deg,#000 30%,#0005,#000 70%) right/300% 100%;
        animation: shimmer 2.5s infinite;
        font-weight: bold;
    }
    #qrcode
    {
        background-color: white;
        padding: 18px; 
        width: 138px; 
        height: 138px;
        margin:10px auto;
    }
</style>

@endsection

@section('content')

<div class="card" style="background: transparent;">

    <div class="card-header mb-0">
        <i class="fa fa-money" style="padding: 7px;"></i> <span>REFERRAL</span>
    </div>
    <marquee class="marquee px-2">Share your favorite offers to your friends when they register with the qrcode below and start earning bonus today!</marquee>

    <div class="card-body">

    	<div class="row mt-4"> 
            <div class="col-12 col-md-4">
                <div class="card">

                    <div class="card-body card-custom-v2" style="position: relative;">

                    	<div class="text-center">
                    		<p>Scan and Earn!</p>
	                    	<div id="qrcode" class="qr">
	                        </div>
	                    </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

</div>
@endsection