<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->randomService = $randomService;
          $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::TREMOR => 'onTremor',
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
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
                EndCauseEnum::INJURY,
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
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
                EndCauseEnum::ELECTROCUTED,
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
    }
}
