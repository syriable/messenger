<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Syriable\Messenger\Actions\BlockConversationAction;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\ConversationBlocked;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Services\ConversationResolver;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
    Event::fake([MessageSent::class, ConversationBlocked::class]);
});

it('blocks a conversation for the participant', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    $row = app(BlockConversationAction::class)->execute($conversation, $alice);

    expect($row->isBlocked())->toBeTrue();

    Event::assertDispatched(ConversationBlocked::class);
});
