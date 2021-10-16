<?php

namespace Mush\Player\Listener;

use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Event\RoomEventInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerModifierEventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
            RoomEventInterface::TREMOR => 'onTremor',
            RoomEventInterface::ELECTRIC_ARC => 'onElectricArc',
        ];
    }

    public function onTremor(RoomEventInterface $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getTremorPlayerDamage());

            $playerModifierEvent = new PlayerModifierEventInterface(
                $player,
                -$damage,
                EndCauseEnum::INJURY,
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::HEALTH_POINT_MODIFIER);
        }
    }

    public function onElectricArc(RoomEventInterface $event): void
    {
        $room = $event->getPlace();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            throw new \LogicException('place should be a room');
        }

        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getElectricArcPlayerDamage());

            $playerModifierEvent = new PlayerModifierEventInterface(
                $player,
                -$damage,
                EndCauseEnum::ELECTROCUTED,
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEventInterface::HEALTH_POINT_MODIFIER);
        }
    }
}
