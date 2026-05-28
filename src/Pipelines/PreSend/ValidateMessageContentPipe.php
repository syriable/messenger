<?php

declare(strict_types=1);

namespace Syriable\Messenger\Pipelines\PreSend;

use Closure;
use Syriable\Messenger\Contracts\PreSendPipe;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Exceptions\InvalidMessageException;

final class ValidateMessageContentPipe implements PreSendPipe
{
    public function handle(SendMessageData $data, Closure $next): SendMessageData
    {
        if (! $data->hasBody() && ! $data->hasAttachments()) {
            throw new InvalidMessageException('A message must include a body, attachments, or both.');
        }

        return $next($data);
    }
}
