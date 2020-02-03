<?php

return [
    'TEMP_MSG_PUSH_NOTIFY' => [
        'MESSAGE' => [
            'request_id' => '',
            'company_id' => '',
            'time_pickup' => ''
        ],
        'USER_TO_ALL_COMPANY' => [
            'title' => '代行アプリ通知',
            'messageBody' => '依頼が追加されました。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.COMPANY'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.USER_TO_ALL_COMPANY')
        ],
        'COMPANY_AGREE_REQUEST_USER' => [
            'title' => '代行アプリ通知',
            'messageBody' => '依頼に返答がされました。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.USER'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.COMPANY_AGREE_REQUEST_USER')
        ],
        'USER_ACCEPT_COMPANY' => [
            'title' => '代行アプリ通知',
            'messageBody' => '依頼が確定いたしました。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.COMPANY'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.USER_ACCEPT_COMPANY')
        ],
        'COMPANY_APPROVE_USER' => [
            'title' => '代行アプリ通知',
            'messageBody' => '代行会社が向かっております。 しばらくお待ちください。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.USER'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.COMPANY_APPROVE_USER')
        ],
        'USER_CANCEL_REQUEST' => [
            'title' => '代行アプリ通知',
            'messageBody' => '依頼がキャンセルされました。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.COMPANY'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.USER_CANCEL_REQUEST')
        ],
        'USER_UPDATE' => [
            'title' => '代行アプリ通知',
            'messageBody' => 'ユーザー情報は更新されました。再度ログインしてください。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.USER'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.USER_UPDATE')
        ],
        'COMPANY_UPDATE' => [
            'title' => '代行アプリ通知',
            'messageBody' => 'ユーザー情報は更新されました。再度ログインしてください。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.COMPANY'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.COMPANY_UPDATE')
        ],
        'REQUEST_USER_EXPIRED_TO_USER' => [
            'title' => '代行アプリ通知',
            'messageBody' => '締切時間が経過いたしました。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.USER'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.REQUEST_USER_EXPIRED_TO_USER')
        ],
        'REQUEST_USER_EXPIRED_TO_COMPANY_AGREE' => [
            'title' => '代行アプリ通知',
            'messageBody' => '締切時間が経過いたしました。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.COMPANY'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.REQUEST_USER_EXPIRED_TO_COMPANY_AGREE')
        ],
        'COMPANY_CANCEL_REQUEST' => [
            'title' => '代行アプリ通知',
            'messageBody' => '返答がキャンセルされました。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.COMPANY'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.COMPANY_CANCEL_REQUEST')
        ],
        'SYNC_COMPANY_READ_REQUEST' => [
            'title' => '代行アプリ通知',
            'messageBody' => '情報を同期する。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.COMPANY'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.SYNC_COMPANY_READ_REQUEST')
        ],
        'SYNC_USER_READ_RESPONSE' => [
            'title' => '代行アプリ通知',
            'messageBody' => '情報を同期する。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.USER'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.SYNC_USER_READ_RESPONSE')
        ],
        'USER_DELETE_HISTORY' => [
            'title' => '代行アプリ通知',
            'messageBody' => '依頼履歴を削除しました。',
            'type_app' => \Config::get('constants.TYPE_NOTIFY.USER'),
            'status_code' => \Config::get('constants.STATUS_CODE_NOTIFY.USER_DELETE_HISTORY')
        ],
    ]
];