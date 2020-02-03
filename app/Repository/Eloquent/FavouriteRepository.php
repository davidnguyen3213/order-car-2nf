<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\FavouriteInterface as FavouriteInterface;

class FavouriteRepository extends BaseRepository implements FavouriteInterface
{

    protected function model()
    {
        return \App\Favourite::class;
    }

    protected function getRules()
    {
        return \App\Favourite::rules;
    }
}
