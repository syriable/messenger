<?php

declare(strict_types=1);

namespace Syriable\Messenger\Data;

use Illuminate\Database\Eloquent\Model;

final readonly class SendMessageData
{
    /**
     * @param  list<mixed>  $attachments  Attachment payloads (handled by attachment layer).
     */
    public function __construct(
        public Model $sender,
        public Model $recipient,
        public ?string $body = null,
        public ?string $replyToMessageId = null,
        public array $attachments = [],
    ) {}

    public function hasBody(): bool
    {
        return filled($this->body);
    }

    public function hasAttachments(): bool
    {
        return $this->attachments !== [];
    }
}
