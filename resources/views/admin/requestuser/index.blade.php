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
                        <form class="form-horizontal" method="POST" action="{{ route('requestuser.index') }}" id="search_form_request_user">
                            @csrf
                            <input type="hidden" value="{{ isset($requestUserData['searchValue']['sort']) ? $requestUserData['searchValue']['sort'] : 'desc' }}" name="sort">
                            <input type="hidden" value="{{ isset($requestUserData['searchValue']['order']) ? $requestUserData['searchValue']['order'] : 'created_at' }}" name="order" id="order">
                            <div class="box-body">
                                <table class="col-md-12">
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header col-sm-3">
                                            依頼日
                                        </th>
                                        <td>
                                            <div class="col-sm-3">
                                                <input data-toggle="datepicker" autocomplete="off" placeholder="ｰｰｰｰ/ｰｰ/ｰｰ" class="form-control custom-table-margin custom-img" name="request_user_start_date" id="start_date" value="{{$requestUserData['searchValue']['request_user_start_date'] != null ? $requestUserData['searchValue']['request_user_start_date'] : old('request_user_start_date')}}">
                                            </div>

                                            <label style="width: 1%" class="col-sm-1 control-label custom-table-margin"> ~ </label>

                                            <div class="col-sm-3">
                                                <input data-toggle="datepicker" autocomplete="off" placeholder="ｰｰｰｰ/ｰｰ/ｰｰ" name="request_user_end_date" id="end_date" class="form-control custom-table-margin custom-img" value="{{$requestUserData['searchValue']['request_user_end_date'] != null ? $requestUserData['searchValue']['request_user_end_date'] : old('request_user_end_date')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            ユーザー名
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control custom-table-margin" name="request_user_name" id="request_user_name" value="{{$requestUserData['searchValue']['request_user_name'] != null ? $requestUserData['searchValue']['request_user_name'] : old('request_user_name')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            代行会社名
                                        </th>
                                        <td>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control custom-table-margin" name="request_company_name" id="request_company_name" value="{{$requestUserData['searchValue']['request_company_name'] != null ? $requestUserData['searchValue']['request_company_name'] : old('request_company_name')}}">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header">
                                            申込ステータス
                                        </th>
                                        <td>
                                            <div class="col-sm-10">
                                                <label class="col-sm-2">
                                                    @php $requestUserHasStatus = $requestUserData['searchValue']['request_user_has_status'];@endphp
                                                    <input type="radio" class="custom-table-margin" name="request_user_has_status" value="" @if($requestUserHasStatus==null || old('user_status') == null) checked @endif>
                                                    全て
                                                </label>

                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="request_user_has_status" value="1" @if($requestUserHasStatus==1 || old('user_status') == 1) checked @endif>
                                                    依頼
                                                </label>

                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="request_user_has_status" value="2" @if($requestUserHasStatus==2 && is_numeric($requestUserHasStatus)) checked @endif>
                                                    返答中
                                                </label>

                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="request_user_has_status" value="3" @if($requestUserHasStatus==3 && is_numeric($requestUserHasStatus)) checked @endif>
                                                    配車
                                                </label>

                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="request_user_has_status" value="4" @if($requestUserHasStatus==4 && is_numeric($requestUserHasStatus)) checked @endif>
                                                    キャンセル
                                                </label>
                                                <label class="col-sm-2">
                                                    <input class="custom-table-margin" type="radio" name="request_user_has_status" value="5" @if($requestUserHasStatus==5 && is_numeric($requestUserHasStatus)) checked @endif>
                                                    時間経過
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="custom-table-border">
                                        <th class="custom-table-header"></th>
                                        <td>
                                            <div class="col-sm-4">
                                                <input name="page" type="hidden" value="1" id="search_page">
                                                <input name="request_bool_search" type="hidden" value="{{ isset($requestUserData['boolSearch']) ? $requestUserData['boolSearch'] : 0 }}" id="request_bool_search">
                                                <button type="button" name="" value="" id="submit-search-request-user" class="btn-custom btn-default pull-left margin-5px">検索</button>
                                                <button type="button" name="" value="" id="export-csv" class="btn-custom btn-default pull-left margin-5px">CSV出力</button>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </form>
                    </div>

                    <div>
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
                        @if(!$requestUserData["requestUserList"])
                            <div class="custom-table-msg">
                                表示するデータがありません
                            </div>
                        @endif
                        <div class="box-header custom-table-box-header">
                            <h3 class="box-title">代行依頼管理一覧</h3>
                        </div>
                        @if($requestUserData["requestUserList"])
                            <div class="box-body">
                                <table id="sortable" class="table table-bordered table-striped table-hover" style="min-width: 1700px">
                                    <thead>
                                    <tr style="background-color: #D3D3D3;">
                                        <th style="width: 5%;">チェック</th>
                                        <th style="width: 8%;">依頼日時</th>
                                        <th style="width: 7%;">ユーザー名</th>
                                        <th style="width: 12%;">申込ステータス</th>
                                        <th style="width: 8%;">返答数</th>
                                        <th style="width: 8%;">代行会社名</th>
                                        <th style="width: 20%;">お迎え先</th>
                                        <th style="width: 20%;">目印</th>
                                        <th style="width: 20%;">おかえり先</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($requestUserData["requestUserList"] as $key => $requestUser)
                                        <tr class="user">
                                            <td align="center">
                                                <label class="container-radio">
                                                    <input name="confirm_change" value="{{ $requestUser->request_users_id }}" type="checkbox">
                                                    <span class="checkmark"></span>
                                                </label>
                                            </td>
                                            <td>
                                                @php
                                                    $time = strtotime($requestUser->request_users_created_at);
                                                @endphp
                                                {{ date('Y/m/d H:i',$time) }}
                                            </td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $requestUser->request_user_user_name ? $requestUser->request_user_user_name : "" }}">{{ $requestUser->request_user_user_name }}</span></td>
                                            <td>
                                                @php
                                                    $listRequestUserHasStatus = [
                                                    '1' => '依頼',
                                                    '2' => '返答中',
                                                    '3' => '配車',
                                                    '4' => 'キャンセル',
                                                    '5' => '時間経過'                                           
                                                    ];
                                                    if(array_key_exists($requestUser->request_user_has_status_name, $listRequestUserHasStatus)) {
                                                        $generalRequestUserHasStatus = $listRequestUserHasStatus[$requestUser->request_user_has_status_name];
                                                    } else {
                                                        $generalRequestUserHasStatus = "";
                                                    }
                                                @endphp
                                                {{ $generalRequestUserHasStatus }}
                                            </td>
                                            <td class="txt-left table-cut number_response cursor-pointer" data-requestId="{{$requestUser->request_users_id}}"><span class="cut-text"  data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $requestUser->count_response ? $requestUser->count_response : '' }}">{{$requestUser->count_response}}</span></td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $requestUser->request_user_company_name ? $requestUser->request_user_company_name : '' }}">{{ $requestUser->request_user_company_name }}</span></td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $requestUser->request_users_address_from ? checkStringTaxi($requestUser->request_users_address_from) : '' }}">{{ checkStringTaxi($requestUser->request_users_address_from) }}</span></td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $requestUser->request_users_address_note ? $requestUser->request_users_address_note : '' }}">{{ $requestUser->request_users_address_note }}</span></td>
                                            <td class="txt-left table-cut"><span class="cut-text" data-placement="bottom" data-toggle="tooltip" data-original-title="{{ $requestUser->request_users_address_to ? $requestUser->request_users_address_to : '' }}">{{ $requestUser->request_users_address_to }}</span></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="row custom-footer-list">
                            <div class="col-sm-3">
                                <button 
                                    {{-- onclick="getRequestUserIdDelete()"  --}}
                                    class="btn-custom btn-default pull-left margin-5px"
                                    id="button-delete">削除</button>
                            </div>
                            <div class="col-sm-9" style="float: right">
                                @php
                                    $page = $requestUserData["page"];
                                @endphp
                                @include('admin.common.paging')
                            </div>
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
                    <button type="button" class="btn-custom btn-primary margin-5px" id="confirm-delete">削除</button>
                    <button type="button" class="btn-custom btn-default margin-5px" data-dismiss="modal">戻る</button>
                    <form method="POST" id="deleteRequestUser">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="modal-response" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-body">
            <table class="table table-bordered table-striped table-hover" id="showData">
                <tbody id="table-companies">
                </tbody>
            </table>
            <div id="noData" class="text-center">表示するデータがありません</div>
        </div>
        </div>
    </div>
    </div>
