<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Messenger;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
    Event::fake([MessageSent::class]);
});

it('sends a message via the messenger facade helper', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $message = Messenger::send(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Via facade',
    ));

    expect($message->body)->toBe('Via facade');

    Event::assertDispatched(MessageSent::class);
});
