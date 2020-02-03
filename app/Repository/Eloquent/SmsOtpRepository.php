<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\SmsOtpInterface as SmsOtpInterface;

class SmsOtpRepository extends BaseRepository implements SmsOtpInterface
{
    protected function model()
    {
        return \App\SmsOtp::class;
    }

    protected function getRules()
    {
        return \App\SmsOtp::rules;
    }

}
