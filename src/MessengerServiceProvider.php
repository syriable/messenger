<?php

declare(strict_types=1);

namespace Syriable\Messenger;

use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Syriable\Messenger\Contracts\PreSendPipe;
use Syriable\Messenger\Events\ConversationArchived;
use Syriable\Messenger\Events\ConversationCleared;
use Syriable\Messenger\Events\ConversationMarkedAsRead;
use Syriable\Messenger\Events\ConversationSpammed;
use Syriable\Messenger\Events\ConversationStarred;
use Syriable\Messenger\Events\ConversationUnspammed;
use Syriable\Messenger\Events\ConversationUnstarred;
use Syriable\Messenger\Events\MessageReported;
use Syriable\Messenger\Events\MessageSent;
use Syriable\Messenger\Listeners\BroadcastConversationArchived;
use Syriable\Messenger\Listeners\BroadcastConversationCleared;
use Syriable\Messenger\Listeners\BroadcastConversationMarkedAsRead;
use Syriable\Messenger\Listeners\BroadcastConversationSpammed;
use Syriable\Messenger\Listeners\BroadcastConversationStarred;
use Syriable\Messenger\Listeners\BroadcastConversationUnspammed;
use Syriable\Messenger\Listeners\BroadcastConversationUnstarred;
use Syriable\Messenger\Listeners\BroadcastMessageReported;
use Syriable\Messenger\Listeners\BroadcastMessageSent;
use Syriable\Messenger\Pipelines\PreSend\EnsureMessagingAllowedPipe;
use Syriable\Messenger\Pipelines\PreSend\ValidateAttachmentsPipe;
use Syriable\Messenger\Pipelines\PreSend\ValidateMessageContentPipe;
use Syriable\Messenger\Pipelines\PreSendPipeline;
use Syriable\Messenger\Services\AttachmentStorage;
use Syriable\Messenger\Services\AttachmentValidator;
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
        ValidateAttachmentsPipe::class,
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
        $this->app->singleton('messenger', static fn ($app): MessengerManager => new MessengerManager($app));

        $this->app->alias('messenger', MessengerManager::class);

        $this->app->singleton(ConversationResolver::class);

        $this->app->singleton(AttachmentValidator::class);

        $this->app->singleton(AttachmentStorage::class);

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

    public function packageBooted(): void
    {
        Event::listen(MessageSent::class, BroadcastMessageSent::class);
        Event::listen(MessageReported::class, BroadcastMessageReported::class);
        Event::listen(ConversationArchived::class, BroadcastConversationArchived::class);
        Event::listen(ConversationMarkedAsRead::class, BroadcastConversationMarkedAsRead::class);
        Event::listen(ConversationCleared::class, BroadcastConversationCleared::class);
        Event::listen(ConversationStarred::class, BroadcastConversationStarred::class);
        Event::listen(ConversationUnstarred::class, BroadcastConversationUnstarred::class);
        Event::listen(ConversationSpammed::class, BroadcastConversationSpammed::class);
        Event::listen(ConversationUnspammed::class, BroadcastConversationUnspammed::class);
    }
}
