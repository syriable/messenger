<?php

declare(strict_types=1);

namespace Syriable\Messenger\Data;

use Illuminate\Database\Eloquent\Model;
use Syriable\Messenger\Models\Message;

final readonly class ReportMessageData
{
    public function __construct(
        public Message $message,
        public Model $reporter,
        public ?string $reason = null,
    ) {}
}
