<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('images/favicon1.png') }}"/>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/utils.js') }}"></script>

    <!-- JqueryUI -->
    <script src="/jquery/jquery.datetimepicker.js"></script>
    <link href="/jquery/jquery.datetimepicker.min.css" rel="stylesheet">

    <!-- Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet"> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> -->
    <!-- <link href="/fonts/montserrat.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="/fonts/font-awesome.min.css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

   	<script type="text/javascript">
		
	</script>

	<style>
		body
		{
			width: 100%;
			height: 100vh;
		}
	</style>
</head>

<body>

	<div id="iframe_container" style="width:100%;height: 100%;">
			<iframe id="iframe_game" src="{{ $iframe }}" style="width:100%;border:0; height: 100%;" allowfullscreen frameborder=0></iframe>
		</center>
	</div>
</body>
</html>


