<?php

namespace Mush\Communication\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    public const COMMUNICATION_ITEMS = [ItemEnum::ITRACKIE, ItemEnum::WALKIE_TALKIE];
    private ChannelServiceInterface $channelService;

    public function __construct(
        ChannelServiceInterface $channelService,
    ) {
        $this->channelService = $channelService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::POST_ACTION => 'onResultAction',
        ];
    }

    public function onResultAction(ActionEvent $event): void
    {
        $player = $event->getAuthor();
        $time = $event->getTime();

        $actionName = $event->getActionConfig()->getActionName();

        $target = $event->getActionTarget();

        switch ($actionName) {
            case ActionEnum::DROP:
                if (!$target instanceof GameEquipment) {
                    throw new \LogicException('a game equipment should be given');
                }

                if (\in_array($target->getName(), self::COMMUNICATION_ITEMS, true)) {
                    $this->channelService->updatePlayerPrivateChannels($player, $actionName->value, $time);
                }

                return;

                // handle movement of a player
            case ActionEnum::MOVE:
                $this->channelService->updatePlayerPrivateChannels($player, $actionName->value, $time);
        }
    }
}
