<?php

declare(strict_types=1);

namespace Mush\Chat\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Chat\Repository\ChannelRepositoryInterface;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
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
        if ($event->isNotAboutAnyAction([ActionEnum::DROP, ActionEnum::MOVE])) {
            return;
        }

        $player = $event->getAuthor();
        $privateChannelsCountBefore = $this->getPrivateChannelCountOf($player);
        $this->channelService->updatePlayerPrivateChannels($event->getAuthor(), $event->getActionNameAsString(), $event->getTime());

        if ($this->shouldReloadPlayerChannels($event, $privateChannelsCountBefore)) {
            $result = $event->getActionResultOrThrow()->addDetail('reloadChannels', true);
            $event->setActionResult($result);
        }
    }

    private function shouldReloadPlayerChannels(ActionEvent $event, int $privateChannelsCountBefore): bool
    {
        return $this->shouldReloadPrivateChannels($event, $privateChannelsCountBefore) || $this->shouldReloadPublicChannel($event);
    }

    private function shouldReloadPrivateChannels(ActionEvent $event, int $privateChannelsCountBefore): bool
    {
        $player = $event->getAuthor();

        return $privateChannelsCountBefore !== $this->getPrivateChannelCountOf($player);
    }

    private function shouldReloadPublicChannel(ActionEvent $event): bool
    {
        $player = $event->getAuthor();
        $action = $event->getActionName();

        $playerDoesNotHaveATalkie = $player->doesNotHaveAnyOperationalEquipment([ItemEnum::WALKIE_TALKIE, ItemEnum::ITRACKIE]);
        $playerEntersBridge = $action === ActionEnum::MOVE && $player->isIn(RoomEnum::BRIDGE);
        $playerExitsBridge = $action === ActionEnum::MOVE && $player->getPreviousRoom()?->getName() === RoomEnum::BRIDGE;

        return $playerDoesNotHaveATalkie && ($playerEntersBridge || $playerExitsBridge);
    }

    private function getPrivateChannelCountOf(Player $player): int
    {
        return $this->channelRepository->getNumberOfPlayerPrivateChannels($player);
    }
}
