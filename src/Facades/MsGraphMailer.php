<?php

namespace Jornatf\MsGraphMailer\Facades;

use Illuminate\Support\Facades\Facade;

class MsGraphMailer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Jornatf\MsGraphMailer\MsGraphMailer::class;
    }
}
