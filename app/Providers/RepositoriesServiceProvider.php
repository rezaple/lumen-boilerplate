<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\EloquentUserRepository;
use App\Repositories\Contracts\UserRepository;

use App\Repositories\EloquentExampleRepository;
use App\Repositories\Contracts\BaseRepository;

class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserRepository::class,EloquentUserRepository::class);
        $this->app->bind(BaseRepository::class,EloquentExampleRepository::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            BaseRepository::class,
            UserRepository::class,
        ];
    }
}