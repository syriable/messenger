<?php

declare(strict_types=1);

namespace Syriable\Messenger\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Syriable\Messenger\Models\Conversation;
use Syriable\Messenger\Models\Message;
use Syriable\Messenger\Models\MessageReport;

final class MessageReported
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly MessageReport $report,
        public readonly Message $message,
        public readonly Conversation $conversation,
        public readonly Model $reporter,
    ) {}
}
