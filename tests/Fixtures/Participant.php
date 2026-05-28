<?php

declare(strict_types=1);

namespace Syriable\Messenger\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Participant extends Model
{
    protected $table = 'test_participants';

    protected $guarded = [];

    public static function migrate(): void
    {
        Schema::create('test_participants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
}
