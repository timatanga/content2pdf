<?php

/*
 * This file is part of the Content2Pdf package.
 *
 * (c) Mark Fluehmann mark.fluehmann@gmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace timatanga\Content2Pdf\Providers;

use Illuminate\Support\ServiceProvider;

class Content2PdfServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // register a controller
        // $this->app->make('dbizapps\Workflow\...');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // register package routes 
        // include __DIR__.'/../../routes/routes.php';

        // publish config file
        $this->publishes([
            __DIR__.'/../../config/templates.php' => config_path('templates.php')
        ], 'config');
    }
}
