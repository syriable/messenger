<?php

declare(strict_types=1);

namespace Syriable\Messenger;

final class MessengerManager
{
    public function table(string $key): string
    {
        /** @var string $table */
        $table = config("messenger.table_names.{$key}");

        return $table;
    }
}
