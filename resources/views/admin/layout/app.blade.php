<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>運転代行Yoboo</title>
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('asset/js/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/js/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/_all-skins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/css/jquery-ui.css') }}">

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <!-- Scripts -->
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <!-- jQuery 3 -->
    <script src="{{ asset('asset/js/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('asset/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('asset/js/jquery-ui-i18n.js') }}"></script>

    <!-- Bootstrap 3.3.7 -->
    <script src="{{ asset('asset/js/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>

    @yield('javascript_head')
    @yield('css_head')

    <style>
        .custom-main-sidebar {
            background-color: #F0F0F0 !important;
            width: 200px;
        }
        .skin-blue .sidebar a {
            color: #333;
            font-weight: bold;
        }
        .content-wrapper {
            background-color: #ffffff;
            z-index: 800;
            margin-left: 200px;
            margin-right: 15px;
        }
        .progress-bar-blue {
            background-color: #0365CC;
        }
        .active p a span{
            text-decoration: none;
            color: red !important;
        }
        .custom-wrapper {
            min-height: 100%;
            position: relative;
        }
        .custom-wrapper:before, .custom-wrapper:after {
            content: " ";
            display: table;
        }
        .custom-icon-sidebar {
            top: 13px;
            position: absolute;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
    @yield('header_page')
    <div class="custom-wrapper">
        <aside class="custom-main-sidebar main-sidebar" style="padding-top: 0px;">
            <section class="sidebar">
                <ul>
                    <li class="parent-nav">
                        <p><a href="#"><i class="square"></i> <span class="custom-icon-sidebar">代行アプリ</span></a></p>
                        <ul class="tree_sidebar_hover">
                            <li @php if(in_array(Route::currentRouteName(),['user.index'])) echo ' class="active"'; @endphp>
                                <p><a href="{{ route('user.index') }}"><i class="square"></i> <span class="custom-icon-sidebar">会員管理</span></a></p>
                            </li>
                            <li @php if(in_array(Route::currentRouteName(),['company.index'])) echo ' class="active"'; @endphp>
                                <p><a href="{{ route('company.index') }}"><i class="square"></i> <span class="custom-icon-sidebar">代行会社管理</span></a></p>
                            </li>
                            <li @php if(in_array(Route::currentRouteName(),['requestuser.index'])) echo ' class="active"'; @endphp>
                                <p><a href="{{ route('requestuser.index') }}"><i class="square"></i> <span class="custom-icon-sidebar">代行依頼管理</span></a></p>
                            </li>
                            <li @php if(in_array(Route::currentRouteName(),['unregisteredCompany.index'])) echo ' class="active"'; @endphp>
                                <p><a href="{{ route('unregisteredCompany.index') }}"><i class="fa square"></i> <span class="custom-icon-sidebar">電話帳</span></a></p>
                            </li>
                            <li @php if(in_array(Route::currentRouteName(),['notify.index'])) echo ' class="active"'; @endphp>
                                <p><a href="{{ route('notify.index') }}"><i class="fa square"></i> <span class="custom-icon-sidebar">お知らせ</span></a></p>
                            </li>
                        </ul>
                    </li>
                </ul>
            </section>
        </aside>
        <style>
            .sidebar ul, .sidebar li { list-style: none; margin: 0; padding: 0; }
            .sidebar ul{ padding-left: 1em; }
            .sidebar li ul li { padding-left: 1em;
                border: 1px dashed black;
                border-width: 0 0 3px 3px;
            }

            li.parent-nav { border-bottom: 0px; }

            .sidebar li p { margin: 0;
                background: #F0F0F0 !important;
                position: relative;
                top: 0.5em;
                height: 25px;
                padding: 15px 17px;

            }

            .tree_sidebar_hover a span:hover {
                text-decoration: none;
                color: red;
            }

            li.parent-nav ul li{
                margin-left: 6px;
                padding-left: 20px;
            }

            .sidebar li ul li:last-child ul {
                border-left: 1px solid white;
                margin-left: -17px;
            }
        </style>
        @yield('content')

        {{--<footer class="main-footer">--}}
            {{--<div class="pull-right hidden-xs">--}}
                {{--<b>Version</b> 1.0--}}
            {{--</div>--}}
            {{--<strong>Copyright &copy; 2019-2020 <a href="#">運転代行Yoboo</a>.</strong>--}}
        {{--</footer>--}}
        <script src="{{ asset('/js/common.js') }}"></script>
        <script src="{{ asset('/js/scroll.js') }}"></script>
    </div>
@yield('javascript_bottom')
@yield('css_bottom')
</body>
</html>