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
    <script src="{{ asset('js/profile.js') }}" defer></script>

    <!-- JqueryUI -->
    <script src="/jquery/jquery.datetimepicker.js"></script>

    <link href="/jquery/jquery.datetimepicker.min.css" rel="stylesheet">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/profile/public.css') }}" rel="stylesheet">
    <link href="{{ asset('css/profile/icomoon.css') }}" rel="stylesheet">
    <link href="{{ asset('css/profile/main.css') }}" rel="stylesheet">
    <link href="{{ asset('css/profile/sp.css') }}" rel="stylesheet">

	<script type="text/javascript">

		var locale = [];

		$(document).ready(function()
		{
			prepareCommonLocale();

			var month = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

			var isLogin = "{{ Auth::check() ? 1 : 0 }}";
			isLogin = (isLogin == '1');
			var userName = "{{ Auth::check() ? Auth::user()->username : '' }}";
			unreadMsg = '{{$unreadMsg}}';
			$('.unreadmsg').html('('+unreadMsg+')');


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
                        // console.log(e);
                        $('.unreadmsg').html("("+e.pending_count+")");


                        if(e.pending_count > unreadMsg)
                        {
                        	playAudio();
                        }

                        unreadMsg = e.pending_count;

                        
                    });
            }
            else
            {
                //if WS is not used, need to remove hook created by Echo
                $.ajaxSetup({beforeSend: function(xhr){}})
            }

			$.ajax({
				type: "GET",
				url: "/ajax/profile/user-details",
				success: function(data)
				{
					var balance = data['balance'];
					var currency = data['currency'];
					var email = data['email'];
					var registerDate = new Date(data['created_at']);
					var lastLogin = new Date(data['last_login']);

					$("#email").html(email);
					$("#username").html("{{Auth::user()->username}}");
					$("#register-date").html(registerDate.getDate()+' '+month[registerDate.getMonth()]+' '+registerDate.getFullYear());
					$("#last-login-date").html(lastLogin.getDate()+' '+month[lastLogin.getMonth()]+' '+lastLogin.getFullYear()+' '+data['last_login'].slice(11));
					$("#user-balance").html(utils.formatMoney(balance));
					$("#currency").html(currency);
				}
			});

		});

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

		function showSideBar()
		{
			var sidebar = document.getElementById("profile-sidebar");

			$(sidebar).show();
				$("#mask").show();

			$("#mask").click(function(event)
			{
				$(sidebar).hide();
				$(this).hide();
			});
		}

		function prepareCommonLocale()
        {
            locale['utils.datatable.totalrecords'] = "{!! __('common.datatable.totalrecords') !!}";
            locale['utils.datatable.norecords'] = "{!! __('common.datatable.norecords') !!}";
            locale['utils.datatable.invaliddata'] = "{!! __('common.datatable.invaliddata') !!}";
            locale['utils.datatable.total'] = "{!! __('common.datatable.total') !!}";
            locale['utils.datatable.pagetotal'] = "{!! __('common.datatable.pagetotal') !!}";
        }

	</script>

	<style>
		body
		{
			font-weight: 800;
			font-size: 12px;
			background: #0c041b;
		}
		.modal-btn
		{
			min-width: 50px !important;
			height: 100% !important;
			line-height: 2rem !important;
			box-shadow: none !important;
			padding: 0 !important;
			font-size: 12px !important;
		}
		.sorting 
		{ 
		    cursor:pointer;
		    position:relative;
		    padding-right: 2em !important;
		}
		.sorting::before 
		{ 
		    content: "\2191";
		    opacity:0.4;
		    right:1.5rem;
		    position:absolute;
		}
		.sorting::after 
		{ 
		    content: "\2193";
		    opacity:0.4;
		    right:0.75em;
		    position:absolute;
		}
		.sorting-asc::before 
		{ 
		    opacity:1;
		}
		.sorting-desc::after 
		{ 
		    opacity:1;
		}
		.pagination
		{
			margin-top: unset !important;
		}
		.pagination li
		{
			width: unset !important;
			background: none !important;
			margin: unset !important;
		}
		.pagination li.page-item
		{
			background: rgba(143, 145, 147, .5) !important;
			padding: 0 8px;
			margin-right: 10px !important;
		}
		.pagination li.page-item.active
		{
			background: #a71f67 !important;
		}
		.page-link
		{
			color: #fff;
			background: none !important;
			border: 0 !important;
		}
		.page-link:hover
		{
			color: #fff !important;
		}
		.div-scrollable::-webkit-scrollbar 
        {
          width: 2px;
        }

        /* Track */
        .div-scrollable::-webkit-scrollbar-track
        {
          background: #333; 
        }
         
        /* Handle */ 
        .div-scrollable::-webkit-scrollbar-thumb 
        {
          background: #888; 
        }

        /* Handle on hover */
        .div-scrollable::-webkit-scrollbar-thumb:hover
        {
          background: #555; 
        }
        .modal-backdrop.fade
        {
        	z-index: -1;
        }
	</style>

