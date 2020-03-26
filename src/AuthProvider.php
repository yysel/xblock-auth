<?php

namespace XBlock\Auth;

use Illuminate\Support\ServiceProvider;

class AuthProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */


    public function boot()
    {
        $this->app->make('auth')->viaRequest('xblock', function ($request) {
            $auth = new AuthService();
            return $auth->getUserFormParseBearerToken($request);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->routeMiddleware([
            'auth' => Authenticate::class,
        ]);

        $this->app->router->group(['prefix' => 'api/xblock/auth', 'namespace' => 'XBlock\Auth'], function ($router) {
            $router->post('/login', [
                'uses' => 'Login@index',
            ]);
            $router->get('/user', [
                'uses' => 'Login@getLoginUser',
                'middleware' => 'auth'
            ]);
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'xblock');
            $this->registerMigrations();
            $this->commands([
                CreateKey::class
            ]);
        }

    }

    protected function registerMigrations()
    {
        $path = base_path('config/auth.php');
        $config = file_exists($path) ? require_once($path) : [];
        $provider = isset($config['guards']['api']['provider']) ? $config['guards']['api']['provider'] : 'xblock';
        $driver = isset($config['providers'][$provider]['driver']) ? $config['providers'][$provider]['driver'] : 'cache';
        if ($driver === 'database') $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

}
