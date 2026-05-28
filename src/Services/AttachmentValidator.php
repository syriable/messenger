<?php

declare(strict_types=1);

namespace Syriable\Messenger\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Syriable\Messenger\Exceptions\InvalidAttachmentException;

final class AttachmentValidator
{
    public function validateCount(int $count): void
    {
        $max = (int) config('messenger.attachments.max_count_per_message', 10);

        if ($count > $max) {
            throw new InvalidAttachmentException("A message may not include more than {$max} attachments.");
        }
    }

    public function validateFile(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new InvalidAttachmentException('The attachment upload is invalid.');
        }

        $maxBytes = (int) config('messenger.attachments.max_size_kb', 10240) * 1024;
        $size = $file->getSize();

        if ($size === false || $size > $maxBytes) {
            throw new InvalidAttachmentException('The attachment exceeds the maximum allowed size.');
        }

        $mimeType = (string) $file->getMimeType();

        /** @var list<string> $forbidden */
        $forbidden = (array) config('messenger.attachments.forbidden_mime_types', []);

        foreach ($forbidden as $pattern) {
            if (Str::is($pattern, $mimeType)) {
                throw new InvalidAttachmentException("The attachment type [{$mimeType}] is not allowed.");
            }
        }

        /** @var list<string> $allowed */
        $allowed = (array) config('messenger.attachments.allowed_mime_types', []);

        if ($allowed !== [] && ! in_array($mimeType, $allowed, true)) {
            throw new InvalidAttachmentException("The attachment type [{$mimeType}] is not allowed.");
        }
    }
}