</head>

<body>

	<audio id="audioAlert">
        <source src="/audio/ogg/definite.ogg" type="audio/ogg">
        <source src="/audio/mpeg/definite.mp3" type="audio/mpeg">
    </audio>

	<div class="sb-account open desktop">
		<div class="account-container">
			<span class="close icon-icon-clear" onclick="window.location.href='/'"></span>
            <div class="sidebar desktop" id="sidebar">
                <div class="title">
                	<div>
                		<div class="profile-info">
                            <div class="profile-header">
                                <div class="image-block ">
                                    <div class="profile-image-big" style="background-image: url('/images/profile/icon-profile.png')">
                                    </div>
                                    <div class="overlay">
                                        <span class="icon-sb-change-avatar"></span>
                                    </div>
                                </div>
                                <div class="profile-info-inner">
                                    <div class="profile-name">
                                        <span id="email"></span> </div>
                                    <div class="profile-id">
                                        <span>ID:&nbsp;</span>
                                        <span id="username"></span>
                                        <span class="info-icon-container">
                                            <i class="icon-icon-info"></i>
                                            <div class="account-info-show-more">
                                                <div class="account-user-info">
                                                    <div class="info-dates">
                                                        <span class="date-text">{{ __('app.profile.registerdate') }}</span>
                                                        <span id="register-date" style="font-size:12px"></span>
                                                    </div>
                                                    <div class="info-dates">
                                                        <span class="date-text">{{ __('app.profile.lastlogin') }}</span>
                                                        <span id="last-login-date" style="font-size:12px"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="balance">
                                <div class="total ">
                                    <span class="balance-label">{{ __('app.profile.mainbalance') }}</span>
                                    <div style="display:inline-flex;">
	                                    <span id="user-balance"></span>
	                                    &nbsp;
	                                    <span id="currency"></span>
	                                </div>
                                </div>
                            </div>
                    	</div>
                	</div>
                	<div>
						<span id="sidebar-toggle-icon" class="icon-icon-close1-lsb"></span>
					</div>
				</div>

				<div class="sections">
	                <div class="accordion-container section wallet">
	                    <div class="accordion-title">
	                        <div>
	                            <span class="icon-sb-wallet"></span>
	                            <span class="span-title">{{ __('app.profile.sidebar.mywallet') }}</span>
	                        </div>
	                        <div class="accordion-arrow">
	                            <i class="icon-icon-arrow-down"></i>
	                        </div>
	                    </div>
	                    <div class="accordion-content ">
	                        <div class="accordion-content-inner">
	                            <div class="accordion-item section-tab deposit" onclick="window.location.href='/my_profile/deposit/new?crypto'">
	                                <a href="/my_profile/deposit/new?crypto">
	                                	{{ __('app.profile.sidebar.mywallet.deposit') }}
	                                </a>
	                            </div>
	                            <div class="accordion-item section-tab withdraw" onclick="window.location.href='/my_profile/withdraw/new?crypto'">
	                                <a href="/my_profile/withdraw/new?crypto">
	                                	{{ __('app.profile.sidebar.mywallet.withdraw') }}
	                                </a>
	                            </div>
	                        </div>
	                    </div>
	                </div>

	                <div class="accordion-container section profile selected">
	                    <div class="accordion-title">
	                        <div>
	                            <span class="icon-sb-profile"></span>
	                            <span class="span-title">{{ __('app.profile.sidebar.memberprofile') }}</span>
	                        </div>
	                        <div class="accordion-arrow">
	                            <i class="icon-icon-arrow-down"></i>
	                        </div>
	                    </div>
	                    <div class="accordion-content">
	                        <div class="accordion-content-inner">
	                        	
	                            <div class="accordion-item section-tab selected" onclick="window.location.href='/my_profile/personal_info'">
	                                <a class="section-tab-a" href="/my_profile/personal_info">
	                                	{{ __('app.profile.sidebar.memberprofile.personal_info') }}
	                                </a>
	                            </div>
	                            <div class="accordion-item section-tab" onclick="window.location.href='/my_profile/change_pw'">
	                                <a class="section-tab-a" href="/my_profile/change_pw">
	                                	{{ __('app.profile.sidebar.memberprofile.change_pw') }}
	                                </a>
	                            </div>
