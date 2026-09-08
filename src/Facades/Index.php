<?php

declare(strict_types=1);

namespace Onelegstudios\Index\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Onelegstudios\Index\Index
 */
class Index extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Onelegstudios\Index\Index::class;
    }
}
