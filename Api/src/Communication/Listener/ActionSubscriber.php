<?php

declare(strict_types=1);

namespace Mush\Communication\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChannelRepository $channelRepository,
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
        $player = $event->getAuthor();

        $privateChannelsCountBefore = $this->getPrivateChannelCountOf($player);

        match ($event->getActionName()) {
            ActionEnum::DROP => $player->hasMeansOfCommunication() ? $this->channelService->updatePlayerPrivateChannels($event->getAuthor(), $event->getActionNameAsString(), $event->getTime()) : null,
            ActionEnum::MOVE => $this->channelService->updatePlayerPrivateChannels($event->getAuthor(), $event->getActionNameAsString(), $event->getTime()),
            default => null,
        };

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
        $playerExitsBridge = $action === ActionEnum::MOVE && $player->getPreviousRoomOrThrow()->getName() === RoomEnum::BRIDGE;

        return $playerDoesNotHaveATalkie && ($playerEntersBridge || $playerExitsBridge);
    }

    private function getPrivateChannelCountOf(Player $player): int
    {
        return $this->channelRepository->getNumberOfPlayerPrivateChannels($player);
    }
}
