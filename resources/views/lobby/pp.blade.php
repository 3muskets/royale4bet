@extends('layouts.app')

@section('head')

<style type="text/css">
	
	a 
	{
		text-decoration: none;
	}

	a:hover 
	{
		text-decoration: none;
	}

	p
	{
		color: white;
		font-size: 12px;
	}

	@media(max-width: 768px)
	{
		img
		{
			width: 158px;
		}
	}

</style>

@endsection

@section('content')

<div class="container pt-5">

	<div class="row">

		@foreach($gameList as $game)

		<div class="col-6 col-md-4 col-lg-3">
			<center>
			    <a class="game-launcher" href="{{ route('game',['gameId' => 3,'type' => $game->id, 'isMobile' => 0]) }}">
			    	<img src="{{ $game->icon_url }}">
			    	<p>{{ $game->game_name }}</p>
			    </a>
		    </center>
		</div>

		@endforeach

	</div>

</div>

@endsection