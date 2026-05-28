<?php

declare(strict_types=1);

namespace Syriable\Messenger\Listeners;

use Syriable\Messenger\Broadcasts\ConversationParticipantBroadcast;
use Syriable\Messenger\Events\ConversationMarkedAsRead;
use Syriable\Messenger\Listeners\Concerns\InteractsWithMessengerBroadcasting;

final class BroadcastConversationMarkedAsRead
{
    use InteractsWithMessengerBroadcasting;

    public function handle(ConversationMarkedAsRead $event): void
    {
        if (! $this->broadcastingEnabled()) {
            return;
        }

        broadcast(new ConversationParticipantBroadcast(
            eventName: 'conversation.marked_as_read',
            conversation: $event->conversation,
            participant: $event->participant,
            actor: $event->actor,
        ));
    }
}
