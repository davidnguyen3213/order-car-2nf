@extends('admin.layout.app')
@section('javascript_head')
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
                        <form class="form-horizontal" method="POST" action="{{ route('unregisteredCompany.index') }}" id="search_form">
                            @csrf
                            <input type="hidden" value="{{ isset($unregisteredCompanyData['searchValue']['sort']) ? $unregisteredCompanyData['searchValue']['sort'] : 'desc' }}" name="sort">
                            <input type="hidden" value="{{ isset($unregisteredCompanyData['searchValue']['order']) ? $unregisteredCompanyData['searchValue']['order'] : 'unregistered_companies.created_at' }}" name="order" id="order">
                            <div class="box-body">
                                <table class="col-md-12">
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header col-sm-3">
                                            依頼日
                                        </th>
                                        <td>
                                            <div class="col-sm-3">
                                                <input data-toggle="datepicker" autocomplete="off" placeholder="ｰｰｰｰ/ｰｰ/ｰｰ" class="form-control custom-table-margin custom-img" name="unregistered_company_start_date" id="start_date" value="{{$unregisteredCompanyData['searchValue']['unregistered_company_start_date'] != null ? $unregisteredCompanyData['searchValue']['unregistered_company_start_date'] : old('unregistered_company_start_date')}}">
                                            </div>
                                            <label style="width: 1%" class="col-sm-1 control-label custom-table-margin"> ~ </label>
                                            <div class="col-sm-3">
                                                <input data-toggle="datepicker" autocomplete="off" placeholder="ｰｰｰｰ/ｰｰ/ｰｰ" name="unregistered_company_end_date" id="end_date" class="form-control custom-table-margin custom-img" value="{{$unregisteredCompanyData['searchValue']['unregistered_company_end_date'] != null ? $unregisteredCompanyData['searchValue']['unregistered_company_end_date'] : old('unregistered_company_end_date')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            会社名
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control custom-table-margin" name="unregistered_company_name" id="unregistered_company_name" value="{{$unregisteredCompanyData['searchValue']['unregistered_company_name'] != null ? $unregisteredCompanyData['searchValue']['unregistered_company_name'] : old('unregistered_company_name')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            電話番号
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control custom-table-margin" name="unregistered_company_phone" id="unregistered_company_phone" value="{{$unregisteredCompanyData['searchValue']['unregistered_company_phone'] != null ? $unregisteredCompanyData['searchValue']['unregistered_company_phone'] : old('unregistered_company_phone')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            対応エリア
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" name="unregistered_company_corresponding_area" id="unregistered_company_corresponding_area" class="form-control custom-table-margin" value="{{$unregisteredCompanyData['searchValue']['unregistered_company_corresponding_area'] != null ? $unregisteredCompanyData['searchValue']['unregistered_company_corresponding_area'] : old('unregistered_company_corresponding_area')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header"></th>
                                        <td>
                                            <div class="col-sm-5">
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
                        @if(!$unregisteredCompanyData["unregisteredCompanyList"])
                            <div class="custom-table-msg">
                                表示するデータがありません
                            </div>
                        @endif
                        <div class="box-header custom-table-box-header">
                            <h3 class="box-title">電話帳管理一覧</h3>
                        </div>
                        @if($unregisteredCompanyData["unregisteredCompanyList"])
                            <div class="box-body">
                                <table id="sortable" class="table table-bordered table-striped table-hover" style="min-width: 1700px">
                                    <thead>
                                    <tr style="background-color: #D3D3D3;">
                                        <th style="width: 5%;">チェック</th>
                                        <th style="width: 7%;">登録日時</th>
                                        <th style="width: 4%;">表示順</th>
                                        <th style="width: 8%;">会社名</th>
                                        <th style="width: 8%;">電話番号</th>
                                        <th style="width: 20%;">所在地</th>
                                        <th style="width: 20%;">対応エリア</th>
                                        <th style="width: 15%;">基本料金</th>
                                        <th style="width: 3%;">カウント</th>
                                        <th style="width: 20%;">会社PR</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($unregisteredCompanyData["unregisteredCompanyList"] as $key => $unregisteredCompany)
                                        <tr class="unregistered-ompany" onclick="clickCheckbox({{$key}})">
                                            <td align="center">
                                                <label class="container-radio">
                                                    <input name="confirm_change" value="{{ $unregisteredCompany->id }}" type="checkbox" id="click-box-{{$key}}">
                                                    <span class="checkmark"></span>
                                                </label>
                                            </td>
                                            <td>{{ $unregisteredCompany->created_at ? date_format($unregisteredCompany->created_at, "Y/m/d H:i") : "" }}</td>
                                            <td>{{ isset($unregisteredCompany->display_order) ? $unregisteredCompany->display_order : "" }}</td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $unregisteredCompany->name ? $unregisteredCompany->name : "" }}">{{ $unregisteredCompany->name }}</span></td>
                                            <td>{{ $unregisteredCompany->phone ? $unregisteredCompany->phone : "" }}</td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $unregisteredCompany->address ? $unregisteredCompany->address : "" }}">{{ $unregisteredCompany->address }}</span></td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $unregisteredCompany->corresponding_area ? $unregisteredCompany->corresponding_area : "" }}">{{ $unregisteredCompany->corresponding_area }}</span></td>
                                            <td>{{ $unregisteredCompany->base_price ? $unregisteredCompany->base_price : "" }}</td>
                                            <td>{{ $unregisteredCompany->call_count_company ? $unregisteredCompany->call_count_company : "" }}</td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $unregisteredCompany->company_pr ? $unregisteredCompany->company_pr : "" }}">{{ $unregisteredCompany->company_pr }}</span></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="row custom-footer-list">
                            <div class="col-sm-3">
                                <button onclick="getIdunregisteredCompanyDelete()" class="btn-custom btn-default pull-left margin-5px">削除</button>
                                <button onclick="getIdunregisteredCompanyEdit()" class="btn-custom btn-default pull-left margin-5px">変更</button>
                            </div>
                            <div class="col-sm-9" style="float: right">
                                @php
                                    $page = $unregisteredCompanyData["page"];
                                @endphp
                                @include('admin.common.paging')
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="box-header custom-table-box-header">
                            <h3 class="box-title">電話帳管理更新</h3>
                        </div>
                        <div class="box-body">
                            <form class="form-horizontal" id="form-create-or-update" method="POST" action="{{ route('unregisteredCompany.store') }}">
                                @csrf
                                <input name="unregistered_company_store_id" value="{{old('_token') ? old('unregistered_company_store_id') : ""}}" type="hidden" id="unregistered_company_store_id">
                                <table class="col-md-8">
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create" style="width: 40%">
                                            登録日
                                        </th>
                                        <td>
                                            <div class="col-sm-8">
                                                <input readonly value="{{old('_token') ? old('unregistered_company_store_created_at') : date("Y/m/d")}}" id="unregistered_company_store_created_at" class="form-control custom-table-margin custom-img" name="unregistered_company_store_created_at">
                                                @if ($errors->has('unregistered_company_store_created_at'))
                                                    <p class="is-error">{{ $errors->first('unregistered_company_store_created_at') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            表示順
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('unregistered_company_store_display_order') : ""}}" class="form-control custom-table-margin" name="unregistered_company_store_display_order">
                                                @if ($errors->has('unregistered_company_store_display_order'))
                                                    <p class="is-error">{{ $errors->first('unregistered_company_store_display_order') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            会社名
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('unregistered_company_store_name') : ""}}" class="form-control custom-table-margin" name="unregistered_company_store_name">
                                                @if ($errors->has('unregistered_company_store_name'))
                                                    <p class="is-error">{{ $errors->first('unregistered_company_store_name') }}</p>
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
                                                <input type="text" value="{{old('_token') ? old('unregistered_company_store_phone') : ""}}" class="form-control custom-table-margin" name="unregistered_company_store_phone">
                                                @if ($errors->has('unregistered_company_store_phone'))
                                                    <p class="is-error">{{ $errors->first('unregistered_company_store_phone') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            所在地
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('unregistered_company_store_address') : ""}}" name="unregistered_company_store_address" class="form-control custom-table-margin">
                                                @if ($errors->has('unregistered_company_store_address'))
                                                    <p class="is-error">{{ $errors->first('unregistered_company_store_address') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create" style="padding-top: 10px;">
                                            対応エリア
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('unregistered_company_store_corresponding_area') : ""}}" name="unregistered_company_store_corresponding_area" class="form-control custom-table-margin">
                                                <p class="warning-text">※必ず<span class="color-red">都道府県+市区町村</span>で記載してください。<span class="color-red">複数設定する場合は「、」で区切って</span>ください。</p>
                                                @if ($errors->has('unregistered_company_store_corresponding_area'))
                                                    <p class="is-error">{{ $errors->first('unregistered_company_store_corresponding_area') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            基本料金
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('unregistered_company_store_base_price') : ""}}" name="unregistered_company_store_base_price" class="form-control custom-table-margin">
                                                @if ($errors->has('unregistered_company_store_base_price'))
                                                    <p class="is-error">{{ $errors->first('unregistered_company_store_base_price') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            カウント
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input readonly type="text" value="{{old('_token') ? old('unregistered_company_store_call_count') : ""}}" name="unregistered_company_store_call_count" class="form-control custom-table-margin">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            会社PR
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('unregistered_company_store_company_pr') : ""}}" name="unregistered_company_store_company_pr" class="form-control custom-table-margin">
                                                @if ($errors->has('unregistered_company_store_company_pr'))
                                                    <p class="is-error">{{ $errors->first('unregistered_company_store_company_pr') }}</p>
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
                    <button type="button" class="btn-custom btn-primary margin-5px" onclick="document.getElementById('deleteUnregisteredCompany').submit();">削除</button>
                    <button type="button" class="btn-custom btn-default margin-5px" data-dismiss="modal">戻る</button>
                    <form method="POST" id="deleteUnregisteredCompany">
                        @csrf
                    </form>
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
            resetDataOldFormSearch(window.objectUnregisterCompanyDataOld, search_form)
            search_form.submit();
        }
        function getIdunregisteredCompanyDelete() {
            if($('input[name=confirm_change]').is(':checked')) {
                var unregisteredCompanyId = $('input[name=confirm_change]:checked').val();
                var url = '{{ route("unregisteredCompany.delete", ":id") }}';
                url = url.replace(':id', unregisteredCompanyId);
                $('#deleteUnregisteredCompany').attr('action', url);
                $('#modal-default').modal('show');
            }
        }
        function getIdunregisteredCompanyEdit() {
            var userId = $('input[name=confirm_change]:checked').val();
            var url = '{{ route("unregisteredCompany.edit", ":id") }}';
            url = url.replace(':id', userId);
            $.ajax({
                type: 'GET',
                url: url,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
            }).done(function (data) {
                $('.is-error').empty();
                var date = new Date(data.unregisteredCompanyEditData.unregisteredCompany.created_at);
                var day = ("0" + date.getDate()).slice(-2);
                var month = ("0" + (date.getMonth() + 1)).slice(-2);
                $('#unregistered_company_store_created_at').val(date.getFullYear()+"/"+(month)+"/"+(day));
                $('input[name=unregistered_company_store_display_order]').val(data.unregisteredCompanyEditData.unregisteredCompany.display_order);
                $('input[name=unregistered_company_store_name]').val(data.unregisteredCompanyEditData.unregisteredCompany.name);
                $('input[name=unregistered_company_store_phone]').val(data.unregisteredCompanyEditData.unregisteredCompany.phone);
                $('input[name=unregistered_company_store_address]').val(data.unregisteredCompanyEditData.unregisteredCompany.address);
                $('input[name=unregistered_company_store_corresponding_area]').val(data.unregisteredCompanyEditData.unregisteredCompany.corresponding_area);
                $('input[name=unregistered_company_store_base_price]').val(data.unregisteredCompanyEditData.unregisteredCompany.base_price);
                $('input[name=unregistered_company_store_company_pr]').val(data.unregisteredCompanyEditData.unregisteredCompany.company_pr);
                $('input[name=unregistered_company_store_id]').val(data.unregisteredCompanyEditData.unregisteredCompany.id);
                $('input[name=unregistered_company_store_call_count]').val(data.unregisteredCompanyEditData.unregisteredCompany.call_count_company);
            });
        }

        if ($('.is-error').length > 0) {
            $('html, body').animate({
                scrollTop: ($('#form-create-or-update').offset().top - 300)
            }, 1000);
        }

        $(document).ready(function() {
            window.objectUnregisterCompanyDataOld = JSON.parse('<?php echo json_encode(isset($unregisteredCompanyData['searchValue']) ? $unregisteredCompanyData['searchValue'] : []); ?>');

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