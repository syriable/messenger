<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Syriable\Messenger\Exceptions\InvalidAttachmentException;
use Syriable\Messenger\Services\AttachmentValidator;

it('allows configured mime types', function () {
    $validator = new AttachmentValidator;

    $validator->validateFile(UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'));

    expect(true)->toBeTrue();
});

it('rejects mime types outside the allow list', function () {
    $validator = new AttachmentValidator;

    $validator->validateFile(UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload'));
})->throws(InvalidAttachmentException::class);
