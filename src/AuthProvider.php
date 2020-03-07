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

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'xblock-auth-migrations');


//            $this->commands([
//                Console\InstallCommand::class,
//                Console\ClientCommand::class,
//                Console\KeysCommand::class,
//            ]);
        }
        $this->app->make('auth')->viaRequest('xblock', function ($request) {
            $auth = new AuthService();
            $token = $auth->parseRequestBearerToken($request);
            return $token ? $token->user : null;
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
        $this->registerMigrations();
        $this->app->router->group(['prefix' => 'api/xblock/auth', 'namespace' => 'XBlock\Auth'], function ($router) {
            $router->post('/login', [
                'uses' => 'Login@index',
            ]);
            $router->get('/user', [
                'uses' => 'Login@getLoginUser',
                'middleware' => 'auth'
            ]);
        });

        $this->commands([
            CreateKey::class
        ]);

    }

    protected function registerMigrations()
    {
        return $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }


}
