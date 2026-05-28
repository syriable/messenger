<?php

declare(strict_types=1);

use Illuminate\Broadcasting\PendingBroadcast;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastingFactory;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Broadcasts\MessageSentBroadcast;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
});

it('does not broadcast when realtime is disabled', function () {
    config(['messenger.broadcasting.enabled' => false]);

    $broadcasting = Mockery::mock(BroadcastingFactory::class);
    $broadcasting->shouldNotReceive('event');
    app()->instance(BroadcastingFactory::class, $broadcasting);

    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));
});

it('broadcasts after sending a message when realtime is enabled', function () {
    config(['messenger.broadcasting.enabled' => true]);

    $pending = Mockery::mock(PendingBroadcast::class);

    $broadcasting = Mockery::mock(BroadcastingFactory::class);
    $broadcasting->shouldReceive('event')
        ->once()
        ->withArgs(
            fn ($event) => $event instanceof MessageSentBroadcast && $event->broadcastAs() === 'message.sent'
        )
        ->andReturn($pending);

    app()->instance(BroadcastingFactory::class, $broadcasting);

    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));
});
