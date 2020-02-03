@extends('admin.layout.app')
@section('javascript_head')
    <script>
        $(document).ready(function() {
            $('#sortable .sortable-item').click(function() {

                var currentSortingType = $(this).find('span').attr('class');

                if (currentSortingType === undefined || currentSortingType.trim() === 'ic-arrow-down') {
                    $('input[name="sort"]').val('asc');
                    $('input[name="order"]').val($(this).attr('id'));
                } else {
                    $('input[name="sort"]').val('desc');
                    $('input[name="order"]').val($(this).attr('id'));
                }

                $('#search_form').submit();
            });
        });
    </script>
@endsection

@section('css_head')
    <link rel="stylesheet" href="{{ asset('asset/css/common.css') }}">
@endsection

@section('header_page')
    <div class="custom-header-page">
        <h3 class="box-title custom-title">運転代行アプリ　管理サイト</h3>
    </div>
@endsection

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box custom-box box-info" style="margin-bottom: 50px;">
                        <form action="{{ route('notify.index') }}" method="POST" id="search_form_request_user">
                            @csrf
                            <input type="hidden" id="search_page" name="page" val="">
                        </form>
                        <form class="form-horizontal" id="form-push" method="POST" action="{{ route('notify.store') }}">
                            @csrf
                            <div class="box-body">
                                <table class="col-md-12">
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            ユーザー名
                                        </th>
                                        <td>
                                            <div class="col-sm-12">
                                                <label class="col-sm-2">
                                                    <input type="radio" class="custom-table-margin" name="notify_type" value="0" checked>
                                                    全体
                                                </label>

                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="notify_type" value="2">
                                                    代行会社
                                                </label>

                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="notify_type" value="1">
                                                    ユーザー
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            タイトル
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control custom-table-margin" name="notify_title" >
                                                <p class="is-error custom-table-margin errors-title" style="display:none">タイトルとメッセージの両方を入力してください</p>
                                                @if ($errors->has('notify_title'))
                                                    <p class="is-error custom-table-margin"></p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            メッセージ
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <textarea name="notify_message" id="" rows="3" style="padding: 6px 12px;width: 100%; margin: 6px;resize: none;"></textarea>
                                                <p class="is-error custom-table-margin errors-message" style="display:none">タイトルとメッセージの両方を入力してください</p>
                                                @if ($errors->has('notify_message'))
                                                    <p class="is-error custom-table-margin">{{ $errors->first('notify_message') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header"></th>
                                        <td>
                                            <div class="col-sm-4">
                                                <button type="submit" id="button-modal" data-toggle="modal" data-target="#exampleModal" id="submit" class="btn-custom btn-default pull-left margin-5px">送信</button>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </form>
                    </div>

                    <div>
                        @if(session('success'))
                            <div class="custom-table-msg alert alert-success alert-dismissible fade in">
                                <a href="#" class="close" data-dismiss="alert" style="right:0" aria-label="close">&times;</a>
                                {{session('success')}}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="custom-table-msg alert alert-danger alert-dismissible fade in">
                                <a href="#" class="close" data-dismiss="alert" style="right:0" aria-label="close">&times;</a>
                                {{session('error')}}
                            </div>
                        @endif
                        {{-- @if(!$requestUserData["requestUserList"])
                            <div class="custom-table-msg">
                                表示するデータがありません
                            </div>
                        @endif --}}
                        <div class="box-header custom-table-box-header">
                            <h3 class="box-title">送信履歴</h3>
                        </div>
                        @if($results)
                            <div class="box-body">
                                <table id="sortable" class="table table-bordered table-striped table-hover" style="min-width: 900px">
                                    <thead>
                                    <tr style="background-color: #D3D3D3;">
                                        <th style="width: 15%;">送信日時</th>
                                        <th style="width: 15%;">送信先</th>
                                        <th style="width: 35%;">タイトル</th>
                                        <th style="width: 35%;">メッセージ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($results as $key => $result)
                                        <tr class="user">
                                            <td class="table-cut">{{$result->created_at}}</td>
                                            <td class="table-cut">
                                            @php
                                                $array_type = config('constants.TYPE_NOTIFY');
                                                $type = "";
                                                if(in_array($result->type,$array_type)){
                                                    switch ($result->type) {
                                                        case 1:
                                                            $type = "ユーザー";
                                                            break;
                                                        case 2:
                                                            $type = "代行会社";
                                                            break;
                                                        default:
                                                            $type = "全体";
                                                            break;
                                                    }
                                                }    
                                            @endphp
                                            {{$type}}
                                            </td>
                                            <td class="txt-left table-cut"><span class="cuttext{{$key}}" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $result->title ? $result->title : "" }}">{{$result->title}}</span></td>
                                            <td class="txt-left table-cut"><span class="cuttext-message{{$key}}" data-toggle="tooltip" data-placement="bottom" data-original-title="{{ $result->message ? $result->message : "" }}">{{$result->message}}</span></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="row custom-footer-list">
                            <div class="col-sm-9" style="float: right">
                                @include('admin.common.paging')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>入力内容でお知らせを送信します。お知らせは即時送信されます。よろしいですか？</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="submit-form-push" class="btn btn-primary">はい</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">いいえ</button>
            </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript_bottom')
    <script>
        function pageing(page) {
                var search_form_request_user = $('#search_form_request_user');
                search_form_request_user.attr('action', "{{ route('notify.index') }}");
                $('#search_page').val(page);
                resetDataOldFormSearch(window.objectUserDataSearchOld, search_form_request_user)
                search_form_request_user.submit();
            }
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip({
                'container':'body'
            });
            $("#button-modal").on("click",function($e){
                $e.preventDefault();
                var title = $("input[name=notify_title]").val();
                var message = $("textarea[name=notify_message]").val();
                if( title == "" || message == ""){
                    if(title == ""){
                        $(".errors-title").show();
                    }
                    else{
                        $(".errors-title").hide();
                    }
                    if(message == ""){
                        $(".errors-message").show();
                    }
                    else{
                        $(".errors-message").hide();
                    }
                }
                else {
                    $(".errors-title").hide();
                    $(".errors-message").hide();
                    $(".modal").modal();
                }
            });
            for( var i = 0; i < {{ $numberPerPage }} ; i++){
                if($(".cuttext"+i).html() && $(".cuttext"+i).html().length){
                    if ($(".cuttext"+i).html().length > 24) {
                    short_text = $(".cuttext"+i).html().substr(0, 24);
                    $(".cuttext"+i).html(short_text + "...");
                    }
                }
                if($(".cuttext-message"+i).html() && $(".cuttext-message"+i).html().length){
                    if ($(".cuttext-message"+i).html().length > 24) {
                    short_text = $(".cuttext-message"+i).html().substr(0, 24);
                    $(".cuttext-message"+i).html(short_text + "...");
                    }
                }
            }
            $("#submit-form-push").on("click",function(){
                $(this).attr('disabled','disabled');
                $("#form-push").submit();
            })
            
        })
    </script>
@endsection

@section('css_bottom')
@endsection
