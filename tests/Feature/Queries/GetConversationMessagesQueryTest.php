<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Syriable\Messenger\Actions\ClearConversationAction;
use Syriable\Messenger\Actions\SendMessageAction;
use Syriable\Messenger\Data\SendMessageData;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Queries\GetConversationMessagesQuery;
use Syriable\Messenger\Services\ConversationResolver;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
    Event::fake([MessageSent::class]);
    Carbon::setTestNow();
});

it('returns messages in chronological order', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $send = app(SendMessageAction::class);

    $send->execute(new SendMessageData(sender: $alice, recipient: $bob, body: 'First'));
    $send->execute(new SendMessageData(sender: $bob, recipient: $alice, body: 'Second'));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    $messages = app(GetConversationMessagesQuery::class)->execute($conversation, $alice);

    expect($messages)->toHaveCount(2)
        ->and($messages->first()->body)->toBe('First')
        ->and($messages->last()->body)->toBe('Second');
});

it('only returns messages after cleared_at for the clearing participant', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    $send = app(SendMessageAction::class);

    $send->execute(new SendMessageData(sender: $alice, recipient: $bob, body: 'Before clear'));
    $send->execute(new SendMessageData(sender: $bob, recipient: $alice, body: 'Also before'));

    $conversation = app(ConversationResolver::class)->findForPair($alice, $bob);

    app(ClearConversationAction::class)->execute($conversation, $alice);

    Carbon::setTestNow(now()->addSecond());

    $send->execute(new SendMessageData(sender: $bob, recipient: $alice, body: 'After clear'));

    Carbon::setTestNow();

    $aliceMessages = app(GetConversationMessagesQuery::class)->execute($conversation, $alice);
    $bobMessages = app(GetConversationMessagesQuery::class)->execute($conversation, $bob);

    expect($aliceMessages)->toHaveCount(1)
        ->and($aliceMessages->first()->body)->toBe('After clear')
        ->and($bobMessages)->toHaveCount(3);
});
