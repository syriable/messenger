<?php

declare(strict_types=1);

namespace Syriable\Messenger\Broadcasts;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Syriable\Messenger\Events\MessageReported;
use Syriable\Messenger\Support\MessengerChannels;

final class MessageReportedBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly MessageReported $domainEvent,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(MessengerChannels::conversation($this->domainEvent->conversation)),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.reported';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $report = $this->domainEvent->report;
        $message = $this->domainEvent->message;

        return [
            'report' => [
                'id' => $report->id,
                'message_id' => $report->message_id,
                'reason' => $report->reason,
                'created_at' => $report->created_at?->toIso8601String(),
            ],
            'message' => [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
            ],
            'reporter' => [
                'type' => $this->domainEvent->reporter->getMorphClass(),
                'id' => $this->domainEvent->reporter->getKey(),
            ],
        ];
    }
}
