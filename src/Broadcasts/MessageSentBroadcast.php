<?php

declare(strict_types=1);

namespace Syriable\Messenger\Broadcasts;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Support\MessengerChannels;

final class MessageSentBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly MessageSent $domainEvent,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(MessengerChannels::conversation($this->domainEvent->conversation)),
            new PrivateChannel(MessengerChannels::participant($this->domainEvent->sender)),
            new PrivateChannel(MessengerChannels::participant($this->domainEvent->recipient)),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $message = $this->domainEvent->message;

        return [
            'message' => [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'body' => $message->body,
                'reply_to_message_id' => $message->reply_to_message_id,
                'created_at' => $message->created_at?->toIso8601String(),
            ],
            'conversation' => [
                'id' => $this->domainEvent->conversation->id,
                'latest_message_id' => $this->domainEvent->conversation->latest_message_id,
                'latest_message_at' => $this->domainEvent->conversation->latest_message_at?->toIso8601String(),
            ],
            'sender' => [
                'type' => $this->domainEvent->sender->getMorphClass(),
                'id' => $this->domainEvent->sender->getKey(),
            ],
            'recipient' => [
                'type' => $this->domainEvent->recipient->getMorphClass(),
                'id' => $this->domainEvent->recipient->getKey(),
            ],
        ];
    }
}
