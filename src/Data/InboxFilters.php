<?php

declare(strict_types=1);

namespace Syriable\Messenger\Data;

final readonly class InboxFilters
{
    public function __construct(
        public ?bool $archived = false,
        public ?bool $starred = null,
    ) {}
}
