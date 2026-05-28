<?php

declare(strict_types=1);

namespace Syriable\Messenger\Listeners;

use Syriable\Messenger\Broadcasts\ConversationParticipantBroadcast;
use Syriable\Messenger\Events\ConversationBlocked;
use Syriable\Messenger\Listeners\Concerns\InteractsWithMessengerBroadcasting;

final class BroadcastConversationBlocked
{
    use InteractsWithMessengerBroadcasting;

    public function handle(ConversationBlocked $event): void
    {
        if (! $this->broadcastingEnabled()) {
            return;
        }

        broadcast(new ConversationParticipantBroadcast(
            eventName: 'conversation.blocked',
            conversation: $event->conversation,
            participant: $event->participant,
            actor: $event->actor,
        ));
    }
}
