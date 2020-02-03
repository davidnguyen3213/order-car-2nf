<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<div>
    <p>{{$company}}　{{strlen(trim($person_charged)) > 0 ? $person_charged . '　' : ''}}様</p>
    <p>「{{$app_name}}」に代行業者登録申請ご登録いただき、誠にありがとうございます。<br>
        後ほど、担当よりご連絡いたします。
    </p>
    <div>
        会社名：{{$company}}<br>
        所在地：{{$address}}<br>
        担当者名：{{$person_charged}}<br>
        電話番号：{{$phone}}<br>
    </div><br>
    本メールは受信専用です。お問い合わせは、お手数ですが下記フォームからお願いいたします。<br>
    本メールにお心当たりのない場合は、お手数ですが本メールの破棄をお願いいたします。<br>
    ________________<br>
    「{{$app_name}}」<br>
    ▽運営会社：株式会社ビーセンサー<br>
    {{$admin_email}}<br>
    ________________
</div>
</body>
</html>