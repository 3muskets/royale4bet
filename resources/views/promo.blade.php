@extends('layouts.app')

@section('head')
<script type="text/javascript">

	$(document).ready(function()
	{

	});

	function showPromoDetails(image, name, details)
	{
		$('#promo-details').modal('show');

		$('#promo-details img').attr('src', image);
		$('#promo-details .card-title').html(name);
		$('#promo-details .card-text').html(details);
	}

</script>

<style>
	
	.page-title
	{
		background:#030305;
		font-size:16px;
		font-weight: bold;
	}

	.btn-apply,.btn-apply:hover
	{
	   	color: white;
	    padding: 5px 20px;
	    border-radius: 5px;
	    border:0;
	    background: #CF2029;

	}

	.promo-card
	{
		color:white;
		border:none;
		border-radius:7px;
		height: 100%;
	}

	.promo-card .card-body
	{
		background: #8E8EA7;
		border-bottom-left-radius:5px;
		border-bottom-right-radius:5px;
	}
	.promo-card .card-img-top
	{
		border-top-left-radius:5px;
		border-top-right-radius:5px;
		height: 150px;
		object-fit: cover;
	}

	.promo-card .card-title
	{
		font-weight: bold;
	}

</style>
@endsection

@section('content')
<div class="w-100 page-title p-2">
	PROMOTION
</div>

<div class="container pt-3">
	<div id="template-container" class="row">
		@foreach($promo as $p)
		<div id="template" class="col-12 col-md-6 col-lg-4 pb-3" style="display:block">

			<div class="card text-center promo-card" style="width:100%">
				<img class="card-img-top" src="{{$p->image}}">
				<div class="card-body">
					<h5 class="card-title">{{$p->promo_name}}</h5>
					<p class="card-text">Terms and conditions applied</p>
					<a href="javascript:showPromoDetails('{{$p->image}}', '{{$p->promo_name}}', '{{$p->detail}}')" class="btn btn-apply">MORE DETAILS</a>
				</div>
			</div>

		</div>
		@endforeach
	</div>
</div>

<div class="modal fade" id="promo-details" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:7px">            
            <div class="modal-body p-0">
                
				<div class="card promo-card" style="width:100%">
					<img class="card-img-top" src="data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAA+gAAAFTCAIAAAAlU7EsAAABhGlDQ1BJQ0MgcHJvZmlsZQAAKJF9kT1Iw0AcxV9Txa+Kg0VUHDJUJwuiIo5SxSJYKG2FVh1MLv2CJg1Jiouj4Fpw8GOx6uDirKuDqyAIfoA4OjkpukiJ/0sKLWI8OO7Hu3uPu3eAUCsx1WybAFTNMhLRiJjOrIodr+iBH10YwoDETD2WXEzBc3zdw8fXuzDP8j735+hVsiYDfCLxHNMNi3iDeGbT0jnvEwdZQVKIz4nHDbog8SPXZZffOOcdFnhm0Egl5omDxGK+heUWZgVDJZ4mDimqRvlC2mWF8xZntVRhjXvyFway2kqS6zRHEMUSYohDhIwKiijBQphWjRQTCdqPePiHHX+cXDK5imDkWEAZKiTHD/4Hv7s1c1OTblIgArS/2PbHKNCxC9Srtv19bNv1E8D/DFxpTX+5Bsx+kl5taqEjoG8buLhuavIecLkDDD7pkiE5kp+mkMsB72f0TRmg/xboXnN7a+zj9AFIUVfLN8DBITCWp+x1j3d3tvb275lGfz8+LHKSJ6/nwgAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAAd0SU1FB+YLAwkaFbuN6X8AAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAD8UlEQVR42u3BMQEAAADCoPVPbQZ/oAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAwwCG3AABskXY0wAAAABJRU5ErkJggg==">
					<div class="card-body">
						<h5 class="card-title">Card title</h5>
						<p class="card-text">Terms and conditions applied</p>
						<center>
							<a href="/my_profile/deposit/new?bank" class="btn btn-apply">APPLY NOW</a>
						</center>
					</div>
				</div>

            </div>
        </div>
    </div>
</div>

@endsection