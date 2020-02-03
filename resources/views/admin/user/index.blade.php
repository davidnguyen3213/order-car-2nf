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
                        <form class="form-horizontal" method="POST" action="{{ route('user.index') }}" id="search_form">
                            @csrf
                            <input type="hidden" value="{{ isset($userData['searchValue']['sort']) ? $userData['searchValue']['sort'] : 'desc' }}" name="sort">
                            <input type="hidden" value="{{ isset($userData['searchValue']['order']) ? $userData['searchValue']['order'] : 'created_at' }}" name="order" id="order">
                            <div class="box-body">
                                <table class="col-md-12">
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header col-sm-3">
                                            登録日
                                        </th>
                                        <td>
                                            <div class="col-sm-3">
                                                <input data-toggle="datepicker" autocomplete="off" placeholder="ｰｰｰｰ/ｰｰ/ｰｰ" name="user_start_date" class="form-control custom-table-margin custom-img" id="start_date" value="{{$userData['searchValue']['user_start_date'] != null ? $userData['searchValue']['user_start_date'] : old('user_start_date')}}">
                                            </div>

                                            <label style="width: 1%" class="col-sm-1 control-label custom-table-margin"> ~ </label>

                                            <div class="col-sm-3">
                                                <input data-toggle="datepicker" autocomplete="off" placeholder="ｰｰｰｰ/ｰｰ/ｰｰ" name="user_end_date" id="end_date" class="form-control custom-table-margin custom-img" value="{{$userData['searchValue']['user_end_date'] != null ? $userData['searchValue']['user_end_date'] : old('user_end_date')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            レコードステータス
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <label class="col-sm-2">
                                                    @php $user_status_is_enabled = $userData['searchValue']['user_status'];@endphp
                                                    <input type="radio" class="custom-table-margin" name="user_status" id="has_status_all" value="" @if($user_status_is_enabled==null || old('user_status') == null) checked @endif>
                                                    全て
                                                </label>

                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="user_status" id="has_status_true" value="1" @if($user_status_is_enabled==1 || old('user_status') == 1) checked @endif>
                                                    有効
                                                </label>

                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="user_status" id="has_status_false" value="0" @if($user_status_is_enabled==0 && is_numeric($user_status_is_enabled) || (old('user_status') == 0 && is_numeric(old('user_status')))) checked @endif>
                                                    無効
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            ユーザー名
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control custom-table-margin" name="user_name" id="user_name" value="{{$userData['searchValue']['user_name'] != null ? $userData['searchValue']['user_name'] : old('user_name')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            電話番号
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" name="user_phone" id="user_phone" class="form-control custom-table-margin" value="{{$userData['searchValue']['user_phone'] != null ? $userData['searchValue']['user_phone'] : old('user_phone')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header"></th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input name="page" type="hidden" value="1" id="search_page">
                                                <button type="submit" class="btn-custom btn-default pull-left margin-5px">検索</button>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </form>
                    </div>

                    <div class="custom-table-box">
                        @if(session('success'))
                            <div class="custom-table-msg">
                                {{session('success')}}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="custom-table-msg">
                                {{session('error')}}
                            </div>
                        @endif
                        @if(!$userData["userList"])
                            <div class="custom-table-msg">
                                表示するデータがありません
                            </div>
                        @endif
                        <div class="box-header custom-table-box-header">
                            <h3 class="box-title">会員管理一覧</h3>
                        </div>
                        @if($userData["userList"])
                            <div class="box-body">
                                <table id="sortable" class="col-md-12 table table-bordered table-striped table-hover" style="min-width: 1400px">
                                    <thead>
                                        <tr style="background-color: #D3D3D3;">
                                            <th style="width: 3%;">チェック</th>
                                            <th style="width: 10%;">登録日時</th>
                                            <th style="width: 10%;">レコードステータス</th>
                                            <th style="width: 20%;">ユーザー名</th>
                                            <th style="width: 20%;">パスワード</th>
                                            <th style="width: 10%;">電話番号</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($userData["userList"] as $key => $user)
                                            <tr class="user" onclick="clickCheckbox({{$key}})">
                                                <td align="center">
                                                    <label class="container-radio">
                                                        <input name="confirm_change" value="{{ $user->id }}" type="checkbox" id="click-box-{{$key}}">
                                                        <span class="checkmark"></span>
                                                    </label>
                                                </td>
                                                <td>{{ $user->created_at ? date_format($user->created_at, "Y/m/d H:i") : "" }}</td>
                                                <td>{{ $user->status == 1 ? '有効' : '無効' }}</td>
                                                <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $user->name ? $user->name : "" }}">{{ $user->name }}</span></td>
                                                <td>{{ $user->raw_pass ? $user->raw_pass : "" }}</td>
                                                <td>{{ $user->phone ? $user->phone : "" }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="row custom-footer-list">
                            <div class="col-sm-3">
                                <button onclick="getIdUserDelete()" class="btn-custom btn-default pull-left margin-5px">削除</button>
                                <button onclick="getIdUserEdit()" class="btn-custom btn-default pull-left margin-5px">変更</button>
                            </div>
                            <div class="col-sm-9" style="float: right">
                                @php
                                    $page = $userData["page"];
                                @endphp
                                @include('admin.common.paging')
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="box-header custom-table-box-header">
                            <h3 class="box-title">会員管理更新</h3>
                        </div>
                        <div class="box-body">
                            <form class="form-horizontal" id="form-create-or-update" method="POST" action="{{ route('user.store') }}">
                                @csrf
                                <input name="user_store_id" value="{{old('_token') ? old('user_store_id') : ""}}" type="hidden" id="user_store_id">
                                <table class="col-md-8">
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            登録日
                                        </th>
                                        <td>
                                            <div class="col-sm-8">
                                                <input readonly value="{{old('_token') ? old('user_store_created_at') : date("Y/m/d")}}" id="user_store_created_at" class="form-control custom-table-margin custom-img" name="user_store_created_at">
                                                @if ($errors->has('user_store_created_at'))
                                                    <p class="is-error">{{ $errors->first('user_store_created_at') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            レコードステータス
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <label class="col-sm-4">
                                                    <input class="custom-table-margin" value="1" id="user_store_status_1" type="radio" name="user_store_status" @if(!old('_token') || old('user_store_status') == 1) checked @endif>
                                                    有効
                                                </label>

                                                <label class="col-sm-4">
                                                    <input class="custom-table-margin" type="radio" id="user_store_status_0" name="user_store_status" value="0" @if(old('_token') && old('user_store_status') == 0) checked @endif>
                                                    無効
                                                </label>
                                                @if ($errors->has('user_store_status'))
                                                    <p class="is-error">{{ $errors->first('user_store_status') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            ユーザー名
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('user_store_name') : ""}}" class="form-control custom-table-margin" name="user_store_name">
                                                @if ($errors->has('user_store_name'))
                                                    <p class="is-error">{{ $errors->first('user_store_name') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            パスワード
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('user_store_password') : ""}}" class="form-control custom-table-margin" name="user_store_password">
                                                @if ($errors->has('user_store_password'))
                                                    <p class="is-error">{{ $errors->first('user_store_password') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            電話番号
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('user_store_phone') : ""}}" name="user_store_phone" class="form-control custom-table-margin">
                                                @if ($errors->has('user_store_phone'))
                                                    <p class="is-error">{{ $errors->first('user_store_phone') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create"></th>
                                        <td>
                                            <div class="col-sm-8">
                                                <button type="submit" class="btn-custom btn-default pull-left margin-5px">更新</button>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="modal-default">
        <div class="modal-dialog">
            <div class="modal-content" style="width:500px; margin: 0 auto;">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h3>削除します。よろしいですか？</h3>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-custom btn-primary margin-5px" onclick="document.getElementById('deleteUser').submit();">削除</button>
                    <button type="button" class="btn-custom btn-default margin-5px" data-dismiss="modal">戻る</button>
                    <form method="POST" id="deleteUser">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-request">
        <div class="modal-dialog">
            <div class="modal-content" style="width:500px; margin: 0 auto;">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h3>全て必須です</h3>
                </div>
                <div class="modal-footer">
                    <button onclick="scrollTop()" type="button" class="btn-custom btn-default" data-dismiss="modal">戻る</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript_bottom')
    <script>
        function pageing(page) {
            $('#search_page').val(page);
            var search_form = $("#search_form")
            resetDataOldFormSearch(window.objectUserDataOld, search_form)
            search_form.submit();
        }

        function getIdUserDelete() {
            if($('input[name=confirm_change]').is(':checked')) {
                var userId = $('input[name=confirm_change]:checked').val();
                var url = '{{ route("user.delete", ":id") }}';
                url = url.replace(':id', userId);

                $('#deleteUser').attr('action', url);
                $('#modal-default').modal('show');
            }
        }

        function getIdUserEdit() {

            var userId = $('input[name=confirm_change]:checked').val();
            var url = '{{ route("user.edit", ":id") }}';
            url = url.replace(':id', userId);

            $.ajax({
                type: 'GET',
                url: url,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
            }).done(function (data) {
                console.log(data.userEditData.user);
                $('.is-error').empty();
                var date = new Date(data.userEditData.user.created_at);
                var day = ("0" + date.getDate()).slice(-2);
                var month = ("0" + (date.getMonth() + 1)).slice(-2);
                $('#user_store_created_at').val(date.getFullYear()+"/"+(month)+"/"+(day));

                $('input[name=user_store_name]').val(data.userEditData.user.name);
                $('input[name=user_store_phone]').val(data.userEditData.user.phone);
                $('input[name=user_store_id]').val(data.userEditData.user.id);
                $('input[name=user_store_password]').val(data.userEditData.user.raw_pass);

                if (data.userEditData.user.status == 1) {
                    $('#user_store_status_1').prop('checked', true);
                    $('#user_store_status_0').prop('checked', false);
                } else if (data.userEditData.user.status == 0) {
                    $('#user_store_status_1').prop('checked', false);
                    $('#user_store_status_0').prop('checked', true);
                }
            });
        }

        if ($('.is-error').length > 0 && ($('input[name=user_store_name]').val().length === 0 || $('input[name=user_store_phone]').val().length === 0 || $('input[name=user_store_password]').val().length === 0)) {
            $('#modal-request').modal('show');
        }

        if ($('.is-error').length > 0) {
            $('html, body').animate({
                scrollTop: ($('#form-create-or-update').offset().top - 300)
            }, 1000);
        }

        $(document).ready(function() {
            window.objectUserDataOld = JSON.parse('<?php echo json_encode(isset($userData['searchValue']) ? $userData['searchValue'] : []); ?>');

            $('[data-toggle="tooltip"]').tooltip({
                'container':'body'
            });

            $.datepicker.setDefaults( $.datepicker.regional[ "ja" ] );

            $('input[data-toggle="datepicker"]').datepicker({
                dateFormat: 'yy/mm/dd',
            });
        });
    </script>
@endsection

@section('css_bottom')
@endsection
