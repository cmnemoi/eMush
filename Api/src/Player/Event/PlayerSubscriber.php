<?php

namespace Mush\Player\Event;

use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        PlayerServiceInterface $playerService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->playerService = $playerService;
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
        ];
    }

    public function onNewPlayer(PlayerEvent $event)
    {
        $player = $event->getPlayer();
        $this->roomLogService->createPlayerLog(
            LogEnum::AWAKEN,
            $player->getRoom(),
            $player,
            VisibilityEnum::PUBLIC
        );
    }

    public function onDeathPlayer(PlayerEvent $event)
    {
        $player = $event->getPlayer();

        if ($player->getEndStatus() !== EndCauseEnum::DEPRESSION) {
            /** @var Player $daedalusPlayer */
            foreach ($player->getDaedalus()->getPlayers() as $daedalusPlayer) {
                if ($daedalusPlayer !== $player) {
                    $daedalusPlayer->addMoralPoint(-1);
                    $this->roomLogService->createQuantityLog(
                        LogEnum::LOSS_MORAL_POINT,
                        $daedalusPlayer->getRoom(),
                        $daedalusPlayer,
                        VisibilityEnum::PRIVATE,
                        1
                    );
                    $this->playerService->persist($daedalusPlayer);
                }
            }
        }

        $player = $event->getPlayer();
        $this->roomLogService->createPlayerLog(
            LogEnum::DEATH,
            $player->getRoom(),
            $player,
            VisibilityEnum::PUBLIC
        );
    }
}
