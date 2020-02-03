<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\CallCountInterface as CallCountInterface;

class CallCountRepository extends BaseRepository implements CallCountInterface
{

    protected function model()
    {
        return \App\CallCount::class;
    }

    protected function getRules()
    {
        return \App\CallCount::rules;
    }
}
