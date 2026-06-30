<?php

namespace Foutraz\Withings\Facades;

use Illuminate\Support\Facades\Facade;

class Withings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'withings';
    }
}
