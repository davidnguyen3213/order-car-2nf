<?php

return [
    'APP' => [
        'ANDROID_USER_APP_VERSION' => env('ANDROID_USER_APP_VERSION', ''),
        'IOS_USER_APP_VERSION' => env('IOS_USER_APP_VERSION', ''),
        'ANDROID_COMPANY_APP_VERSION' => env('ANDROID_COMPANY_APP_VERSION', ''),
        'IOS_COMPANY_APP_VERSION' => env('IOS_COMPANY_APP_VERSION', ''),
        'ANDROID_USER_APP_DOWNLOAD_URL' => env('ANDROID_USER_APP_DOWNLOAD_URL', ''),
        'IOS_USER_APP_DOWNLOAD_URL' => env('IOS_USER_APP_DOWNLOAD_URL', ''),
        'ANDROID_COMPANY_APP_DOWNLOAD_URL' => env('ANDROID_COMPANY_APP_DOWNLOAD_URL', ''),
        'IOS_COMPANY_APP_DOWNLOAD_URL' => env('IOS_COMPANY_APP_DOWNLOAD_URL', ''),
    ],
    'TYPE_USER' => [
        'WEB_ADMIN' => 1,
        'OTHER' => 0
    ],
    'FAVOURITE' => 1,
    'REQUEST_USERS' => [
        'EXPIRED' => 1,
        'UNEXPIRED' => 0,
        'CANCELED' => 1,
        'IS_HISTORY_DELETED' => 1,
        'IS_HISTORY_ACTIVE' => 0,
        'IS_DELETED_FREQUENCY' => 1,
        'IS_ACTIVE_FREQUENCY' => 0
    ],
    'RESPONSE_FOR_USERS' => [
        'DELETED' => 1,
        'NOT_DELETED' => 0,
        'APPROVED' => 1,
        'UNAPPROVED' => 0,
        'STATUS_ACCEPTED' => 1,
        'STATUS_UNACCEPTED' => 0,
        'READ' => 1,
        'UNREAD' => 0,
    ],
    'COMPANY_LOGIN' => [
        'STATUS_DISABLE' => 0,
        'STATUS_ENABLE' => 1
    ],
    'USER_LOGIN' => [
        'STATUS_DISABLE' => 0,
        'STATUS_ENABLE' => 1,
    ],
    'NOTIFY' => [
        'OFF' => 0,
        'ON' => 1
    ],
    'REQUEST_MINUTE_EXISTED' => [
        'USER_WAIT' => 5,
        'COMPANY_WAIT' => 2
    ],
    'FCM_SERVER_KEY' => env('FCM_SERVER_KEY', ''),
    'TYPE_NOTIFY' => [
        'USER' => 1,
        'COMPANY' => 2,
        'All' => 0
    ],
    'TYPE_AREA' => [
        'COMPANY' => 1,
        'UNREGISTERED_COMPANY' => 2
    ],
    'NUMBER_PERPAGE' => 20,
    'TYPE_PLATFORM' => [
        0 => 'android',
        1 => 'ios'
    ],
    'STATUS_CODE_NOTIFY' => [
        'USER_TO_ALL_COMPANY' => 'APP001',
        'COMPANY_AGREE_REQUEST_USER' => 'APP002',
        'USER_ACCEPT_COMPANY' => 'APP003',
        'COMPANY_APPROVE_USER' => 'APP004',
        'USER_CANCEL_REQUEST' => 'APP005',
        'USER_UPDATE' => 'APP006',
        'REQUEST_USER_EXPIRED_TO_USER' => 'APP007',
        'REQUEST_USER_EXPIRED_TO_COMPANY_AGREE' => 'APP008',
        'COMPANY_CANCEL_REQUEST' => 'APP009',
        'SYNC_COMPANY_READ_REQUEST' => 'APP011',
        'SYNC_USER_READ_RESPONSE' => 'APP012',
        'COMPANY_UPDATE' => 'APP010',
        'USER_DELETE_HISTORY' => 'APP013',
        'ADMIN_PUSH_NOTIFY' => 'APP014',
    ],
    'USER_DELETED' => [
        'DELETED' => 1,
        'ACTIVE' => 0,
    ],
    'COMPANY_DELETED' => [
        'DELETED' => 1,
        'ACTIVE' => 0,
    ],
    'SMS_CONFIG' => [
        'USERNAME' => env('SMS_USERNAME'),
        'PASSWORD' => env('SMS_PASSWORD')
    ],
    'DELIMITER' => "、",
    "NOTIFY_READ"=> [
        "READ" => 1
    ]
];
