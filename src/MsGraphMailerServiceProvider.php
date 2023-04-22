<?php

namespace Jornatf\MsGraphMailer;

use Illuminate\Support\ServiceProvider;
use Jornatf\MsGraphMailer\MsGraphMailer;

class MsGraphMailerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('MsGraphMailer', function($app) {
            return new MsGraphMailer();
        });
    }
}