<!-- 	                            <div class="accordion-item section-tab">
	                                <a class="section-tab-a" href="/my_profile/bank_info">
	                                	{{ __('app.profile.sidebar.memberprofile.bank_info') }}
	                                </a>
	                            </div> -->
	                        </div>
	                    </div>
	                </div>

	                <div class="accordion-container section my-bets">
	                    <div class="accordion-title">
	                        <div>
	                            <span class="icon-sb-my-bets"></span>
	                            <span class="span-title">
	                            	{{ __('app.profile.sidebar.bethistory') }}
	                            </span>
	                        </div>
	                        <div class="accordion-arrow">
	                            <i class="icon-icon-arrow-down"></i>
	                        </div>
	                    </div>
	                    <div class="accordion-content">
	                        <div class="accordion-content-inner">
	                            <div class="accordion-item section-tab" onclick="window.location.href='/bet_history'">
	                                <a href="/bet_history">
	                                	{{ __('app.profile.sidebar.bethistory') }}
	                                </a>
	                            </div>
	                        </div>
	                    </div>
	                </div>

	                <div class="accordion-container section messages">
	                    <div class="accordion-title">
	                        <div>
	                            <span class="icon-sb-messages"></span>
	                            <i class="notice "></i>
	                            <span class="span-title">{{ __('app.profile.sidebar.message') }}</span>&nbsp;
	                            <span class="unreadmsg">({{ $unreadMsg }})</span>
	                        </div>
	                        <div class="accordion-arrow">
	                            <i class="icon-icon-arrow-down"></i>
	                        </div>
	                    </div>
	                    <div class="accordion-content ">
	                        <div class="accordion-content-inner">
	                            <div class="accordion-item section-tab inbox" onclick="window.location.href='/message/inbox'">
	                                <a href="/message/inbox">
	                                	{{ __('app.profile.sidebar.message.inbox') }}
	                                	<span class="unreadmsg">({{ $unreadMsg }})</span>
	                                </a>
	                            </div>
	                            <div class="accordion-item section-tab sent-messages" onclick="window.location.href='/message/sent'">
	                                <a href="/message/sent">
	                                	{{ __('app.profile.sidebar.message.sent') }}
	                                </a>
	                            </div>
	                            <div class="accordion-item section-tab new-message" onclick="window.location.href='/message/new'">
	                                <a href="/message/new">
	                                	{{ __('app.profile.sidebar.message.new') }}
	                                </a>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>

	            <div class="sidebar-footer"></div>

			</div>	

			@yield('head')

			@yield('details')

		</div>
	</div>

	
</body>
</html>
