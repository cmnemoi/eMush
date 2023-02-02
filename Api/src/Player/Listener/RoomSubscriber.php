<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\QuantityEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService
    ) {
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::TREMOR => 'onTremor',
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
            RoomEvent::DELETE_PLACE => 'onDeletePlace',
        ];
    }

    public function onTremor(RoomEvent $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getTremorPlayerDamage());

            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::HEALTH_POINT,
                -$damage,
                [EndCauseEnum::INJURY],
                $event->getTime()
            );
            $this->eventService->callEvent($playerModifierEvent, QuantityEventInterface::CHANGE_VARIABLE);
        }
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getElectricArcPlayerDamage());

            $playerModifierEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::HEALTH_POINT,
                -$damage,
                [EndCauseEnum::ELECTROCUTED],
                $event->getTime()
            );
            $this->eventService->callEvent($playerModifierEvent, QuantityEventInterface::CHANGE_VARIABLE);
        }
    }

    public function onDeletePlace(RoomEvent $event): void
    {
        foreach ($event->getPlace()->getPlayers() as $player) {
            $this->playerService->delete($player);
        }
    }
}
