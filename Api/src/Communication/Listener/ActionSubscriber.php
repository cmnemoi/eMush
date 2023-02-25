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
            ActionEvent::RESULT_ACTION => 'onResultAction',
        ];
    }

    public function onResultAction(ActionEvent $event): void
    {
        $player = $event->getPlayer();
        $time = $event->getTime();

        $actionName = $event->getAction()->getActionName();

        $target = $event->getActionParameter();

        switch ($actionName) {
            case ActionEnum::DROP:
                if (!($target instanceof GameEquipment)) {
                    throw new \LogicException('a game equipment should be given');
                }

                if (in_array($target->getName(), self::COMMUNICATION_ITEMS)) {
                    $this->channelService->updatePlayerPrivateChannels($player, $actionName, $time);
                }

                return;

                // handle movement of a player
            case ActionEnum::MOVE:
                $this->channelService->updatePlayerPrivateChannels($player, $actionName, $time);
        }
    }
}
