<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsOtp extends Model
{
    protected $table = 'sms_otp';
    protected $fillable = [
        'phone', 'otp_code'
    ];
}
