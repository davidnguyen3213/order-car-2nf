<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('without_spaces', function ($attribute, $value) {
            return preg_match('/^\S*$/u', $value);
        });

        Validator::extend('alpha_spaces', function ($attribute, $value) {
            return preg_match('/^[\pL\s\d]+$/u', $value);
        });

        Validator::extend('company_phone', function ($attribute, $value) {
            return preg_match('/^\d{2,3}-\d{4}-\d{4}$/', $value)
                || preg_match('/^\d{4}-\d{2}-\d{4}$/', $value)
                || preg_match('/^\d{10,11}$/', $value);
        });

        Validator::extend('user_phone', function ($attribute, $value) {
            return preg_match('/^\d{3}-\d{4}-\d{4}$/', $value);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
