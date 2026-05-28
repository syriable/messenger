<?php

declare(strict_types=1);

namespace Syriable\Messenger\Listeners;

use Syriable\Messenger\Broadcasts\MessageSentBroadcast;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Listeners\Concerns\InteractsWithMessengerBroadcasting;

final class BroadcastMessageSent
{
    use InteractsWithMessengerBroadcasting;

    public function handle(MessageSent $event): void
    {
        if (! $this->broadcastingEnabled()) {
            return;
        }

        broadcast(new MessageSentBroadcast($event));
    }
}
