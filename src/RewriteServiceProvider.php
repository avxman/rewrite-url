<?php

namespace Avxman\Rewrite;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class RewriteServiceProvider extends ServiceProvider implements DeferrableProvider
{

    /**
     * Bootstrap any application services.
     *
     * @param Filesystem $filesystem
     * @return void info
     */
    public function boot(Filesystem $filesystem){

        if(App()->runningInConsole()){
            $this->publishes([
                dirname(__DIR__, 1).'/database/migrations/create_routes_table.php.stub' => $this->getMigrationFileName($filesystem, 'create_routes_table'),
                dirname(__DIR__, 1).'/database/migrations/create_route_groups_table.php.stub' => $this->getMigrationFileName($filesystem, 'create_route_groups_table'),
                dirname(__DIR__, 1).'/config/rewrite.php' => $this->getConfigFileName($filesystem, 'rewrite'),
                dirname(__DIR__, 1).'/database/seeders/RouteGroupsSeeder.php' => $this->getSeederFileName($filesystem, 'RouteGroupsSeeder'),
                dirname(__DIR__, 1).'/database/seeders/RoutesSeeder.php' => $this->getSeederFileName($filesystem, 'RoutesSeeder'),
                dirname(__DIR__, 1).'/database/factories/RouteGroupsFactory.php' => $this->getFactoryFileName($filesystem, 'RouteGroupsFactory'),
                dirname(__DIR__, 1).'/database/factories/RoutesFactory.php' => $this->getFactoryFileName($filesystem, 'RoutesFactory'),
                dirname(__DIR__, 1).'/controllers/RouteController.php.stub' => $this->getControllerFileName($filesystem, 'RouteController'),
                dirname(__DIR__, 1).'/controllers/ResourceController.php.stub' => $this->getControllerFileName($filesystem, 'ResourceController'),
            ], 'avxman-rewrite-url');

            $this->publishes([
                dirname(__DIR__, 1).'/models/Routes.php.stub' => $this->getModelFileName($filesystem, 'Routes'),
                dirname(__DIR__, 1).'/models/RouteGroups.php.stub' => $this->getModelFileName($filesystem, 'RouteGroups'),
            ], 'avxman-rewrite-url-model');
        }

    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        if(Config()->has('rewrite.provider')) {
            App()->register(Config()->get('rewrite.provider'));
            Config()->push('app.providers', Config()->get('rewrite.provider'));
        }
    }

    public function provides()
    {
        return [Config()->get('rewrite.provider')];
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @param string $name
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem, string $name = ''): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $name) {
                return $filesystem->glob($path.'*_'.$name.'.php');
            })->push($this->app->databasePath().DIRECTORY_SEPARATOR."migrations/{$timestamp}_{$name}.php")
            ->first();
    }

    /**
     * Returns existing config file if found, else uses the current.
     *
     * @param Filesystem $filesystem
     * @param string $name
     * @return string
     */
    protected function getConfigFileName(Filesystem $filesystem, string $name = ''): string
    {
        return Collection::make($this->app->configPath().DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $name) {
                return $filesystem->glob($path.$name.'.php');
            })->push($this->app->configPath().DIRECTORY_SEPARATOR."{$name}.php")
            ->first();
    }

    /**
     * Returns existing route file if found, else uses the current.
     *
     * @param Filesystem $filesystem
     * @param string $name
     * @return string
     */
    protected function getRouteFileName(Filesystem $filesystem, string $name = ''): string
    {
        return Collection::make(base_path('routes').DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $name) {
                return $filesystem->glob($path.$name.'.php');
            })->push(base_path('routes').DIRECTORY_SEPARATOR."{$name}.php")
            ->first();
    }

    /**
     * Returns existing model file if found, else uses the current.
     *
     * @param Filesystem $filesystem
     * @param string $name
     * @return string
     */
    protected function getModelFileName(Filesystem $filesystem, string $name = ''): string
    {
        return Collection::make(app_path('Models').DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $name) {
                return $filesystem->glob($path.$name.'.php');
            })->push(app_path('Models'.DIRECTORY_SEPARATOR."{$name}.php"))
            ->first();
    }

    /**
     * Returns existing seeder file if found, else uses the current.
     *
     * @param Filesystem $filesystem
     * @param string $name
     * @return string
     */
    protected function getSeederFileName(Filesystem $filesystem, string $name = ''): string
    {
        return Collection::make(database_path('seeders').DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $name) {
                return $filesystem->glob($path.$name.'.php');
            })->push(database_path('seeders'.DIRECTORY_SEPARATOR."{$name}.php"))
            ->first();
    }

    /**
     * Returns existing factory file if found, else uses the current.
     *
     * @param Filesystem $filesystem
     * @param string $name
     * @return string
     */
    protected function getFactoryFileName(Filesystem $filesystem, string $name = ''): string
    {
        return Collection::make(database_path('factories').DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $name) {
                return $filesystem->glob($path.$name.'.php');
            })->push(database_path('factories'.DIRECTORY_SEPARATOR."{$name}.php"))
            ->first();
    }

    /**
     * Returns existing controller file if found, else uses the current.
     *
     * @param Filesystem $filesystem
     * @param string $name
     * @return string
     */
    protected function getControllerFileName(Filesystem $filesystem, string $name = ''): string
    {
        return Collection::make(app_path('Http/Controllers/Routes').DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $name) {
                return $filesystem->glob($path.$name.'.php');
            })->push(app_path("Http/Controllers/Routes".DIRECTORY_SEPARATOR."{$name}.php"))
            ->first();
    }

}
