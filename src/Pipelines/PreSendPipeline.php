<?php

declare(strict_types=1);

namespace Syriable\Messenger\Pipelines;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Syriable\Messenger\Contracts\PreSendPipe;
use Syriable\Messenger\Data\SendMessageData;

final class PreSendPipeline
{
    /**
     * @param  list<class-string<PreSendPipe>>  $pipes
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $pipes,
    ) {}

    /**
     * @param  Closure(SendMessageData): mixed  $destination
     */
    public function process(SendMessageData $data, Closure $destination): mixed
    {
        $pipes = array_map(fn (string $pipe): PreSendPipe => $this->container->make($pipe), $this->pipes);

        $validated = $this->container->make(Pipeline::class)
            ->send($data)
            ->through($pipes)
            ->thenReturn();

        if (! $validated instanceof SendMessageData) {
            throw new \RuntimeException('Pre-send pipeline must return SendMessageData.');
        }

        return $destination($validated);
    }
}
