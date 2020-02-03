<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerService();
        $this->registerModel();
        $this->registerRepository();
    }

    private function registerService()
    {

    }

    /*
    * Register Model for Ioc
    */
    private function registerModel()
    {
    }

    /*
    * Register Repository for Ioc
    */
    private function registerRepository()
    {
        $this->app->bind('App\Repository\Contracts\UserInterface', 'App\Repository\Eloquent\UserRepository');
        $this->app->bind('App\Repository\Contracts\CompanyInterface', 'App\Repository\Eloquent\CompanyRepository');
        $this->app->bind('App\Repository\Contracts\FavouriteInterface', 'App\Repository\Eloquent\FavouriteRepository');
        $this->app->bind('App\Repository\Contracts\RequestUserInterface', 'App\Repository\Eloquent\RequestUserRepository');
        $this->app->bind('App\Repository\Contracts\ResponseForUserInterface', 'App\Repository\Eloquent\ResponseForUserRepository');
        $this->app->bind('App\Repository\Contracts\CompanyReadRequestInterface', 'App\Repository\Eloquent\CompanyReadRequestRepository');
        $this->app->bind('App\Repository\Contracts\CorrespondingAreaInterface', 'App\Repository\Eloquent\CorrespondingAreaRepository');
        $this->app->bind('App\Repository\Contracts\NotifyCationInterface', 'App\Repository\Eloquent\NotifyCationRepository');
        $this->app->bind('App\Repository\Contracts\DeviceTokenInterface', 'App\Repository\Eloquent\DeviceTokenRepository');
    }
}
