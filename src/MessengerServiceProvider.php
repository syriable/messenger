<?php

declare(strict_types=1);

namespace Syriable\Messenger;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Syriable\Messenger\Contracts\PreSendPipe;
use Syriable\Messenger\Pipelines\PreSend\EnsureMessagingAllowedPipe;
use Syriable\Messenger\Pipelines\PreSend\ValidateMessageContentPipe;
use Syriable\Messenger\Pipelines\PreSendPipeline;
use Syriable\Messenger\Services\ConversationResolver;

class MessengerServiceProvider extends PackageServiceProvider
{
    /**
     * Required pre-send pipes — config can only append, not replace.
     *
     * @var list<class-string<PreSendPipe>>
     */
    private const REQUIRED_PRE_SEND_PIPES = [
        ValidateMessageContentPipe::class,
        EnsureMessagingAllowedPipe::class,
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('messenger')
            ->hasConfigFile()
            ->runsMigrations()
            ->hasMigrations([
                'create_messenger_conversations_table',
                'create_messenger_messages_table',
                'create_messenger_conversation_participants_table',
                'create_messenger_message_attachments_table',
                'create_messenger_message_reports_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('messenger', static fn (): MessengerManager => new MessengerManager);

        $this->app->alias('messenger', MessengerManager::class);

        $this->app->singleton(ConversationResolver::class);

        $this->app->singleton(PreSendPipeline::class, function ($app): PreSendPipeline {
            /** @var list<class-string<PreSendPipe>> $extra */
            $extra = (array) config('messenger.pipelines.pre_send', []);

            $pipes = self::REQUIRED_PRE_SEND_PIPES;

            foreach ($extra as $pipe) {
                $pipes[] = $pipe;
            }

            return new PreSendPipeline($app, $pipes);
        });
    }
}
