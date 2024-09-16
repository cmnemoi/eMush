<?php

namespace Mush\Communication\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    public const COMMUNICATION_ITEMS = [ItemEnum::ITRACKIE, ItemEnum::WALKIE_TALKIE];

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

        $privateChannelsCountBefore = $this->channelRepository->getNumberOfPlayerPrivateChannels($player);

        match ($event->getActionName()) {
            ActionEnum::DROP => $player->hasMeansOfCommunication() ? $this->channelService->updatePlayerPrivateChannels($event->getAuthor(), $event->getActionNameAsString(), $event->getTime()) : null,
            ActionEnum::MOVE => $this->channelService->updatePlayerPrivateChannels($event->getAuthor(), $event->getActionNameAsString(), $event->getTime()),
            default => null,
        };

        if ($this->shouldReloadPlayerChannels($player, $privateChannelsCountBefore)) {
            $result = $event->getActionResultOrThrow()->addDetail('reloadChannels', true);
            $event->setActionResult($result);
        }
    }

    private function shouldReloadPlayerChannels(Player $player, int $privateChannelsCountBefore): bool
    {
        $privateChannelsAfter = $this->channelRepository->getNumberOfPlayerPrivateChannels($player);

        return $privateChannelsCountBefore !== $privateChannelsAfter;
    }
}
