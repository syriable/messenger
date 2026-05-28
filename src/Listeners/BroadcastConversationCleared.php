<?php

declare(strict_types=1);

namespace Syriable\Messenger\Listeners;

use Syriable\Messenger\Broadcasts\ConversationParticipantBroadcast;
use Syriable\Messenger\Events\ConversationCleared;
use Syriable\Messenger\Listeners\Concerns\InteractsWithMessengerBroadcasting;

final class BroadcastConversationCleared
{
    use InteractsWithMessengerBroadcasting;

    public function handle(ConversationCleared $event): void
    {
        if (! $this->broadcastingEnabled()) {
            return;
        }

        broadcast(new ConversationParticipantBroadcast(
            eventName: 'conversation.cleared',
            conversation: $event->conversation,
            participant: $event->participant,
            actor: $event->actor,
        ));
    }
}
