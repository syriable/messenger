<?php

declare(strict_types=1);

use Syriable\Messenger\Support\ParticipantPairHash;
use Syriable\Messenger\Tests\Fixtures\Participant;

beforeEach(function () {
    Participant::migrate();
});

it('produces the same hash regardless of participant order', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);

    expect(ParticipantPairHash::for($alice, $bob))
        ->toBe(ParticipantPairHash::for($bob, $alice));
});

it('produces different hashes for different pairs', function () {
    $alice = Participant::query()->create(['name' => 'Alice']);
    $bob = Participant::query()->create(['name' => 'Bob']);
    $carol = Participant::query()->create(['name' => 'Carol']);

    expect(ParticipantPairHash::for($alice, $bob))
        ->not->toBe(ParticipantPairHash::for($alice, $carol));
});
