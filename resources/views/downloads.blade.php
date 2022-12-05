@extends('layouts.app')

@section('head')

<script type="text/javascript">

    $(document).ready(function()
    {
        var gameId = utils.getParameterByName("gameId");

        if(gameId == 1008)
        {
            $("#app-logo").attr("src", "/images/home/slots2/megalogo.png");
            $("#notes").html('*Above is the login details, get tutorials on how to download MEGA Apps on the right.');
            $("#text").html('Mega888 is the up and coming trending online casino in Asia because we feature the hottest games from the hottest providers. Mega888 has nothing but quality games, we have slot games, poker, baccarat, fishing games and many many more. You can play our live table games or you can play our single player games, it is all up to you, because our selections are aplenty, and we have the right game for anyone. Whether you are a casual gamer looking to relax or a hardcore gamer looking for a challenge, you will not find yourself bored when your a part of the Mega888 family.');
        }
        else if(gameId == 1009)
        {
            $("#app-logo").attr("src", "/images/home/slots2/918logo.png");
            $("#notes").html('*Above is the login details, get tutorials on how to download 918Kiss Apps on the right.');
            $("#text").html('918Kiss is not only your one-stop destination to unlimited online gaming, featuring all sorts of high quality entertainment ranging from slot games, fishing games, poker, baccarat and other unique live table games, we are also one of the top ranking online casinos in Asia due to the sheer number of bonuses, promotions, cash rebates and event bonuses that we have to offer to players.');
        }
        else if(gameId == 1010)
        {
            $("#app-logo").attr("src", "/images/home/slots2/scrlogo.png");
            $("#notes").html('*Above is the login details, get tutorials on how to download SCR888 Apps on the right.');
            $("#text").html('SCR888 is not only your one-stop destination to unlimited online gaming, featuring all sorts of high quality entertainment ranging from slot games, poker, baccarat and other unique live table games, we are also one of the top ranking online casinos in Asia due to the sheer number of bonuses, promotions, cash rebates and event bonuses that we have to offer to players.');
        }

        else if(gameId == 1011)
        {
            $("#app-logo").attr("src", "/images/home/slots2/pussylogo.png");
            $("#notes").html('*Above is the login details, get tutorials on how to download PUSSY GAMING Apps on the right.');
            $("#text").html('Pussy 888 ⭐ is the online casino ⚡ that is taking Malaysia by storm with its new and latest games and the best experience for a fun casino to play anywhere you like. It has the best of the best of online casino games such as the toto, 4d malaysia, live jackpot, latest Ocean King and live table games. They also have slot games too for those who want a more easy gaming experience. Covered in a blue theme and named funnily, this game will make you keep coming back for more especially if you like to win cash the fun way.');
        }
    });

</script>

<style>
label
{
    color: #000;
}
a, a:hover
{
    text-decoration: none;
    color: #fff;
}
</style>
@endsection

@section('content')  

<div class="w-100 p-2">
    <div style="background:white;border-radius:5px">
        <div class="py-4 px-2">
            <div class="container-fluid">

                <div class="row">

                    <div class="col-sm-12 col-md-6 py-4" style="background:#ffe2e2; border-radius: 4px;">

                        <div style="width: 100%; padding: 15px; display: flex; justify-content: center;">
                            <img id="app-logo" style="max-width: 100%">
                        </div>

                        <form>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control" value={{$loginId}} disabled>
                            </div>

                            @if($ft_password)
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="text" id="password" name="password" class="form-control" value={{$ft_password}} disabled>
                            </div>
                            @endif

                            <div class="form-group">
                                <i style="color: grey" id="notes"></i>
                            </div>
                        </form>
                    </div>

                    <div class="col-sm-12 col-md-6 text-center py-4 my-auto">

                        <div class="d-flex" style="justify-content: center;">
                            <a href="{{$iframe}}"" target="_blank" class="d-flex" style="cursor: pointer; background: lightgrey; border-radius: 4px; align-items: center; justify-content: center; padding: 5px 20px;">
                                <img style="width: 25px" src="/images/home/slots2/apple.png"><span style="margin-left: 10px;">iOS 64bit</span>
                            </a>

                            <a href="{{$iframe}}"" target="_blank" class="d-flex" style="cursor: pointer; background: lightgrey; border-radius: 4px; align-items: center; justify-content: center; padding: 5px 20px; margin-left: 15px;">
                                <img style="width: 25px" src="/images/home/slots2/android.png"><span style="margin-left: 10px;">Android</span>
                            </a>
                        </div>

                        <div>
                            <p id="text" style="text-align: justify; color: lightgrey; margin-top: 15px;">
                                
                            </p>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

</div>

@endsection