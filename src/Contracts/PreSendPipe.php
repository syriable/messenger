<?php

declare(strict_types=1);

namespace Syriable\Messenger\Contracts;

use Closure;
use Syriable\Messenger\Data\SendMessageData;

interface PreSendPipe
{
    public function handle(SendMessageData $data, Closure $next): SendMessageData;
}
