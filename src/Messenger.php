<?php

declare(strict_types=1);

namespace Syriable\Messenger;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string table(string $key)
 *
 * @see MessengerManager
 */
final class Messenger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'messenger';
    }
}
