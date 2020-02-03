<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    protected $fillable = [
        'user_id', 'unregistered_company_id',
    ];

    /**
     * Define relationship table UnregisteredCompany
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unregisteredCompanies()
    {
        return $this->belongsto('App\UnregisteredCompany', 'unregistered_company_id', 'id');
    }
}
