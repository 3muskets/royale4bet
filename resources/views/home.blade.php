@extends('layouts.app')

@section('head')

<script type="text/javascript">
	$(document).ready(function()
	{
		var popup = JSON.parse(@json($popupBanner));

		for(var i = 0; i < popup.length; i++)
		{
			var div = document.createElement('div');
			div.id = "popup";
			div.setAttribute("style", "width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index:999; display: flex; align-items: center; justify-content: center; position: fixed; top: 0; left: 0;");

			var imgContainer = document.createElement("div");
			imgContainer.style.position = "relative";

			var img = document.createElement("img");
			img.src = popup[i]['image'];

			var closeBtn = document.createElement("div");
			closeBtn.setAttribute("style", "position:absolute; top: 0; right: 0; color: #fff; cursor: pointer; background: black; padding: 0 5px;");
			closeBtn.innerHTML = "Close [X]";
			closeBtn.onclick = function()
			{
				$("#popup").remove();
			}

			imgContainer.appendChild(img);
			imgContainer.appendChild(closeBtn);
			div.appendChild(imgContainer);

			$('body').append(div);
		}
	});
</script>

<style type="text/css">
	
	.section-title
	{
		color: #9be1ff;
		font-size: 20px;
	}

	.icon-box
	{
		position:relative;
		height: 100%;
		text-align: center;
		display: flex;
		justify-content: center;
	}

	.icon-box-img
	{
		max-width: 100%;
		border-radius: 10px;
		object-fit: contain;
	}

	.icon-box-overlay
	{
		display: none !important;

		position:absolute;
		left:0;
		top:0;
		width:100%;
		height: 100%;
		background: rgba(56,54,83,.7);
		border-radius: 10px;
	}

	.icon-box:hover > .icon-box-overlay
	{
		display: flex !important;
	}

	.icon-box-play
	{
		background: linear-gradient(180deg,#fb0053,#150841);
		padding: 10px 10px;
		border-radius: 5px;
		filter: drop-shadow(0 1px 1px #0005);
	}

	.icon-box-play > span
	{
		color: white;
		font-size:12px;
		filter: drop-shadow(0 1px 1px #000a);
	}

	/*multi item carousel*/
	.multi-item-carousel 
	{
		overflow: hidden;
	}

	.multi-item-carousel .carousel-indicators 
	{
		margin-right: 25%;
		margin-left: 25%;
	}

	.multi-item-carousel .carousel-control-prev
	{
		background: linear-gradient(to right,#1f0a5a, rgba(0,0,0,0));
		opacity: 1;
	}

	.multi-item-carousel .carousel-control-next
	{
		background: linear-gradient(to left,#1f0a5a, rgba(0,0,0,0));
		opacity: 1;
	}

	.multi-item-carousel .carousel-control-prev:hover,
	.multi-item-carousel .carousel-control-next:hover,
	.multi-item-carousel .carousel-control-prev:hover>span,
	.multi-item-carousel .carousel-control-next:hover >span
	{
		opacity: 1;
	}

	.multi-item-carousel .carousel-control-prev>span,
	.multi-item-carousel .carousel-control-next>span
	{
		
		opacity: 0.5;
	}

	.multi-item-carousel .carousel-control-prev,
	.multi-item-carousel .carousel-control-next 
	{
		
		/*background: rgba(255, 255, 255, 0.5);*/
		width: 25%;
		z-index: 11; /* .carousel-caption has z-index 10 */
	}

	.multi-item-carousel .carousel-inner 
	{
		width: 150%;
		left: -25%;
	}

	.multi-item-carousel .carousel-item-next:not(.carousel-item-left),
	.multi-item-carousel .carousel-item-right.active 
	{
		-webkit-transform: translate3d(33%, 0, 0);
		transform: translate3d(33%, 0, 0);
	}

	.multi-item-carousel .carousel-item-prev:not(.carousel-item-right),
	.multi-item-carousel .carousel-item-left.active 
	{
		-webkit-transform: translate3d(-33%, 0, 0);
		transform: translate3d(-33%, 0, 0);
	}

	.multi-item-carousel .carousel-item>div 
	{
		float: left;
		position: relative;
		width: 33.33333333%;
		border-radius:20px;	
	}

	.clone
	{
		display: block;
	}

	@media(max-width: 767px)
    {
       .clone
        {
            display: none;
        } 
    }

    .carousel-item>div
    {
    	padding: 0px 10px;
    }

	.carousel-item >div >img 
	{
		border-radius:10px;
	}

	.carousel-item,.carousel-item-left,.carousel-item-right,.carousel-item-next,.carousel-item-prev
	{
		transition-timing-function: linear !important;
	}

	.active:not(.carousel-item-left):not(.carousel-item-right)
	{
		transition-duration: 0s !important;
	}

</style>

<script type="text/javascript">

	$( document ).ready(function() 
	{

		// multi item carousel
		$('.carousel-item', '.multi-item-carousel').each(function()
		{
			var next = $(this).next();
			if (! next.length) 
			{
				next = $(this).siblings(':first');
			}
			var clone = next.children(':first-child').clone();
			clone.addClass('clone');
			clone.appendTo($(this));

		}).each(function()
		{
			var prev = $(this).prev();
			if (! prev.length) 
			{
				prev = $(this).siblings(':last');
			}
			var clone = prev.children(':nth-last-child(2)').clone();
			clone.addClass('clone');
			clone.prependTo($(this));
		});

		$('#carouselBanner').on('slide.bs.carousel', function (carousel) 
		{
			$('.carousel-indicators').children(':nth-child(' + (carousel.from + 1) + ')').removeClass('active');
			$('.carousel-indicators').children(':nth-child(' + (carousel.to + 1) + ')').addClass('active');
		})

		reformatBanner();
		window.addEventListener('resize', reformatBanner);
	});

	function reformatBanner()
	{
		var innerWidth = window.innerWidth;
        
        if(innerWidth <= 767)
        {
            $('#carouselBanner').removeClass('multi-item-carousel');
        }
        else
        {
        	$('#carouselBanner').addClass('multi-item-carousel');
        }
	}
	
</script>
@endsection

@section('content')                              

<div id="carouselBanner" class="carousel slide multi-item-carousel my-2" data-ride="carousel">

	<div class="carousel-inner">
		@foreach($mainBanner as $banner)
			@if($banner->sequence == 1)
			<div class="carousel-item active">
				<div>
					<img class="w-100" src="{{$banner->image}}">
				</div>
			</div>
			@else
			<div class="carousel-item">
				<div>
					<img class="w-100" src="{{$banner->image}}">
				</div>
			</div>
			<div class="carousel-item">
				<div>
					<img class="w-100" src="{{$banner->image}}">
				</div>
			</div>
			@endif
		@endforeach
	</div>

	<a class="carousel-control-prev" href="#carouselBanner" role="button" data-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="sr-only">Previous</span>
	</a>
	<a class="carousel-control-next" href="#carouselBanner" role="button" data-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="sr-only">Next</span>
	</a>
</div>

<div class="carousel" style="height:10px">
	<ol class="carousel-indicators" style="top:0px">
		<li class="active" style="cursor: default; border-radius: 50%; width: 8px; height: 8px"></li>
		<li style="cursor: default; border-radius: 50%; width: 8px; height: 8px"></li>
		<li style="cursor: default; border-radius: 50%; width: 8px; height: 8px"></li>
	</ol>
</div>

<br>

<div id="section-hot" class="container" style="display:none">

	<div class="row py-4">

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 1013]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/918kaya.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" target="_blank" href="{{ route('game',['gameId' => 1001,'type' => 0, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/evo1.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div>

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" target="_blank" href="{{ route('game',['gameId' => 1012,'type' => 0, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/sexy.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div>

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 1008]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/mega.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 1009]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/918.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 1011]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/pussy.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

	</div>

</div>

<div id="section-casino" class="container" style="display:none">

	<div class="row py-4">

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" target="_blank" href="{{ route('game',['gameId' => 1001,'type' => 0, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/evo1.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div>

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" target="_blank" href="{{ route('game',['gameId' => 1004,'type' => 0, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/ab.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div>

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" target="_blank" href="{{ route('game',['gameId' => 1012,'type' => 0, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/sexy.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div>

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" target="_blank" href="{{ route('game',['gameId' => 1003,'type' => 0, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/sa1.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div>

	</div>

</div>

<div id="section-slots" class="container" style="display:none">

	<br>

	<div class="row py-4">

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 1013]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/918kaya.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('lobby',['gameId' => 1006]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/joker-thumbnail.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('lobby',['gameId' => 1007]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/xe88.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 1008]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/mega.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 1009]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/918.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 1010]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/scr.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 1011]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/slots2/pussy.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div> 

	</div>
</div>

<div id="section-sports" class="container" style="display:none">

	<br>

	<div class="row py-4">

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" target="_blank" href="{{ route('game',['gameId' => 1000]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/sb/sbo.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div>

		<div class="col-6 col-md-4 pb-3">
			
			<a class="game-launcher" target="_blank" href="{{ route('game',['gameId' => 1002, 'type' => 3, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/sb/ibc.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
		</div>

	</div>

</div>
<div id="section-fishing" class="container" style="display:none">

	<div class="section-title d-flex justify-content-center">Fishing betting</div>

	<br>

	<div class="row py-4">

		<!-- <div class="col-6 col-md-4 col-lg-3 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 8,'type' => 4, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/fishing/joker.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
			<div>JOKER</div>
		</div>

		<div class="col-6 col-md-4 col-lg-3 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 10,'type' => 4, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/fishing/joker.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
			<div>Spade Gaming</div>
		</div> -->

	</div>

</div>
<div id="section-lottery" class="container" style="display:none">

	<div class="section-title d-flex justify-content-center">Lottery</div>

	<br>

	<div class="row py-4">

		<div class="col-6 col-md-4 col-lg-3 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 2,'type' => 5, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/bbin.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
			<div>BBIN</div>
		</div>

		<div class="col-6 col-md-4 col-lg-3 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 9,'type' => 5, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/bbin.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
			<div>Psbet 4D</div>
		</div>

		<div class="col-6 col-md-4 col-lg-3 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 11,'type' => 5, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/bbin.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
			<div>QQ Keno</div>
		</div>

		<div class="col-6 col-md-4 col-lg-3 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 14,'type' => 9, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/bbin.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
			<div>Digmaan</div>
		</div>

	</div>

</div>
<div id="section-esport" class="container" style="display:none">

	<div class="section-title d-flex justify-content-center">E-Sports</div>

	<br>

	<div class="row py-4">

		<div class="col-6 col-md-4 col-lg-3 pb-3">
			
			<a class="game-launcher" href="{{ route('game',['gameId' => 16,'type' => 6, 'isMobile' => 0]) }}">
				<div class="icon-box">
					<img class="icon-box-img" src="/images/home/casino/bbin.png">
					<div class="d-flex flex-column justify-content-center align-items-center icon-box-overlay">
						<div class="icon-box-play">
							<span class="fa fa-play" >&nbsp;</span>
							<span>{{ __('app.home.playnow') }}</span>
						</div>
					</div>
				</div>
			</a>
			<div>IA</div>
		</div>

	</div>

</div>

@endsection