@endsection

@section('javascript_bottom')
    <script>
        function pageing(page) {
            var search_form_request_user = $('#search_form_request_user');
            search_form_request_user.attr('action', '{{ route('requestuser.index') }}');
            $('#search_page').val(page);
            resetDataOldFormSearch(window.objectUserDataSearchOld, search_form_request_user)
            search_form_request_user.submit();
        }

        // function getRequestUserIdDelete() {
        //     if($('input[name=confirm_change]').is(':checked')) {
        //         var userId = $('input[name=confirm_change]:checked').val();
        //         var url = '{{ route("requestuser.delete", ":id") }}';
        //         url = url.replace(':id', userId);

        //         $('#deleteRequestUser').attr('action', url);
        //         $('#modal-default').modal('show');
        //     }
        // }


        $(document).ready(function() {
            window.objectUserDataSearchOld = JSON.parse('<?php echo json_encode(isset($requestUserData['searchValue']) ? $requestUserData['searchValue'] : []); ?>');

            $( "#export-csv" ).click(function() {
                $('#search_form_request_user').attr('action', '{{ route('requestuser.exportCsv') }}');
                $('#search_form_request_user').submit();

                //reset action
                $('#search_form_request_user').attr('action', '{{ route('requestuser.index') }}');
            });

            $( "#submit-search-request-user" ).click(function() {
                $('#search_form_request_user').attr('action', '{{ route('requestuser.index') }}');
                $('#request_bool_search').val(1);
                $('#search_form_request_user').submit();
            });

            $('[data-toggle="tooltip"]').tooltip({
                'container':'body'
            });

            $.datepicker.setDefaults( $.datepicker.regional[ "ja" ] );

            $('input[data-toggle="datepicker"]').datepicker({
                dateFormat: 'yy/mm/dd',
            });

            $("#button-delete").on('click',function(){
                if( array_delete != []){
                    $("input[name=confirm_change]:checked").each(function() { 
                        array_delete.push($(this).val());
                    });
                    $('#modal-default').modal('show');
                }
            })
            // add mutil delete
            var array_delete = [];
            $('#confirm-delete').on('click',function(){
                $.ajax({
                    url: "/admin/requestuser/delete",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        array_delete : array_delete
                    },
                    dataType: "json",
                    success: function(data){
                        $('#modal-default').modal('hide');
                        if(data.status == 200 && data.message == "Successfully."){
                            window.location.reload();
                        }
                        else{
                            alert(data.message);
                        }
                    }
                });
            });
            $(".number_response").on('click',function(){
                let request_id = $(this).attr('data-requestId');
                $.ajax({
                    type: "POST",
                    url: "/admin/requestuser/getListResponseCompany",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "request_id" : request_id
                    },
                    cache: false,
                    success: function(data){
                        if(data.length != 0){
                            $("#noData").hide();
                            $("#showData").show();
                            for( let i = 0; i < data.length; i++){
                                $("#table-companies").append("<tr>"+
                                    "<td>"+data[i].name+"代行</td>"+
                                    "<td>"+data[i].time_pickup+"分</td>"+
                                "</tr>")
                            }
                        }
                        else{
                            $("#showData").hide();
                            $("#noData").show();
                        }
                        
                        $('#modal-response').modal('show');
                    }
                });
            });
            $('#modal-response').on('hidden.bs.modal', function () {
                $("#table-companies").html('');
            })
        });
    </script>
@endsection

@section('css_bottom')
@endsection
