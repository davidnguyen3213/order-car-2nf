<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CorrespondingArea extends Model
{
    protected $table = 'corresponding_area';
    protected $fillable = [
        'company_id', 'corresponding_area', 'type'
    ];

    /**
     * Define relationship table UnregisteredCompany
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unregisteredCompanies()
    {
        return $this->belongsto('App\UnregisteredCompany', 'company_id', 'id')
            ->correspondingAreaUnregisteredCompanies();
    }

    /**
     * Define relationship table company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function companies()
    {
        return $this->belongsto('App\Company', 'company_id', 'id')
            ->correspondingAreaCompanies();
    }

    /**
     * Get token UnregisteredCompany
     *
     * @param $query
     * @return mixed
     */
    public function scopeCorrespondingAreaUnregisteredCompanies($query)
    {
        return $query->where('type', \Config::get('constants.TYPE_AREA.UNREGISTERED_COMPANY'));
    }

    /**
     * Get token Company
     *
     * @param $query
     * @return mixed
     */
    public function scopeCorrespondingAreaCompanies($query)
    {
        return $query->where('type', \Config::get('constants.TYPE_AREA.COMPANY'));
    }
}

