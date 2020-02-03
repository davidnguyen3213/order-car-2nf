<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnregisteredCompany extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'base_price', 'display_order', 'corresponding_area', 'company_pr'
    ];

    /**
     * Define relationship table favourites
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favourites()
    {
        return $this->hasMany('App\Favourite', 'unregistered_company_id');
    }

    /**
     *
     * Scope a query to only include active search.
     * @param $query
     * @param $search
     * @return mixed
     */
    public function scopeSearchAddress($query, $search)
    {
        if (empty($search)) return $query->whereNotNull('corresponding_area')
            ->where('corresponding_area', '!=', '');

        return $query->whereNotNull('corresponding_area')
            ->where('corresponding_area', '!=', '')
            ->where(function ($query1) use ($search) {
                $query1->whereRaw("corresponding_area LIKE '" . $search . "%'")
                    ->orWhereRaw("'" . $search . "' LIKE CONCAT(corresponding_area, '%')");
            });
    }

    /**
     * Get count favourites
     *
     * @return int
     */
    public function getFavouritesCountAttribute()
    {
        return $this->favourites()->count();
    }
}
