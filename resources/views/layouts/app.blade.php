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
    <script src="/air-datepicker/dist/js/datepicker.min.js"></script>
    <script src="/air-datepicker/dist/js/i18n/datepicker.en.js"></script>
    <script src="/air-datepicker/dist/js/i18n/datepicker.ko.js"></script>

    <!-- Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet"> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> -->
    <!-- <link href="/fonts/montserrat.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="/fonts/font-awesome.min.css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="/air-datepicker/dist/css/datepicker.min.css" rel="stylesheet" type="text/css">

    <script type="text/javascript">

        var locale = [];
        var isLogin = "{{ Auth::check() ? 1 : 0 }}";
        isLogin = (isLogin == '1');
        var userName = "{{ Auth::check() ? Auth::user()->username : '' }}";
        
        $(document).ready(function() 
        {
            history.scrollRestoration = 'manual';

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error : function(xhr,textStatus,errorThrown) 
                {
                    if(xhr.status == 440)
                        window.location.href = "/?k=1";
                    else if(xhr.status == 441)
                        window.location.href = "/?k=2";
                }
            });

            //timer
            $('#current-time').html(utils.getCurrentDateTime());

            timerTick = setInterval(function(){
                $('#current-time').html(utils.getCurrentDateTime());
            }, 1000);

            if(utils.getParameterByName("k") == 1)
            {
                alert("{!! __('error.login.multiple_login') !!}");
                window.history.pushState({}, document.title, "/");
            }
            else if(utils.getParameterByName("k") == 2)
            {
                alert("{!! __('error.login.account_inactive') !!}");
                window.history.pushState({}, document.title, "/");
            }

            if(isLogin)
            {
                
                //web socket
                createWS();

                Echo.private('fe-main.' + userName)

                    .listen('.msgNotif', (e) => 
                    {
                        console.log(e);
                        var unreadMsg = $('#unreadmsg').html();
                        $('#unreadmsg').html(e.pending_count);
                        if(e.pending_count > unreadMsg)
                        {
                            blink();
                            playAudio();
                        }

                        unreadMsg = e.pending_count;
  
                    });


                    Echo.private('fe-main.' + userName)

                        .listen('.promoNotif', (e) => 
                        {
                            console.log('promoNotif');
                            console.log(e);
                        });



            }
            else
            {
                //if WS is not used, need to remove hook created by Echo
                $.ajaxSetup({beforeSend: function(xhr){}})
            }
            
            prepareCommonLocale();
            // prepareLiveChat();

            //game launcher mode if mobile
            if(utils.isMobile())
            {
                $('.game-launcher').each(function(index) 
                {
                    this.href = this.href.replace('isMobile=0', 'isMobile=1');
                    this.target = '_blank';
                });
            }

            //marquee
            initMarquee();

            //sidenav
            window.addEventListener('resize', function(){toggleSidenav(true)});

            //set selected section
            if(window.location.pathname == '/')
            {
                var hash = window.location.hash.substring(1);

                if(hash == 'hot' || hash == 'casino' || hash == 'slots' || hash == 'sports')
                {
                    showSection(hash);

                    //clear hash for refresh
                    window.history.replaceState("", document.title, window.location.pathname);
                    window.location.hash = '';
                }
                else
                {
                    showSection('hot');
                }
            }


            //restore main wallet
            $("#restore-btn").click(function(e)
            {
                e.preventDefault();

                $.ajax({
                    type: "GET",
                    url: "/ajax/wallet/restore",
                    success: function(data)
                    {

                    }
                });
            });

        });

        function blink()
        {
            $('.blink-icon').addClass("blink");

        }

        function stopBlink()
        {
            $('.blink-icon').removeClass("blink");
        }

        function prepareCommonLocale()
        {
            locale['utils.datatable.totalrecords'] = "{!! __('common.datatable.totalrecords') !!}";
            locale['utils.datatable.norecords'] = "{!! __('common.datatable.norecords') !!}";
            locale['utils.datatable.invaliddata'] = "{!! __('common.datatable.invaliddata') !!}";
            locale['utils.datatable.total'] = "{!! __('common.datatable.total') !!}";
            locale['utils.datatable.pagetotal'] = "{!! __('common.datatable.pagetotal') !!}";
        }

        function createWS()
        {
            window.Echo.options = 
                {
                    broadcaster: 'pusher',
                    key: "{{ env('PUSHER_APP_KEY') }}",
                    cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
                    encrypted: false,
                    wsHost: "{{ env('PUSHER_WSHOST') }}",
                    wsPort: "{{ env('PUSHER_PORT') }}",
                    disableStats: true,
                };

            window.Echo.connect();
        }

        function flashDiv(div)
        {
            //maxFlash = -1 : won't stop

            if(div.flashCount > div.maxFlash && div.maxFlash != -1)
            {
                div.style.backgroundColor = '#4CAF50';
                return;
            }

            div.flashCount += 1;

            if(div.currentFlash)
            {
                div.currentFlash = false;
                div.style.backgroundColor = '#4CAF50';
            }
            else
            {
                div.currentFlash = true;
                div.style.backgroundColor = '#f86c6b';
            }

            div.flashTimer = window.setTimeout(function(){flashDiv(div)},1000); 
        }

        function playAudio() 
        { 
            var x = document.getElementById("audioAlert"); 
            var playPromise = x.play(); 

            if (playPromise !== undefined) 
            {
                playPromise.then(_ => 
                {
                  // Automatic playback started!
                  // Show playing UI.
                  // We can now safely pause video...
                  video.pause();
                })
                .catch(error => 
                {
                  // Auto-play was prevented
                  // Show paused UI.
                });
            }
        } 

        function prepareLiveChat()
        {
            var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
            (function(){
            var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
            s1.async=true;
            s1.src='https://embed.tawk.to/{{ env('TAWK_TOKEN') }}/default';
            s1.charset='UTF-8';
            s1.setAttribute('crossorigin','*');
            s0.parentNode.insertBefore(s1,s0);
            })();
        }

        if(isLogin)
        {
            function getBalance()
            {
                $("#refresh").addClass("fa-spin");

                $.ajax({
                        type: "GET",
                        url: "/ajax/user_balance",
                        success: function(data)
                        {
                            $("#refresh").removeClass("fa-spin");
                            
                            $("#balance").html(utils.formatMoney(data));
                        }
                    });

            }
        }

        var isSidenavOpen = true;

        function toggleSidenav(resize = false) 
        {
            var innerWidth = window.innerWidth;
            var sidenavWidth = 180;

            if(innerWidth <= 767)
            {
                sidenavWidth = 80;
            }

            if(!resize)
                isSidenavOpen = !isSidenavOpen;

            if(isSidenavOpen)
            {
                $('#sidenav').css({left:0});
                $('#main').css({marginLeft:sidenavWidth});  

                //table responsive purpose
                //for d-list and w-list blade used
                $("#main-container-detail").addClass("main-container-detail-w1");   
                $("#main-container-detail").removeClass("main-container-detail-w2");
            }
            else
            {
                $('#sidenav').css({left:-sidenavWidth});
                $('#main').css({marginLeft:0});

                //table responsive purpose
                //for d-list and w-list blade used
                $("#main-container-detail").addClass("main-container-detail-w2"); 
                $("#main-container-detail").removeClass("main-container-detail-w1");
            }
        }

        var isSidemoreOpen = false;

        function toggleSidemore() 
        {
            var innerWidth = window.innerWidth;
            var sidemoreWidth = parseInt($('#sidemore').css('width'));

            isSidemoreOpen = !isSidemoreOpen;

            if(isSidemoreOpen)
            {
                $('#sidemore').css({display:'block'}); 
                $('#sidemore').css({opacity:1});    
            }
            else
            {
                $('.sidemore').css({display:'none'});
                $('#sidemore').css({opacity:0}); 
            }
        }

        function showSection(elem)
        {
            $('#section-hot').css({display:'none'});
            $('#section-casino').css({display:'none'});
            $('#section-slots').css({display:'none'});
            $('#section-sports').css({display:'none'});
            $('#section-fishing').css({display:'none'});
            $('#section-lottery').css({display:'none'});
            $('#section-esport').css({display:'none'});

            $('#menu-section-hot').removeClass('selected');
            $('#menu-mobile-section-hot').removeClass('selected');
            $('#menu-section-casino').removeClass('selected');
            $('#menu-mobile-section-casino').removeClass('selected');
            $('#menu-section-slots').removeClass('selected');
            $('#menu-mobile-section-slots').removeClass('selected');
            $('#menu-section-sports').removeClass('selected');
            $('#menu-mobile-section-sports').removeClass('selected');
            $('#menu-section-fishing').removeClass('selected');
            $('#menu-mobile-section-fishing').removeClass('selected');
            $('#menu-section-lottery').removeClass('selected');
            $('#menu-mobile-section-lottery').removeClass('selected');
            $('#menu-section-esport').removeClass('selected');
            $('#menu-mobile-section-esport').removeClass('selected');

            if($('#section-' + elem).length)
            {
                $('#section-' + elem).css({display:'block'});
                $('#menu-section-' + elem).addClass('selected');
                $('#menu-mobile-section-' + elem).addClass('selected');

                window.scrollTo(0,0);
            }
            else
            {
                window.location.href = "/#" + elem;
            }
            
            
        }

        var marquee = {};
    
        function initMarquee()
        {
            marquee.holder = $('.marquee');
            marquee.txt = $('.marquee>p');

            var holderW = marquee.holder.innerWidth();

            marquee.txtX = holderW;

            marquee.txt.css({left:marquee.txtX + 'px'});
            
            resumeMarquee();

            marquee.holder.on("mouseover", pauseMarquee);
            marquee.holder.on("mouseout", resumeMarquee);
        }

        function marqueeTick()
        {
            var txtW = marquee.txt.innerWidth();

            marquee.txtX -= 2;

            if(marquee.txtX < -txtW)
            {
                var holderW = marquee.holder.innerWidth();
            
                marquee.txtX = holderW;
            }

            marquee.txt.css({left:marquee.txtX + 'px'});
        }

        function resumeMarquee()
        {
            clearInterval(marquee.timer);
            marquee.timer = setInterval(marqueeTick,30);
        }

        function pauseMarquee()
        {
            clearInterval(marquee.timer);
        }

        function openBannerWukong()
        {
            if(!isLogin)
            {
                window.location.href = '/login';
            }
            else if(utils.isMobile())
            {
                window.open('/game?gameId=5&type=2');
            }
            else
        {
            if(!$('#iframe-wukong>div>iframe').attr('src'))
                $('#iframe-wukong>div>iframe').attr('src', '/game?gameId=5&type=2');

            $('#iframe-wukong').css({right:0});
        }
        }

        function closeBannerWukong()
        {
            $('#iframe-wukong').css({right:-430});
        }

    </script>

    </script>

    <style type="text/css">
        
    body
    {
        font-family: Helvetica, sans-serif;
        font-size: 12px;
        color: white;
        background-color: #1d0413;
    }
    a:hover
    {
        text-decoration: none;
    }

    .main-container
    {
        height:100vh;
    }

    #logo-text
    {
        -webkit-mask: linear-gradient(-60deg,#000 30%,#0005,#000 70%) right/300% 100%;
        animation: shimmer 2.5s infinite;
    }

    /* CSS specific to iOS devices */ 
    @supports (-webkit-touch-callout: none) 
    {
        .main-container
        {
            /* expand height to cover available only*/
            height:-webkit-fill-available;
        }
    }

    #main
    {
        /*background-image: url('/images/bg.png');*/
        background-size: contain;
        background-position: top;
        background-repeat: repeat;
    }

    .pointer
    {
        cursor: pointer;
    }

    ::-webkit-scrollbar 
    {
        width: 8px;
        height: 8px;
        background-color: transparent;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb 
    {
        border-radius: 4px;
        background-color: #8574a6;
    }

    ::-webkit-scrollbar-track 
    {
        background: #26114b;
        width: 8px;
        height: 8px;
        border-radius: 0;
    }

    .btn
    {
        font-size:12px;
    }

    @media(max-width: 767px)
    {
       .timer
        {
            width:0px;
            display: none;
        } 
    }

    .header-link>div
    {
        background: linear-gradient(180deg,#a50037,#330a93);
        padding-top: 15px;
        color: white;
    }

    .header-link:hover
    {
        text-decoration: none;
    }

    .header-link:hover >div
    {
        background: linear-gradient(180deg,#79c1f4,#4300d2);
    }

    .menu-link>div,.menu-link-mobile>div
    {
        color: white;
        font-size: 12px;
        border-radius: 10px;
        background: linear-gradient(180deg,#330a93,#a50037);
        filter: brightness(0.8);
    }

    .menu-link-mobile>div>span
    {
        font-size: 8px;
        padding-top: 2px;
    }

    .menu-link:hover
    {
        text-decoration: none;
    }

    .menu-link:hover >div,.menu-link.selected>div,.menu-link-mobile:hover >div,.menu-link-mobile.selected>div
    {
        filter: brightness(1.5);
    }

    .sidenav 
    {
        position: fixed;
        width: 180px;
        height: calc(100% - 80px);
        z-index: 999;
        top: 80px;
        left: 0px;
        background-color: #000;
        overflow-x: hidden;
        overflow-y: auto;
        transition: 0.5s;
        color: white;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .sidenav::-webkit-scrollbar 
    {
        display: none;
    }

    #main 
    {
        margin-left:180px;
        transition: margin-left .5s;
    }

    @media(max-width: 767px)
    {
       .sidenav
        {
            width: 80px;
            padding-bottom: 60px;
        }

        #main 
        {
            margin-left:80px;
        }
    }

    .sidemore 
    {
        position: fixed;
        min-width: 200px;
        max-width: 500px;
        max-height: 60%;
        z-index: 999;
        top:80px;
        right:0px;
        background-color: white;
        overflow-x: hidden;
        overflow-y: auto;
        transition: 0.5s;
        color: black;
        border-radius: 3px;
        
    }

    .sidemore::-webkit-scrollbar 
    {
        display: none;
    }

    @media(max-width: 767px)
    {
        .sidemore 
        {
            max-width: 250px;
        }
    }


    /*hamburger*/
    @keyframes hamburger-wave 
    {
        from {width: 60%;}
        to {width: 100%;}
    }

    .hamburger>div
    {
        animation-name: hamburger-wave;
        animation-duration: 0.4s;
        animation-timing-function: ease-out;
        animation-delay: 0s; 
        animation-iteration-count: infinite;
        animation-direction: alternate;

        border-radius: 3px;
    }

    .hamburger>div:nth-child(2)
    {
        animation-delay: 0.3s;
    }

    .hamburger>div:nth-child(3)
    {
        animation-delay: 0.6s;
    }
  
    /* container with img as background with text centered +  shimmer effect */
    .container-button 
    {
        position: relative;
        text-align: center;
    }

    .container-button>span 
    {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color:white;
        filter: drop-shadow(0 1px 1px #0005);
        font-size:15px;

        /*shimmer*/
        -webkit-mask:linear-gradient(-60deg,#000 30%,#0005,#000 70%) right/300% 100%;
        background-repeat: no-repeat;
        animation: shimmer 2.5s infinite;
    }

    @keyframes shimmer 
    {
        100% {-webkit-mask-position:left}
    }

    .glow-gold
    {
        animation: glow-gold 2.5s infinite;
    }

    @keyframes glow-gold
    {
        0% { box-shadow: 0 0 13px 3px #0000; }
        50% { box-shadow: 0 0 13px 3px #fcd341; }
        100% { box-shadow: 0 0 13px 3px #0000; }
    }

    .footer-title,.footer-title>a
    {
        color: #eb77cf;
    }

    /*marquee*/
    .marquee 
    {
        overflow: hidden;
        position: relative;
        width:100%;
        height:20px;
    }

    .marquee>p 
    {
        position: absolute;
        left: 0;
        top: 0;
        white-space: nowrap;
    }

    .logo
    {
        height: 40px;
    }

    @media(max-width: 767px)
    {
       .logo
        {
            height: 30px;
        }
    }

    .footer
    {
        padding-bottom:0px;
    }

    .footer-link
    {
        color: #8E8EA7;
    }

    @media(max-width: 767px)
    {
       .footer
        {
            padding-bottom:60px;
        }
    }

    .footerfixed-link>div
    {
        color: white;
        font-size: 8px;
    }

    .footerfixed-link-mid>div>div
    {
        background: linear-gradient(180deg,#ffe872 25%,#ffa021);
        color: white;
        font-size: 8px;

        /*shimmer*/
        -webkit-mask:linear-gradient(-60deg,#000 30%,#0005,#000 70%) right/300% 100%;
        background-repeat: no-repeat;
        animation: shimmer 2.5s infinite;
    }

    .footerfixed-link:hover,.footerfixed-link-mid:hover
    {
        text-decoration: none;
    }

    #banner-wukong
    {
        height:80px;
    }

    #banner-wukong>img
    {
        height:100%;
        animation: banner-hover 2s infinite alternate;
    }

    @keyframes banner-hover
    {
        0% { margin-left: 30px; }
        100% { margin-left: 70px; }   
    }

    @media(max-width: 767px)
    {
        @keyframes banner-hover
        {
            0% { margin-left: -10px; }
            100% { margin-left: 10px; }   
        }
    }

    #iframe-wukong
    {
        z-index: 998;
        position:fixed;
        top:80px;
        right:-430px;
        background-color: white;
        color:black;

        transition: right 1s;
    }

    #iframe-wukong>div
    {
        width: 430px;
        height: 300px;
    }

    </style>

</head>
<body>

    <audio id="audioAlert">
        <source src="/audio/ogg/definite.ogg" type="audio/ogg">
        <source src="/audio/mpeg/definite.mp3" type="audio/mpeg">
    </audio>

    <div class="p-0 m-0" style="position:fixed;width:100%;height:80px;top:0px;left:0px;z-index:999;">
        
        <table border=0 style="width:100%;height:20px;color:white;background-color:black" cellpadding="0" cellspacing="0">
            <tr>
                <td width="1">
                    <span class="fa fa-volume-up pl-2 py-0" style="font-size:16px">&nbsp;</span>
                </td>
                <td class="">
                    <div class="marquee"><p>Special Announcement : Bookmark your fortune now.</p></div>
                </td>
                <td class="timer" style="width:135px;color:#9be1ff;">
                    <div id="current-time" style="font-size:12px;padding-left: 15px;font-weight: bold">&nbsp;</div>
                </td>
            </tr>
        </table>

        <table border=0 style="width:100%;height:60px;color:white;background: linear-gradient(180deg,#a50037,#000);" cellpadding="0" cellspacing="0">
            <tr>
                <td width="1" class="pl-2 pr-2">
                    <div class="hamburger d-flex flex-column justify-content-center pointer" style="width:30px;height:40px;" onclick="toggleSidenav()">
                        <div style="height:3px;background-color: white;margin:3px 0px"></div>
                        <div style="height:3px;background-color: white;margin:3px 0px"></div>
                        <div style="height:3px;background-color: white;margin:3px 0px"></div>
                    </div>
                </td>
                <td width="1">
                    <a href="/" style="display: flex; align-items: center;">
                        <img class="logo" src="/images/app/logo.png">
                        <span id="logo-text" style="font-size: 25px; margin-left: 5px; font-style: italic; font-weight: 700; color: #fff">Royale4Bet</span>
                    </a>
                </td>
                <td>&nbsp;</td>
                <td width="1" class="pl-md-2">
                    <a href="/promo" class="header-link d-none d-md-block">
                        <div class="p-2" style="width:80px;height:60px">
                            <div class="d-flex justify-content-center">
                                <img src="/images/app/promo.png" style="height:30px">
                            </div>
                            <div class="d-flex justify-content-center">PROMOS</div>
                        </div>
                    </a>
                </td>
                <!-- <td width="1" class="pl-md-2">
                    <a href="/" class="header-link d-none d-md-block">
                        <div class="p-2" style="width:80px;height:60px">
                            <div class="d-flex justify-content-center">
                                <img src="/images/app/footer/logo.png" style="height:30px">
                            </div>
                            <div class="d-flex justify-content-center">BONUS</div>
                        </div>
                    </a>
                </td>
                <td width="1" class="pl-md-2">
                    <a href="/" class="header-link d-none d-md-block">
                        <div class="p-2" style="width:80px;height:60px">
                            <div class="d-flex justify-content-center">
                                <img src="/images/app/vip.png" style="height:30px">
                            </div>
                            <div class="d-flex justify-content-center">VIP</div>
                        </div>
                    </a>
                </td> -->

                @guest
                    <td width="1" class="pl-md-3">
                        <a href="/login" class="d-none d-md-block">
                            <div class="container-button">
                                <img src="/images/app/btn-blue.png" class="" style="width:120px;height:35px;box-shadow:0 0 10px #94c5ffcc;border-radius:5px">
                                <span>{{ __('app.header.login') }}</span>
                            </div>
                        </a>
                    </td>

                    <td width="1" class="">
                        <a href="/register" class="d-block d-md-none">
                            <div class="container-button">
                                <img src="/images/app/btn-gold.png" class="glow-gold" style="width:100px;height:30px;border-radius:5px">
                                <span>REGISTER</span>
                            </div>
                        </a>
                    </td>
                @endguest

                @auth
                    <td width="1" class="pl-3 pr-2">
                        <a href="/message/inbox" style="position: relative; color: white">
                            <i class="fa fa-envelope" style="font-size: 25px; cursor: pointer"></i>
                            <span id="unreadmsg" style="background:#a71f67;color:#fff;padding:0 5px;border-radius:50%;font-size:10px;position:absolute;top:-15px;right:-10px">{{ $unreadMsg }}</span>
                        </a>
                    </td>
                @endauth

                <td width="1" class="pl-3 pr-2">
                    <img src="/images/app/more.png" class="pointer" style="height:25px;filter: brightness(0) invert(1);" onclick="toggleSidemore()">
                </td>

            </tr>
        </table>

    </div>

    <div id="sidenav" class="sidenav" style="">

        @guest
            <div class="m-2 d-none d-md-block" style="">
                <a href="/register">
                    <div class="container-button">
                        <img src="/images/app/btn-gold.png" class="glow-gold" style="width:100%;border-radius:5px">
                        <span>REGISTER</span>
                    </div>
                </a>
            </div>
        @else
            <div class="m-2 d-none d-md-block" style="font-weight: bold">
                <div>{{ __('app.header.welcome') }}, {{ Auth::user()->username }}</div>
                <span>{{ __('app.header.balance') }} : {{ $userCurrency }}</span>
                <span id="balance">{{ Helper::formatMoney($userBalance) }}</span>
                <div id="restore-btn" style="background: url('/images/app/more/wallet.png'); background-repeat: round; height: 50px; display: flex; align-items: center; justify-content: center; margin-top: 10px; cursor: pointer;">
                    <span style="margin-left: 30px;">RESTORE <i class="fa fa-spin fa-spinner" style="display: none"></i></span>
                </div>
            </div>

        @endguest

        <div id="banner-wukong">
            <img src="/images/app/wukong.png" class="pointer" onclick="openBannerWukong()">
        </div>

        <div class="mx-2 my-2 d-none d-md-block" style="">
            <a id="menu-section-hot" href="javascript:showSection('hot')" class="menu-link">
                <div class="" style="width:100%;padding:8px 8px">
                    <img src="/images/app/menu/popular.png" style="height:40px">
                    <span class="pl-2">HOT GAMES</span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-block d-md-none" style="">
            <a id="menu-mobile-section-hot" href="javascript:showSection('hot')" class="menu-link-mobile">
                <div class="d-flex align-items-center flex-column" style="width:100%;padding:8px 2px">
                    <img src="/images/app/menu/popular.png" style="height:40px">
                    <span><center>HOT GAMES</center></span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-none d-md-block" style="">
            <a id="menu-section-casino" href="javascript:showSection('casino')" class="menu-link">
                <div class="" style="width:100%;padding:8px 8px">
                    <img src="/images/app/menu/casino.png" style="height:40px">
                    <span class="pl-2">CASINO</span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-block d-md-none" style="">
            <a id="menu-mobile-section-casino" href="javascript:showSection('casino')" class="menu-link-mobile">
                <div class="d-flex align-items-center flex-column" style="width:100%;padding:8px 2px">
                    <img src="/images/app/menu/casino.png" style="height:40px">
                    <span><center>CASINO</center></span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-none d-md-block" style="">
            <a id="menu-section-slots" href="javascript:showSection('slots')" class="menu-link">
                <div class="" style="width:100%;padding:8px 8px">
                    <img src="/images/app/menu/slots.png" style="height:40px">
                    <span class="pl-2">SLOTS</span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-block d-md-none" style="">
            <a id="menu-mobile-section-slots" href="javascript:showSection('slots')" class="menu-link-mobile">
                <div class="d-flex align-items-center flex-column" style="width:100%;padding:8px 2px">
                    <img src="/images/app/menu/slots.png" style="height:40px">
                    <span><center>SLOTS</center></span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-none d-md-block" style="">
            <a id="menu-section-sports" href="javascript:showSection('sports')" class="menu-link">
                <div class="" style="width:100%;padding:8px 8px">
                    <img src="/images/app/menu/sports.png" style="height:40px">
                    <span class="pl-2">SPORTS</span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-block d-md-none" style="">
            <a id="menu-mobile-section-sports" href="javascript:showSection('sports')" class="menu-link-mobile">
                <div class="d-flex align-items-center flex-column" style="width:100%;padding:8px 2px">
                    <img src="/images/app/menu/sports.png" style="height:40px">
                    <span><center>SPORTS</center></span>
                </div>
            </a>
        </div>

        <!-- <div class="mx-2 my-2 d-none d-md-block" style="">
            <a id="menu-section-fishing" href="javascript:showSection('fishing')" class="menu-link">
                <div class="" style="width:100%;padding:8px 8px">
                    <img src="/images/app/menu/fishing.png" style="height:40px">
                    <span class="pl-2">FISHING</span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-block d-md-none" style="">
            <a id="menu-mobile-section-fishing" href="javascript:showSection('fishing')" class="menu-link-mobile">
                <div class="d-flex align-items-center flex-column" style="width:100%;padding:8px 2px">
                    <img src="/images/app/menu/fishing.png" style="height:40px">
                    <span><center>FISHING</center></span>
                </div>
            </a>
        </div> -->

<!--         <div class="mx-2 my-2 d-none d-md-block" style="">
            <a href="/" class="menu-link">
                <div class="" style="width:100%;padding:8px 8px">
                    <img src="/images/app/menu/skills.png" style="height:40px">
                    <span class="pl-2">SKILL GAMES</span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-block d-md-none" style="">
            <a href="/" class="menu-link-mobile">
                <div class="d-flex align-items-center flex-column" style="width:100%;padding:8px 2px">
                    <img src="/images/app/menu/skills.png" style="height:40px">
                    <span><center>SKILL GAMES</center></span>
                </div>
            </a>
        </div> -->

<!--         <div class="mx-2 my-2 d-none d-md-block" style="">
            <a id="menu-section-esport" href="javascript:showSection('esport')" class="menu-link">
                <div class="" style="width:100%;padding:8px 8px">
                    <img src="/images/app/menu/esports.png" style="height:40px">
                    <span class="pl-2">E-SPORTS</span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-block d-md-none" style="">
            <a id="menu-mobile-section-esport" href="javascript:showSection('esport')" class="menu-link-mobile">
                <div class="d-flex align-items-center flex-column" style="width:100%;padding:8px 2px">
                    <img src="/images/app/menu/esports.png" style="height:40px">
                    <span><center>E-SPORTS</center></span>
                </div>
            </a>
        </div> -->

<!--         <div class="mx-2 my-2 d-none d-md-block" style="">
            <a id="menu-section-lottery" href="javascript:showSection('lottery')" class="menu-link">
                <div class="" style="width:100%;padding:8px 8px">
                    <img src="/images/app/menu/lottery.png" style="height:40px">
                    <span class="pl-2">Lottery</span>
                </div>
            </a>
        </div>

        <div class="mx-2 my-2 d-block d-md-none" style="">
            <a id="menu-mobile-section-lottery" href="javascript:showSection('lottery')" class="menu-link-mobile">
                <div class="d-flex align-items-center flex-column" style="width:100%;padding:8px 2px">
                    <img src="/images/app/menu/lottery.png" style="height:40px">
                    <span><center>Lottery</center></span>
                </div>
            </a>
        </div> -->

<!--         <div class="mx-2 my-2 d-none d-md-block" style="">
            <a href="/">
                <div class="container-button">
                    <img src="/images/app/menu/wechat.png" class="" style="width:100%;border-radius:10px">
                </div>
            </a>
        </div> -->

    </div>


    <table class="main-container" border=0 style="width:100%" cellpadding="0" cellspacing="0">
        <tr><td style="height:80px">&nbsp;</td></tr>
        <tr>
            <td>    
                @yield('head')

                <main id="main" class="py-0" style="height:100%">

                    <table border=0 style="width:100%;height:100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td valign="top">@yield('content')</td>
                        </tr>
                        <tr height="1">
                            <td colspan="2">
                                <div class="container-fluid footer" style="background: linear-gradient(to bottom, #1d0610, #000);color:white;padding-top:100px">
                                    
                                    <div class="row">

                                        <div class="col-6 col-md-4 col-xl pb-5">
                                            
                                            <span class="footer-title">Information</span>
                                            <br>
                                            <a class="footer-link" href="/tnc/tnc.pdf" target="_blank">Terms & Conditions</a>
                                            <br>
                                            <a class="footer-link" href="/tnc/about_us.pdf" target="_blank">About us</a>
                                            <br>
                                            <a class="footer-link" href="/tnc/responsible_gaming.pdf" target="_blank">Responsible Gaming</a>
                                            <br>

                                        </div>

                                        <div class="col-6 col-md-4 col-xl pb-5">
                                            
                                            <span class="footer-title">Certification</span>
                                            <br>
                                            <img src="/images/app/footer/bmm.png" style="width:50px;padding-top:10px">
                                            <img src="/images/app/footer/itech.png" style="width:40px;padding-left:5px;padding-top:10px">
                                            <img src="/images/app/footer/gli.png" style="width:40px;padding-left:5px;padding-top:10px">

                                        </div>

                                        <div class="col-6 col-md-4 col-xl pb-5">
                                            
                                            <span class="footer-title">Payment Method</span>
                                            <br>
                                            <img src="/images/app/footer/visa.png" style="width:40px;padding-top: 20px">
                                            <img src="/images/app/footer/master.png" style="width:40px;padding-left:5px;padding-top: 20px">
                                            <img src="/images/app/footer/fpx.png" style="width:50px;padding-left:5px;padding-top: 20px">

                                        </div>

                                        <div class="col-6 col-md-4 col-xl pb-5">
                                            
                                            <span class="footer-title">Gaming License</span>
                                            <br>
                                            <img src="/images/app/footer/curacao.png" style="width:80px;padding-top:10px">

                                        </div>

                                        <!-- <div class="col-6 col-md-4 col-xl pb-5">
                                            
                                            <span class="footer-title">Follow Us</span>
                                            <br>
                                            <a href="/" class="footer-link-img">
                                                <img src="/images/app/footer/facebook.png" style="width:35px;padding-top:10px">
                                            </a>
                                            <a href="/" class="footer-link-img">
                                                <img src="/images/app/footer/youtube.png" style="width:35px;padding-top:10px">
                                            </a>
                                            <a href="/" class="footer-link-img">
                                                <img src="/images/app/footer/instagram.png" style="width:35px;padding-top:10px">
                                            </a>
                                            <a href="/" class="footer-link-img">
                                                <img src="/images/app/footer/twitter.png" style="width:35px;padding-top:10px">
                                            </a>

                                        </div> -->

                                    </div>

                                    <hr style="background-color:#8E8EA7;height:1px; border: none;">

                                    <div class="container-fluid" style="">
                                        <div class="row">
                                            <div class="col-12 col-lg pb-3 d-flex align-items-center justify-content-center justify-content-lg-start">
                                                <span class="pr-2" style="color:white;font-size:14px">Powered by</span>
                                                <img src="/images/app/footer/moontech.png" style="width:40px;">
                                            </div>

                                            <div class="col-12 col-lg pb-3 d-flex align-items-center justify-content-center justify-content-lg-end" style="">
                                                <span class="pr-2 text-center" style="color:#8E8EA7;font-size:10px">COPYRIGHT © {{ date('Y') }} ROYALE4BET™ ALL RIGHTS RESERVED</span>
                                                <img src="/images/app/footer/18.png" style="width:30px;">
                                            </div>
                                            
                                        </div>

                                    </div>

                                </div>
                                
                            </td>
                        </tr>
                    </table>

                </main>
            </td>
        </tr>
    </table>

    <div class="d-block d-md-none" style="position:fixed;width:100%;height:60px;bottom:0px;left:0px;z-index:999">
        
        <table border=0 style="width:100%;height:100%;background: linear-gradient(180deg,#a50037,#000);" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width:50%;">
                    <div class="d-flex justify-content-around align-items-center" style="">
                        <div>
                            <a href="#" onclick="window.Tawk_API.popup();" class="footerfixed-link" style="">
                                <div class="d-flex align-items-center flex-column" style="">
                                    <img src="/images/app/footer/wechat.png" style="height:40px;">
                                    <span><center>CONTACT US</center></span>
                                </div>
                            </a>
                        </div>
                        <div>
                            <a href="/" class="footerfixed-link" style="">
                                <div class="d-flex align-items-center flex-column" style="">
                                    <img src="/images/app/vip.png" style="height:40px;">
                                    <span><center>DEPOSIT</center></span>
                                </div>
                            </a>
                        </div>
                    </div>
                </td>
                <td width="1" style="height:100%;padding:0px 15px" valign="top">
                    <div class="" style="">
                        <a href="/" class="footerfixed-link-mid" style="">
                            <div style="background-color: white;border-radius:60px" class="glow-gold">
                                <div class="d-flex align-items-center flex-column" style="width:60px;height:60px;padding:6px 0px;border-radius:60px;">
                                    <img src="/images/app/footer/home-logo-1.png" style="width:45px;">
                                    <span><center>HOME</center></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </td>
                <td style="width:50%;">
                    <div class="d-flex justify-content-around align-items-center" style="">
                        <div>
                            <a href="/" class="footerfixed-link" style="">
                                <div class="d-flex align-items-center flex-column" style="">
                                    <img src="/images/app/footer/promos.png" style="height:40px;">
                                    <span><center>PROMOS</center></span>
                                </div>
                            </a>
                        </div>

                        @guest
                            <div>
                                <a href="/login" class="footerfixed-link" style="">
                                    <div class="d-flex align-items-center flex-column" style="">
                                        <img src="/images/app/footer/login.png" style="height:40px;">
                                        <span><center>{{ __('app.header.login') }}</center></span>
                                    </div>
                                </a>
                            </div>
                        @else
                            <div>
                                <a href="{{ route('logout') }}" class="footerfixed-link" style="" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    <div class="d-flex align-items-center flex-column" style="">
                                        <img src="/images/app/footer/logout.png" style="height:40px;">
                                        <span><center>LOGOUT</center></span>
                                    </div>
                                </a>
                            </div>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        @endguest

                    </div>
                </td>
            </tr>
        </table>

    </div>

    <div id="sidemore" class="sidemore p-2 mr-2" style="display: none;">
        
        @auth
            <div class="p-1" style="font-weight:bold">
                <div>{{ __('app.header.welcome') }}, {{ Auth::user()->username }}</div>
                <span>{{ __('app.header.balance') }} : {{ $userCurrency }}</span>
                <span id="balance">{{ Helper::formatMoney($userBalance) }}</span>
            </div>

            <div class="container">
                <div class="row">

                    <div class="col-6 col-md-3 p-1">
                        <a href="/account">
                            <button type="button" class="btn btn-primary w-100">
                                <div class="d-flex justify-content-center">
                                    <span class="fa fa-user" style="font-size:30px">
                                </div>
                                <div class="d-flex justify-content-center">{{ __('app.header.my_account') }}</div>
                            </button>
                        </a>
                    </div>           

                    <div class="col-6 col-md-3 p-1">
                        <a href="/my_profile/deposit/new?bank">
                            <button type="button" class="btn btn-primary w-100">
                                <div class="d-flex justify-content-center">
                                    <span class="fa fa-money" style="font-size:30px">
                                </div>
                                <div class="d-flex justify-content-center">Deposit</div>
                            </button>
                        </a>
                    </div>

                    <div class="col-6 col-md-3 p-1">
                        <a href="/my_profile/withdraw/new">
                            <button type="button" class="btn btn-primary w-100">
                                <div class="d-flex justify-content-center">
                                    <span class="fa fa-money" style="font-size:30px">
                                </div>
                                <div class="d-flex justify-content-center">Withdraw</div>
                            </button>
                        </a>
                    </div>

                    <div class="col-6 col-md-3 p-1">
                        <a href="/transfer">
                            <button type="button" class="btn btn-primary w-100">
                                <div class="d-flex justify-content-center">
                                    <span class="fa fa-exchange" style="font-size:30px">
                                </div>
                                <div class="d-flex justify-content-center">Wallet</div>
                            </button>
                        </a>
                    </div>
                    
                </div>
                <div class="row">

                    <div class="col-6 col-md-3 p-1">
                        <a href="/history">
                            <button type="button" class="btn btn-primary w-100">
                                <div class="d-flex justify-content-center">
                                    <span class="fa fa-list" style="font-size:30px">
                                </div>
                                <div class="d-flex justify-content-center">History</div>
                            </button>
                        </a>
                    </div>

                    <!-- <div class="col-6 col-md-3 p-1">
                        <a href="/my_profile/deposit/new?status">
                            <button type="button" class="btn btn-primary w-100">
                                <div class="d-flex justify-content-center">
                                    <span class="fa fa-money" style="font-size:30px">
                                </div>
                                <div class="d-flex justify-content-center" style="font-size:10px;">Deposit List</div>
                            </button>
                        </a>
                    </div>

                    <div class="col-6 col-md-3 p-1">
                        <a href="/my_profile/withdraw/new?status">
                            <button type="button" class="btn btn-primary w-100">
                                <div class="d-flex justify-content-center">
                                    <span class="fa fa-money" style="font-size:30px">
                                </div>
                                <div class="d-flex justify-content-center" style="font-size:10px;">Withdraw List</div>
                            </button>
                        </a>
                    </div>

                    <div class="col-6 col-md-3 p-1">
                        <a href="/my_profile/referral">
                            <button type="button" class="btn btn-primary w-100">
                                <div class="d-flex justify-content-center">
                                    <span class="fa fa-money" style="font-size:30px">
                                </div>
                                <div class="d-flex justify-content-center" style="font-size:10px;">Referral</div>
                            </button>
                        </a>
                    </div> -->
                </div>
            </div>

            <div class="p-1">
                <button type="button" class="btn btn-danger w-100" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    LOGOUT
                </button>
            </div>

        @endauth

        <div class="">

            <form id="form-locale" action="{{ route('locale') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" id="locale" name="locale" value="">
            </form>

            <div class="container">
                <div class="row">
                    <div class="col-6 p-1">
                        <button type="button" class="btn btn-secondary w-100" onclick="event.preventDefault();document.getElementById('locale').value = 'en';document.getElementById('form-locale').submit();">
                            EN
                        </button>
                    </div>
                    <div class="col-6 p-1">
                        <button type="button" class="btn btn-secondary w-100" onclick="event.preventDefault();document.getElementById('locale').value = 'zh-cn';document.getElementById('form-locale').submit();">
                            BAHASA MELAYU
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>


    <div id="iframe-wukong" class="d-none d-md-block" onclick="closeBannerWukong()">
        <span style="font-size:14px" class="pl-2">X</span>
        <div>
            <iframe style="width:100%;height:100%;border:1px solid white"></iframe>
        </div>
    </div>


    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        (function()
        {
            var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
            s1.async=true;
            s1.src='https://embed.tawk.to/6384c906daff0e1306d9d7a1/1givb2h1t';
            s1.charset='UTF-8';
            s1.setAttribute('crossorigin','*');
            s0.parentNode.insertBefore(s1,s0);


        })();

        if (window.matchMedia("(max-width: 576.98px)").matches)
        {
            window.Tawk_API.onLoad = function(){
                window.Tawk_API.hideWidget();
            };
        }
        
    </script>
    <!--End of Tawk.to Script-->
</body>
</html>
