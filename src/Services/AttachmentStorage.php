<?php

declare(strict_types=1);

namespace Syriable\Messenger\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Syriable\Messenger\Models\Message;
use Syriable\Messenger\Models\MessageAttachment;

final class AttachmentStorage
{
    public function __construct(
        private readonly AttachmentValidator $attachmentValidator,
    ) {}

    /**
     * @param  list<UploadedFile>  $files
     * @return Collection<int, MessageAttachment>
     */
    public function storeForMessage(Message $message, array $files): Collection
    {
        $this->attachmentValidator->validateCount(count($files));

        $disk = (string) config('messenger.attachments.disk', 'local');
        $directory = trim((string) config('messenger.attachments.directory', 'messenger/attachments'), '/');

        $attachments = collect();

        foreach ($files as $file) {
            $this->attachmentValidator->validateFile($file);

            $originalFilename = $this->sanitizeFilename($file->getClientOriginalName());
            $storageDirectory = "{$directory}/{$message->conversation_id}/{$message->id}";
            $storedFilename = Str::ulid().'_'.$originalFilename;

            $path = $file->storeAs($storageDirectory, $storedFilename, $disk);

            if ($path === false) {
                throw new \RuntimeException('Failed to store the message attachment.');
            }

            $attachments->push(MessageAttachment::query()->create([
                'message_id' => $message->id,
                'disk' => $disk,
                'path' => $path,
                'original_filename' => $originalFilename,
                'mime_type' => (string) $file->getMimeType(),
                'size_bytes' => (int) $file->getSize(),
                'created_at' => now(),
            ]));
        }

        return $attachments;
    }

    public function delete(MessageAttachment $attachment): void
    {
        Storage::disk($attachment->disk)->delete($attachment->path);

        $attachment->delete();
    }

    private function sanitizeFilename(string $filename): string
    {
        $basename = basename($filename);

        return $basename !== '' ? $basename : 'attachment';
    }
}
