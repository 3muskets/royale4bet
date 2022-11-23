<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

     <style type="text/css">
        
        body
        {
        	font-family: 'Montserrat', sans-serif;
        	font-weight: 700;
        	font-size: 12px;
            background: #0c041b;
            color: #ffffff;
        }

        a
        {
        	color: #c92985;
        }
        a:hover
        {
        	color: #c92985;
        }
    </style>
</head>

<body>

	<div class="container mt-5 text-left">
		<h3 style="color: #c92985;">ABOUT US</h3>

		<p class="text-left">
			At Ditto Gaming, we know what players need. Through our own gaming experience, we’ve discovered what’s important from the customer’s point of view: generous winnings, fast payouts, licensed software, a wide selection of games, friendly and helpful Customer Support, and a user-friendly, no-fuss casino.
		</p>

		<p class="text-left">
			We don’t need to tell you all about how good and exclusive we are – you can check it out for yourself right here and now. Don't miss your chance: seize the moment and join us now! 24/7
		</p>

		<p class="text-left">
			For more information of our services and products please e-mail us to:
			<br>
			<a href="#">Info@dittogaming.games</a>
		</p>

		<p class="text-left">
			For Customer Supports please e-mail us to:
			<br>
			<a href="#">support@dittogaming.games</a>
		</p>

		<p class="text-left">
			If you are facing any issues or inconvenient while using our website, please e-mail us to:
			<br>
			<a href="#">Complaint@dittogaming.games</a>
		</p>
	</div>

</body>

</html>