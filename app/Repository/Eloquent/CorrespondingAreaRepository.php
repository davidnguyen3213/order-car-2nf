<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\CorrespondingAreaInterface as CorrespondingAreaInterface;

class CorrespondingAreaRepository extends BaseRepository implements CorrespondingAreaInterface
{

    protected function model()
    {
        return \App\CorrespondingArea::class;
    }

    protected function getRules()
    {
        return \App\CorrespondingArea::rules;
    }
}
