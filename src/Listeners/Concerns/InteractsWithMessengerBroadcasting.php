<?php

declare(strict_types=1);

namespace Syriable\Messenger\Listeners\Concerns;

trait InteractsWithMessengerBroadcasting
{
    protected function broadcastingEnabled(): bool
    {
        return (bool) config('messenger.broadcasting.enabled', false);
    }
}
