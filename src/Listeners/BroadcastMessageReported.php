<?php

declare(strict_types=1);

namespace Syriable\Messenger\Listeners;

use Syriable\Messenger\Broadcasts\MessageReportedBroadcast;
use Syriable\Messenger\Events\MessageReported;
use Syriable\Messenger\Listeners\Concerns\InteractsWithMessengerBroadcasting;

final class BroadcastMessageReported
{
    use InteractsWithMessengerBroadcasting;

    public function handle(MessageReported $event): void
    {
        if (! $this->broadcastingEnabled()) {
            return;
        }

        broadcast(new MessageReportedBroadcast($event));
    }
}
