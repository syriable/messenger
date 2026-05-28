<?php

declare(strict_types=1);

namespace Syriable\Messenger\Data;

final readonly class ConversationMessagesOptions
{
    public function __construct(
        public ?int $limit = null,
    ) {}
}
