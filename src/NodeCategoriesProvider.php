<?php

namespace VergilLai\NodeCategories;

use Illuminate\Support\ServiceProvider;

/**
 * Class NodeCategoriesProvider
 * @package VergilLai\NodeCategories
 * @author Vergil <vergil@vip.163.com>
 */
class NodeCategoriesProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register commands
        $this->commands('command.node-categories.migration');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();
    }

    private function registerCommands()
    {
        $this->app->singleton('command.node-categories.migration', function ($app) {
            return new MigrationCommand();
        });
    }


}
