<?php

declare(strict_types=1);

namespace Mush\Chat\Services;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Event\ChannelEvent;
use Mush\Chat\Repository\ChannelRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;

final readonly class CreateNeronChannelForPlayerService
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private EventServiceInterface $eventService,
    ) {}

    public function execute(Player $player): void
    {
        $channel = new Channel();
        $channel
            ->setScope(ChannelScopeEnum::NERON)
            ->setDaedalus($player->getDaedalus()->getDaedalusInfo());
        $this->channelRepository->save($channel);

        $event = new ChannelEvent($channel, [], new \DateTime(), $player);
        $this->eventService->callEvent($event, ChannelEvent::NEW_CHANNEL);
    }
}
