<?php

declare(strict_types=1);

namespace Mush\Chat\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Chat\Repository\ChannelRepositoryInterface;
use Mush\Chat\Services\ChannelServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private ChannelServiceInterface $channelService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPostAction(ActionEvent $event): void
    {
        if (!$event->isIn([ActionEnum::DROP, ActionEnum::MOVE, ActionEnum::GO_BERSERK])) {
            return;
        }

        $this->channelService->updatePlayerPrivateChannels($event->getAuthor(), $event->getActionNameAsString(), $event->getTime());
    }
}
