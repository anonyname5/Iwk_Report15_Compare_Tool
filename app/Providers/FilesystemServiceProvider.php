<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemServiceProvider as BaseFilesystemServiceProvider;

class FilesystemServiceProvider extends BaseFilesystemServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        
        $this->app->singleton('files', function () {
            return new \Illuminate\Filesystem\Filesystem;
        });
    }
} 