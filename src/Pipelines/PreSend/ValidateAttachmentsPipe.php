<?php

declare(strict_types=1);

namespace Syriable\Messenger\Pipelines\PreSend;

use Closure;
use Illuminate\Http\UploadedFile;
use Syriable\Messenger\Contracts\PreSendPipe;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Services\AttachmentValidator;

final class ValidateAttachmentsPipe implements PreSendPipe
{
    public function __construct(
        private readonly AttachmentValidator $attachmentValidator,
    ) {}

    public function handle(SendMessageData $data, Closure $next): SendMessageData
    {
        if (! $data->hasAttachments()) {
            return $next($data);
        }

        $this->attachmentValidator->validateCount(count($data->attachments));

        foreach ($data->attachments as $attachment) {
            if (! $attachment instanceof UploadedFile) {
                throw new \InvalidArgumentException('Each attachment must be an instance of '.UploadedFile::class.'.');
            }

            $this->attachmentValidator->validateFile($attachment);
        }

        return $next($data);
    }
}
