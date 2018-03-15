<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>@yield('title') - {{$app_name}}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <meta name="admin-baseurl" content="{{ url('/') }}">
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
    <script src="{{ mix('/js/app.js') }}"></script>
  </head>

<body>
	<div class="admin-page" id="app">
		<div class="admin-header">

			<div class="admin-header-title" style="flex: 50rem 0;font-size: 18px;">
				<img class="appbanner" src="{{ url('/appbanner.jpeg') }}" />
                {{$app_name}}
			</div>

			@if (Auth::guest())
			<div class="admin-header-content">
				<div>
					<a href="{{ route('login') }}" type="button" class="btn btn-default external">登陆系统</a>
				</div>
			</div>
			@else

		  @if (isset($searchbar) and $searchbar)
		  	<searchbar :items="searchbar"></searchbar>
	      @endif

			<div class="admin-header-content">

                <!-- <appsel :appinfo_url="appinfo_url"></appsel> -->

                <span class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        {{ Auth::user()->name }} <span class="caret"></span>
                    </a>  
                    <ul class="dropdown-menu-right dropdown-menu" role="menu">
                        <li>
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();">
                                退出系统
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </li>
                    </ul>
                </span>
			</div>
			@endif

		</div>

    	<div class="admin-main">
	    	<div class="admin-sidebar">
	    		<appmenu :menus="menus"></appmenu>
			</div>

			<div class="main-content">
				<div class="main-header">
					<div class="main-header-basic">
                        @if (array_key_exists('navpath', View::getSections()))
                        @yield('navpath')
                        @else
                        <ol class="breadcrumb">
                            <li class="active">@yield('title')</li>
                        </ol>          
                        @endif
						<div class="main-header-action">
							@yield('action-bar')
						</div>
					</div>
					@if (array_key_exists('header', View::getSections()))
					<div class="main-header-custom">@yield('header')</div>
					@endif
				</div>

				<div class="main-body">@yield('content')</div>
				@if (array_key_exists('footer', View::getSections()))
				<div class="main-footer">@yield('footer')</div>
				@endif
			</div>
	    </div>

    </div>

    <div class="main-script">
    	@yield('scripts')
    </div>

    <div id="indicator" style="display: none">
    	<div class="indicator-container">
    		<div class="indicator-process"></div>
    	</div>
    </div>
    </body>

    <script>
    @if (isset($searchbar) and $searchbar)
    window.searchbar = {!! json_encode($searchbar) !!};
    @else
    window.searchbar = [];
    @endif;

    window.appinfo_url = "{{ route('admin-site-menus') }}";
    window.menus = {!! json_encode($app_menus) !!};

    $(document).click(function(e){
        if($(e.target).hasClass('ajax-btn')){
            e.preventDefault();

            var btn = $(e.target);
            var form = btn.parents('form.ajax-form');
            $.ajax({
                url: form.prop('action'),
                type:form.prop('method'),
                data: form.serialize(),
                success: function(res){
                    if(res.msg){
                        alert(res.msg);
                    }

                    if(res.redirect){
                        location.href = res.redirect;
                    }
                }
            });
        }
    });
    </script>
</html>