<?php

declare(strict_types=1);

namespace Mush\Chat\Listener;

use Mush\Chat\Event\MessageEvent;
use Mush\Chat\Services\CreateNeronAnswerToQuestionService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class MessageEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CreateNeronAnswerToQuestionService $createNeronAnswerToQuestion,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::NEW_MESSAGE => 'onNewMessage',
        ];
    }

    public function onNewMessage(MessageEvent $event): void
    {
        $message = $event->getMessage();
        $channel = $message->getChannel();

        if ($channel->isNeronChannel()) {
            $this->createNeronAnswerToQuestion->execute($message->getMessage(), $channel);
        }
    }
}
