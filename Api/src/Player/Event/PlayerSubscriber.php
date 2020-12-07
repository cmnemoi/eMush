<?php

namespace Mush\Player\Event;

use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private EventDispatcherInterface $eventDispatcher;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        PlayerServiceInterface $playerService,
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->playerService = $playerService;
        $this->eventDispatcher = $eventDispatcher;
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::MODIFIER_PLAYER => 'onModifierPlayer',
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
            foreach ($player->getDaedalus()->getPlayers()->getPlayerAlive() as $daedalusPlayer) {
                if ($daedalusPlayer !== $player) {
                    $actionModifier = new ActionModifier();
                    $actionModifier->setMoralPointModifier(-1);
                    $playerEvent = new PlayerEvent($daedalusPlayer, $event->getTime());
                    $playerEvent->setActionModifier($actionModifier);
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

    public function onModifierPlayer(PlayerEvent $playerEvent)
    {
        $player = $playerEvent->getPlayer();
        $playerModifier = $playerEvent->getActionModifier();

        $this->playerService->handlePlayerModifier($player, $playerModifier, $playerEvent->getTime());

        if ($player->getHealthPoint() === 0) {
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        }

        $this->playerService->persist($player);
    }
}
