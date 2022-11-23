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
        a, u
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
		<h3 style="color: #c92985;">RESPONSIBLE GAMING</h3>

		<p class="text-left">
			Gaming can be both entertaining and profitable. But you can’t always win. Losing is a part of the game and you have to be prepared for it. Therefore, you should only play with money you can afford to lose. Gaming can also be addictive and make you lose track of time and money. At Ditto Gaming we want all your gaming experiences to be as positive as possible, even if you lose. That is why we work hard to help you control your own gaming.
		</p>

		<p class="text-left">
			We help you set the limits We want to give you the opportunity to set your own gaming limits, budgets, and boundaries. we provide you with tools to prevent unhealthy gaming behaviour and enable you to play responsibly. 
		</p>

		<p class="text-left">
			<u>Deposit Limits</u>
			<br>
			The golden rule of gambling is to play within your means. So to help you do just that, you can set deposit limits by contacting our Customer Service team. These can be set for periods of time ranging from daily to monthly, and can be decreased, increased and removed entirely. 
		</p>

        <p class="text-left">
            <u>Take a break</u>
            <br>
            There’s nothing wrong with taking a break, especially when things aren’t turning out quite as planned. You can set a break period yourself for anything from 6 months to 5 years.
        </p>

        <p class="text-left">
            <u>Parental Controls</u>
            <br>
            If you share your computer with underage persons, always make sure they don't have access to usernames, passwords, and banking details. There is also software you can use for this purpose. Two examples are: NetNanny and Cyber Patrol. If you need additional help you can always e-mail us at <a href="#">info@dittogaming.com</a>.
        </p>

		<p class="text-left">
            <u>Underage Gambling</u>
            <br>
            Ditto Gaming takes the issue of underage gambling very seriously and understands the responsibility it carries regarding this matter. Ditto Gaming rules and regulations clearly state that the company does not accept clients under 18 years old. All Ditto Gaming clients are subjects to age verification procedure and must provide us with a document proving their age and identity. Should Ditto Gaming find any customers violating the underage gambling rules and regulations, it reserves the right to report such users to the state authorities as well as forfeit any winnings that may have been obtained.
        </p>

        <p class="text-left">
            <u>Professional Resources</u>
            <br>
            There are many resources available to individuals who have, or are developing, gambling problems. If you feel that you have a problem, we recommend visiting:
            <ul>
                <li>Gambling Therapy</li>
                <li>Gamblers Anonymous</li>
                <li>GamCare</li>
            </ul>
        </p>
	</div>

</body>

</html>