@extends('layouts.app')

@section('head')

<script type="text/javascript">

</script>

@endsection

@section('content')

<div id="menu" style="text-align: -webkit-center;">
	<div class="col-md-10 row" style="text-align: center">
		@foreach($gameList as $game)
			<div class="col-md-3" style="text-align: center; padding-bottom: 20px">
				<img src="{{ $game->img_url }}" style="width: 150px">
				<br>
				<a class="game-launcher" href="{{ route('game',['gameId' => $game->prd_id,'type' => $game->game_id, 'isMobile' => 0]) }}">{{ $game->game_name }}</a>
			</div>
		@endforeach
	</div>
</div>

@endsection

