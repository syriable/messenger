<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Exceptions\InvalidAttachmentException;
use Syriable\Messenger\Exceptions\InvalidMessageException;
use Syriable\Messenger\Models\MessageAttachment;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
    Event::fake([MessageSent::class]);
    Storage::fake('local');
});

it('sends a message with attachments only', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $file = UploadedFile::fake()->image('photo.jpg');

    $message = app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        attachments: [$file],
    ));

    expect($message->attachments)->toHaveCount(1)
        ->and($message->attachments->first()->original_filename)->toBe('photo.jpg')
        ->and($message->attachments->first()->mime_type)->toBe('image/jpeg');

    Storage::disk('local')->assertExists($message->attachments->first()->path);
});

it('sends a message with both body and attachments', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $message = app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'See attached',
        attachments: [UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')],
    ));

    expect($message->body)->toBe('See attached')
        ->and($message->attachments)->toHaveCount(1);
});

it('rejects forbidden video attachments', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        attachments: [UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4')],
    ));
})->throws(InvalidAttachmentException::class);

it('rejects attachments that exceed the configured size limit', function () {
    config(['messenger.attachments.max_size_kb' => 1]);

    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        attachments: [UploadedFile::fake()->create('large.pdf', 2048, 'application/pdf')],
    ));
})->throws(InvalidAttachmentException::class);

it('rejects more attachments than allowed per message', function () {
    config(['messenger.attachments.max_count_per_message' => 1]);

    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        attachments: [
            UploadedFile::fake()->image('one.jpg'),
            UploadedFile::fake()->image('two.jpg'),
        ],
    ));
})->throws(InvalidAttachmentException::class);

it('still rejects messages without body or attachments', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
    ));
})->throws(InvalidMessageException::class);

it('persists attachment metadata in the database', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $message = app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        attachments: [UploadedFile::fake()->create('archive.zip', 50, 'application/zip')],
    ));

    expect(MessageAttachment::query()->where('message_id', $message->id)->count())->toBe(1);
});
