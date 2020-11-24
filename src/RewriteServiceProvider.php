<?php

namespace Avxman\Rewrite;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class RewriteServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @param Filesystem $filesystem
     * @return void info
     */
    public function boot(Filesystem $filesystem){

        $this->publishes([
            dirname(__DIR__, 1).'/database/migrations/create_route_table.php.stub' => $this->getMigrationFileName($filesystem, 'create_route_table'),
            dirname(__DIR__, 1).'/database/migrations/create_route_group_table.php.stub' => $this->getMigrationFileName($filesystem, 'create_route_group_table'),
            dirname(__DIR__, 1).'/config/rewrite.php' => $this->getConfigFileName($filesystem, 'rewrite'),
            dirname(__DIR__, 1).'/routes/rewrite_api.php' => $this->getRouteFileName($filesystem, 'rewrite_api'),
            dirname(__DIR__, 1).'/routes/rewrite_web.php' => $this->getRouteFileName($filesystem, 'rewrite_web'),
        ], 'avxman-rewrite-url');

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

}
