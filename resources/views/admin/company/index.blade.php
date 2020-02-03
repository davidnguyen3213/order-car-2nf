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
                        <form class="form-horizontal" method="POST" action="{{ route('company.index') }}" id="search_form">
                            @csrf
                            <input type="hidden" value="{{ isset($companyData['searchValue']['sort']) ? $companyData['searchValue']['sort'] : 'desc' }}" name="sort">
                            <input type="hidden" value="{{ isset($companyData['searchValue']['order']) ? $companyData['searchValue']['order'] : 'created_at' }}" name="order" id="order">
                            <div class="box-body">
                                <table class="col-md-12">
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header col-sm-3">
                                            登録日
                                        </th>
                                        <td>
                                            <div class="col-sm-3">
                                                <input data-toggle="datepicker" autocomplete="off" placeholder="ｰｰｰｰ/ｰｰ/ｰｰ" class="form-control custom-table-margin custom-img" name="company_start_date" id="start_date" value="{{$companyData['searchValue']['company_start_date'] != null ? $companyData['searchValue']['company_start_date'] : old('company_start_date')}}">
                                            </div>

                                            <label style="width: 1%" class="col-sm-1 control-label custom-table-margin"> ~ </label>

                                            <div class="col-sm-3">
                                                <input data-toggle="datepicker" autocomplete="off" placeholder="ｰｰｰｰ/ｰｰ/ｰｰ" name="company_end_date" id="end_date" class="form-control custom-table-margin custom-img" value="{{$companyData['searchValue']['company_end_date'] != null ? $companyData['searchValue']['company_end_date'] : old('company_end_date')}}">
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
                                                    @php $company_status_is_enabled = $companyData['searchValue']['company_status_login'];@endphp
                                                    <input type="radio" class="custom-table-margin" name="company_status_login" id="has_status_all" value="" @if($company_status_is_enabled==null || old('company_status_login') == null) checked @endif>
                                                    全て
                                                </label>

                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="company_status_login" id="has_status_true" value="1" @if($company_status_is_enabled==1 || old('company_status_login') == 1) checked @endif>
                                                    有効
                                                </label>

                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="company_status_login" id="has_status_false" value="0" @if($company_status_is_enabled==0 && is_numeric($company_status_is_enabled) || (old('company_status_login') == 0 && is_numeric(old('company_status_login')))) checked @endif>
                                                    無効
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            会社名
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control custom-table-margin" name="company_name" id="company_name" value="{{$companyData['searchValue']['company_name'] != null ? $companyData['searchValue']['company_name'] : old('company_name')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            電話番号
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" name="company_phone" id="company_phone" class="form-control custom-table-margin" value="{{$companyData['searchValue']['company_phone'] != null ? $companyData['searchValue']['company_phone'] : old('company_phone')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            対応エリア
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" name="company_corresponding_area" id="company_corresponding_area" class="form-control custom-table-margin" value="{{$companyData['searchValue']['company_corresponding_area'] != null ? $companyData['searchValue']['company_corresponding_area'] : old('company_corresponding_area')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header"></th>
                                        <td>
                                            <div class="col-sm-4">
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
                        @if(!$companyData["companyList"])
                            <div class="custom-table-msg">
                                表示するデータがありません
                            </div>
                        @endif
                        <div class="box-header custom-table-box-header">
                            <h3 class="box-title">代行会社管理一覧</h3>
                        </div>
                        @if($companyData["companyList"])
                            <div class="box-body">
                                <table id="sortable" class="table table-bordered table-striped table-hover" style="min-width: 1800px">
                                    <thead>
                                    <tr style="background-color: #D3D3D3;">
                                        <th style="width: 2%;">チェック</th>
                                        <th style="width: 7%;">登録日時</th>
                                        <th style="width: 5%;">レコードステータス</th>
                                        <th style="width: 10%;">会社名</th>
                                        <th style="width: 10%;">担当者名</th>
                                        <th style="width: 10%;">メールアドレス</th>
                                        <th style="width: 10%;">パスワード</th>
                                        <th style="width: 10%;">電話番号</th>
                                        <th style="width: 10%;">所在地</th>
                                        <th style="width: 10%;">対応エリア</th>
                                        <th style="width: 10%;">基本料金</th>
                                        <th style="width: 10%;">会社PR</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($companyData["companyList"] as $key => $company)
                                        <tr class="user" onclick="clickCheckbox({{$key}})">
                                            <td align="center">
                                                <label class="container-radio">
                                                    <input name="confirm_change" value="{{ $company->id }}" type="checkbox" id="click-box-{{$key}}">
                                                    <span class="checkmark"></span>
                                                </label>
                                            </td>
                                            <td>{{ $company->created_at ? date_format($company->created_at, "Y/m/d H:i") : "" }}</td>
                                            <td>{{ $company->status_login == 1 ? '有効' : '無効' }}</td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $company->name ? $company->name : "" }}">{{ $company->name }}</span></td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $company->person_charged ? $company->person_charged : "" }}">{{ $company->person_charged }}</span></td>
                                            <td>{{ $company->email ? $company->email : "" }}</td>
                                            <td>{{ $company->raw_pass ? $company->raw_pass : "" }}</td>
                                            <td>{{ $company->phone ? $company->phone : "" }}</td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $company->address ? $company->address : "" }}">{{ $company->address }}</span></td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $company->corresponding_area ? $company->corresponding_area : "" }}">{{ $company->corresponding_area }}</span></td>
                                            <td>{{ $company->base_price ? $company->base_price : "" }}</td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ ($company->company_pr ? $company->company_pr : "") }}">{{ $company->company_pr }}</span></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="row custom-footer-list">
                            <div class="col-sm-3">
                                <button onclick="getIdCompanyDelete()" class="btn-custom btn-default pull-left margin-5px">削除</button>
                                <button onclick="getIdCompanyEdit()" class="btn-custom btn-default pull-left margin-5px">変更</button>
                            </div>
                            <div class="col-sm-9" style="float: right">
                                @php
                                    $page = $companyData["page"];
                                @endphp
                                @include('admin.common.paging')
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="box-header custom-table-box-header">
                            <h3 class="box-title">代行会社管理更新</h3>
                        </div>
                        <div class="box-body">
                            <form class="form-horizontal" id="form-create-or-update" method="POST" action="{{ route('company.store') }}">
                                @csrf
                                <input name="company_store_id" value="{{old('_token') ? old('company_store_id') : ""}}" type="hidden" id="company_store_id">
                                <table class="col-md-8">
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create" style="width: 40%">
                                            登録日
                                        </th>
                                        <td>
                                            <div class="col-sm-8">
                                                <input readonly value="{{old('_token') ? old('company_store_created_at') : date("Y/m/d")}}" id="company_store_created_at" class="form-control custom-table-margin custom-img" name="company_store_created_at">
                                                @if ($errors->has('company_store_created_at'))
                                                    <p class="is-error">{{ $errors->first('company_store_created_at') }}</p>
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
                                                    <input class="custom-table-margin" value="1" id="company_store_status_login_1" type="radio" name="company_store_status_login" @if(!old('_token') || old('company_store_status_login') == 1) checked @endif>
                                                    有効
                                                </label>

                                                <label class="col-sm-4">
                                                    <input class="custom-table-margin" type="radio" id="company_store_status_login_0" name="company_store_status_login" value="0" @if(old('_token') && old('company_store_status_login') == 0) checked @endif>
                                                    無効
                                                </label>
                                                @if ($errors->has('company_store_status_login'))
                                                    <p class="is-error">{{ $errors->first('company_store_status_login') }}</p>
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
                                                <input type="text" value="{{old('_token') ? old('company_store_name') : ""}}" class="form-control custom-table-margin" name="company_store_name">
                                                @if ($errors->has('company_store_name'))
                                                    <p class="is-error">{{ $errors->first('company_store_name') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            担当者名
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('company_store_person_charged') : ""}}" class="form-control custom-table-margin" name="company_store_person_charged">
                                                @if ($errors->has('company_store_person_charged'))
                                                    <p class="is-error">{{ $errors->first('company_store_person_charged') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            メールアドレス
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('company_store_email') : ""}}" class="form-control custom-table-margin" name="company_store_email">
                                                @if ($errors->has('company_store_email'))
                                                    <p class="is-error">{{ $errors->first('company_store_email') }}</p>
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
                                                <input type="text" value="{{old('_token') ? old('company_store_password') : ""}}" class="form-control custom-table-margin" name="company_store_password">
                                                @if ($errors->has('company_store_password'))
                                                    <p class="is-error">{{ $errors->first('company_store_password') }}</p>
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
                                                <input type="text" value="{{old('_token') ? old('company_store_phone') : ""}}" name="company_store_phone" class="form-control custom-table-margin">
                                                @if ($errors->has('company_store_phone'))
                                                    <p class="is-error">{{ $errors->first('company_store_phone') }}</p>
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
                                                <input type="text" value="{{old('_token') ? old('company_store_address') : ""}}" name="company_store_address" class="form-control custom-table-margin">
                                                @if ($errors->has('company_store_address'))
                                                    <p class="is-error">{{ $errors->first('company_store_address') }}</p>
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
                                                <input type="text" value="{{old('_token') ? old('company_store_corresponding_area') : ""}}" name="company_store_corresponding_area" class="form-control custom-table-margin">
                                                <p class="warning-text">※必ず<span class="color-red">都道府県+市区町村</span>で記載してください。<span class="color-red">複数設定する場合は「、」で区切って</span>ください。</p>
                                                @if ($errors->has('company_store_corresponding_area'))
                                                    <p class="is-error">{{ $errors->first('company_store_corresponding_area') }}</p>
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
                                                <input type="text" value="{{old('_token') ? old('company_store_base_price') : ""}}" name="company_store_base_price" class="form-control custom-table-margin">
                                                @if ($errors->has('company_store_base_price'))
                                                    <p class="is-error">{{ $errors->first('company_store_base_price') }}</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header-create">
                                            会社PR
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <input type="text" value="{{old('_token') ? old('company_store_company_pr') : ""}}" name="company_store_company_pr" class="form-control custom-table-margin">
                                                @if ($errors->has('company_store_company_pr'))
                                                    <p class="is-error">{{ $errors->first('company_store_company_pr') }}</p>
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
                    <button type="button" class="btn-custom btn-primary margin-5px" onclick="document.getElementById('deleteCompany').submit();">削除</button>
                    <button type="button" class="btn-custom btn-default margin-5px" data-dismiss="modal">戻る</button>
                    <form method="POST" id="deleteCompany">
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
            resetDataOldFormSearch(window.objectCompanyDataOld, search_form)
            search_form.submit();
        }

        function getIdCompanyDelete() {
            if($('input[name=confirm_change]').is(':checked')) {
                var companyId = $('input[name=confirm_change]:checked').val();
                var url = '{{ route("company.delete", ":id") }}';
                url = url.replace(':id', companyId);

                $('#deleteCompany').attr('action', url);
                $('#modal-default').modal('show');
            }
        }

        function getIdCompanyEdit() {

            var companyId = $('input[name=confirm_change]:checked').val();
            var url = '{{ route("company.edit", ":id") }}';
            url = url.replace(':id', companyId);

            $.ajax({
                type: 'GET',
                url: url,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
            }).done(function (data) {
                console.log(data.companyEditData.company);
                $('.is-error').empty();
                var date = new Date(data.companyEditData.company.created_at);
                var day = ("0" + date.getDate()).slice(-2);
                var month = ("0" + (date.getMonth() + 1)).slice(-2);
                $('#company_store_created_at').val(date.getFullYear()+"/"+(month)+"/"+(day));

                $('input[name=company_store_name]').val(data.companyEditData.company.name);
                $('input[name=company_store_person_charged]').val(data.companyEditData.company.person_charged);
                $('input[name=company_store_email]').val(data.companyEditData.company.email);
                $('input[name=company_store_phone]').val(data.companyEditData.company.phone);
                $('input[name=company_store_address]').val(data.companyEditData.company.address);
                $('input[name=company_store_corresponding_area]').val(data.companyEditData.company.corresponding_area);
                $('input[name=company_store_base_price]').val(data.companyEditData.company.base_price);
                $('input[name=company_store_company_pr]').val(data.companyEditData.company.company_pr);
                $('input[name=company_store_id]').val(data.companyEditData.company.id);
                $('input[name=company_store_password]').val(data.companyEditData.company.raw_pass);

                if (data.companyEditData.company.status_login == 1) {
                    $('#company_store_status_login_1').prop('checked', true);
                    $('#company_store_status_login_0').prop('checked', false);
                } else if (data.companyEditData.company.status_login == 0) {
                    $('#company_store_status_login_1').prop('checked', false);
                    $('#company_store_status_login_0').prop('checked', true);
                }
            });
        }

        if ($('.is-error').length > 0) {
            $('html, body').animate({
                scrollTop: ($('#form-create-or-update').offset().top - 300)
            }, 1000);
        }

        $(document).ready(function() {
            window.objectCompanyDataOld = JSON.parse('<?php echo json_encode(isset($companyData['searchValue']) ? $companyData['searchValue'] : []); ?>');

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
