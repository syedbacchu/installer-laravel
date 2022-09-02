<?php

namespace SdTech\ProjectInstaller\Providers;

use Illuminate\Auth\CreatesUserProviders;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use SdTech\ProjectInstaller\Helpers\SdTechGuard;
use SdTech\ProjectInstaller\Middleware\canInstall;
use SdTech\ProjectInstaller\Middleware\canUpdate;

class ProjectInstallerServiceProvider extends ServiceProvider
{
    use CreatesUserProviders;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Auth::extend('sdtech', function ($app, $name, $config) {
            $provider = $this->createUserProvider($config['provider'] ?? null);

            $guard = new SdTechGuard($name, $provider, $app['session.store']);

            // When using the remember me functionality of the authentication services we
            // will need to be set the encryption instance of the guard, which allows
            // secure, encrypted cookie values to get generated for those cookies.
            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($app['cookie']);
            }

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
            }

            return $guard;
        });
//        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }

    /**
     * Bootstrap the application events.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function boot(Router $router)
    {
        $this->publishFiles();
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $router->middlewareGroup('install', [CanInstall::class]);
        $router->middlewareGroup('update', [CanUpdate::class]);
    }

    /**
     * Publish config file for the installer.
     *
     * @return void
     */
    protected function publishFiles()
    {
        $this->publishes([
            __DIR__ . '/../Config/installer.php' => base_path('config/installer.php'),
        ], 'projectinstaller');

        $this->publishes([
            __DIR__ . '/../assets' => public_path('installer'),
        ], 'projectinstaller');

        $this->publishes([
            __DIR__ . '/../Views' => base_path('resources/views/vendor/installer'),
        ], 'projectinstaller');

        $this->publishes([
            __DIR__ . '/../Lang' => base_path('resources/lang'),
        ], 'projectinstaller');
    }
}
