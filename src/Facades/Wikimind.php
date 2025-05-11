<?php

namespace Denason\Wikimind\Facades;

use Denason\Wikimind\WikimindInterface;
use Illuminate\Support\Facades\Facade;

class Wikimind extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WikimindInterface::class;
    }
}
