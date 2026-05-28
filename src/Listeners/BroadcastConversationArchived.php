<?php

declare(strict_types=1);

namespace Syriable\Messenger\Listeners;

use Syriable\Messenger\Broadcasts\ConversationParticipantBroadcast;
use Syriable\Messenger\Events\ConversationArchived;
use Syriable\Messenger\Listeners\Concerns\InteractsWithMessengerBroadcasting;

final class BroadcastConversationArchived
{
    use InteractsWithMessengerBroadcasting;

    public function handle(ConversationArchived $event): void
    {
        if (! $this->broadcastingEnabled()) {
            return;
        }

        broadcast(new ConversationParticipantBroadcast(
            eventName: 'conversation.archived',
            conversation: $event->conversation,
            participant: $event->participant,
            actor: $event->actor,
        ));
    }
}
