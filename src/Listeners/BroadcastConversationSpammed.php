<?php

declare(strict_types=1);

namespace Syriable\Messenger\Listeners;

use Syriable\Messenger\Broadcasts\ConversationParticipantBroadcast;
use Syriable\Messenger\Events\ConversationSpammed;
use Syriable\Messenger\Listeners\Concerns\InteractsWithMessengerBroadcasting;

final class BroadcastConversationSpammed
{
    use InteractsWithMessengerBroadcasting;

    public function handle(ConversationSpammed $event): void
    {
        if (! $this->broadcastingEnabled()) {
            return;
        }

        broadcast(new ConversationParticipantBroadcast(
            eventName: 'conversation.spammed',
            conversation: $event->conversation,
            participant: $event->participant,
            actor: $event->actor,
        ));
    }
}
