<?php

namespace XBlock\Auth;

use Illuminate\Auth\RequestGuard;
use Illuminate\Support\ServiceProvider;

class AuthProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    static $loginUser;

    public function boot()
    {
        $auth = $this->app->make('auth');
        $auth->extend('xblock', function ($app, $name, array $config) {
            $guard = new RequestGuard(function () use ($app, $name, $config) {
                if (static::$loginUser) return static::$loginUser;
                $auth = new AuthService();
                $provider = isset($config['provider']) ? $config['provider'] : null;
                AuthService::$config = [
                    'driver' => config("auth.providers.{$provider}.driver", 'database'),
                    'token' => config("auth.providers.{$provider}.token", \XBlock\Auth\Token::class),
                    'model' => config("auth.providers.{$provider}.model", \App\Models\User::class),
                    'expires' => config("auth.providers.{$provider}.expires", null)
                ];
                $user = $auth->getUserFormParseBearerToken($app['request']);
                static::$loginUser = $user;
                return $user;
            }, $this->app['request']);

            $this->app->refresh('request', $guard, 'setRequest');

            return $guard;
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
