<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>運転代行Yoboo</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="{{ asset('asset/js/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('asset/js/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{ asset('asset/js/bower_components/Ionicons/css/ionicons.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('asset/css/AdminLTE.min.css') }}">
    <!-- iCheck -->
{{--    <link rel="stylesheet" href="{{ asset('asset/js/plugins/iCheck/square/blue.css') }}">--}}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <style>
        .red-color {
            color: #dd4b39;
        }
        .custom-label {
            padding-bottom: 7px;
            text-align: center !important;
            font-weight: normal;
            background-color: #D9DAD9;
            color: #000;
        }
        .login-box {
            width: unset;
        }
        .box {
          width: 500px;
        }
        @media (max-width: 607px) {
            .box {
                margin-left: 0;
                width: calc(100% - 20px);
            }
        }
        .custom-header {
            border-bottom: 4px solid #f4f4f4;
            background-color: #0365CC;
            color: #ffffff;
            margin-left: 20px;
            margin-top: 5px;
            margin-right: 20px;
        }
        .custom-title {
            padding-left: 10px;
        }
        .custom-login {
            margin-top: 25px;
            margin-left: 20px;
        }
        .custom-content {
            background-color: #F0F0F0;
            min-height: 400px;
            margin-left: 20px;
            padding-top: 1px;
            margin-top: 20px;
            margin-right: 20px;
        }
        .custom-footer {
            border-top:0px;
        }
    </style>
</head>
<body class="hold-transition">
    <div class="box-header with-border custom-header">
        <h3 class="box-title custom-title">運転代行アプリ　管理サイト</h3>
    </div>
    <div class="custom-content">
        <div class="login-box custom-login row">
            <div class="login-box-body box box-info col-lg-6 col-lg-offset-4 col-md-5 col-md-offset-3 col-sm-2 col-sm-offset-2 col-xs-1 col-xs-offset-1">
                <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-4 control-label disabled color-palette custom-label">ログインID</label>

                            <div class="col-sm-8">
                                <input type="text" value="{{ old('_token') ? old('name') : "" }}" name="name" class="form-control" id="inputEmail3">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label disabled color-palette custom-label">パスワード</label>

                            <div class="col-sm-8">
                                <input type="password" name="password" value="{{ old('_token') ? old('password') : "" }}" class="form-control" id="inputPassword3">
                            </div>
                        </div>

                        @if ($errors->has('error_login'))
                            <div class="red-color">
                                {{ $errors->first('error_login') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <p class="red-color">
                            {{ session('error') }}
                            <p>
                        @endif

                        @if ($errors->has('name'))
                            <p class="red-color">{{ $errors->first('name') }}</p>
                        @endif

                        @if ($errors->has('password') && !$errors->has('name'))
                            <p class="red-color">{{ $errors->first('password') }}</p>
                        @endif
                    </div>
                    <div class="box-footer custom-footer">
                        <button style="font-weight: bold;" type="submit" class="btn btn-default pull-left">ログイン</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
