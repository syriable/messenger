<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Actions\SpamConversationAction;
use Syriable\Messenger\Actions\UnspamConversationAction;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\ConversationSpammed;
use Syriable\Messenger\Events\ConversationUnspammed;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Exceptions\MessagingNotAllowedException;
use Syriable\Messenger\Services\ConversationResolver;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
    Event::fake([MessageSent::class, ConversationSpammed::class, ConversationUnspammed::class]);
});

it('marks a conversation as spam for the participant', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    $row = app(SpamConversationAction::class)->execute($conversation, $alice);

    expect($row->isSpammed())->toBeTrue();

    Event::assertDispatched(ConversationSpammed::class);
});

it('rejects messages when either participant has marked spam', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    app(SpamConversationAction::class)->execute($conversation, $alice);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $bob,
        recipient: $alice,
        body: 'Blocked reply',
    ));
})->throws(MessagingNotAllowedException::class);

it('allows messaging again after unspam', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $alice,
        recipient: $bob,
        body: 'Hello',
    ));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    app(SpamConversationAction::class)->execute($conversation, $alice);
    app(UnspamConversationAction::class)->execute($conversation, $alice);

    app(SendMessageAction::class)->execute(new SendMessageData(
        sender: $bob,
        recipient: $alice,
        body: 'Back online',
    ));

    Event::assertDispatched(ConversationUnspammed::class);

    expect($conversation->refresh()->latestMessage?->body)->toBe('Back online');
});
