<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyReadRequest extends Model
{
    protected $table = 'company_read_request';

    protected $fillable = [
        'request_id', 'company_id',
    ];

    /**
     * Define relationship table Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function companies()
    {
        return $this->belongsto('App\Company', 'company_id', 'id');
    }

    /**
     * Define relationship table RequestUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestUser()
    {
        return $this->belongsto('App\RequestUser', 'request_id', 'id');
    }
}
