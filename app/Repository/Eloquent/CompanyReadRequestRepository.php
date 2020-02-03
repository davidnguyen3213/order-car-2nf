<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts\CompanyReadRequestInterface as CompanyReadRequestInterface;

class CompanyReadRequestRepository extends BaseRepository implements CompanyReadRequestInterface
{

    protected function model()
    {
        return \App\CompanyReadRequest::class;
    }

    protected function getRules()
    {
        return \App\CompanyReadRequest::rules;
    }
}
