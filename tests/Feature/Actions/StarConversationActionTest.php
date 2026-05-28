<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Actions\StarConversationAction;
use Syriable\Messenger\Actions\UnstarConversationAction;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\ConversationStarred;
use Syriable\Messenger\Events\ConversationUnstarred;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Services\ConversationResolver;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
    Event::fake([MessageSent::class, ConversationStarred::class, ConversationUnstarred::class]);
});

it('stars a conversation for the participant', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    $row = app(StarConversationAction::class)->execute($conversation, $alice);

    expect($row->isStarred())->toBeTrue();

    Event::assertDispatched(ConversationStarred::class);
});

it('unstars a conversation for the participant', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    app(StarConversationAction::class)->execute($conversation, $alice);

    $row = app(UnstarConversationAction::class)->execute($conversation, $alice);

    expect($row->isStarred())->toBeFalse();

    Event::assertDispatched(ConversationUnstarred::class);
});
