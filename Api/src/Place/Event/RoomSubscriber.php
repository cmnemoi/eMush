<?php

namespace Mush\Place\Event;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        StatusServiceInterface $statusService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->statusService = $statusService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::TREMOR => 'onTremor',
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
            RoomEvent::STARTING_FIRE => 'onStartingFire',
        ];
    }

    public function onTremor(RoomEvent $event): void
    {
        $room = $event->getRoom();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            return;
        }

        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getTremorPlayerDamage());

            $playerModifierEvent = new PlayerModifierEvent($player, -$damage, $event->getTime());
            $playerModifierEvent->setReason(EndCauseEnum::INJURY);
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::HEALTH_POINT_MODIFIER);
        }

        //@TODO add the log in case gravity is broken
        $this->roomLogService->createLog(
            LogEnum::TREMOR_GRAVITY,
            $room,
            VisibilityEnum::PUBLIC,
            'event_log',
            null,
            null,
            null,
            $event->getTime()
        );
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getRoom();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            return;
        }

        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        foreach ($room->getPlayers()->getPlayerAlive() as $player) {
            $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getElectricArcPlayerDamage());

            $playerModifierEvent = new PlayerModifierEvent($player, -$damage, $event->getTime());
            $playerModifierEvent->setReason(EndCauseEnum::ELECTROCUTED);
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::HEALTH_POINT_MODIFIER);
        }

        //@FIXME does electric arc break everythings?
        foreach ($room->getEquipments() as $equipment) {
            if (!$equipment->isBroken() &&
                !($equipment instanceof Door) &&
                !($equipment instanceof GameItem) &&
                $equipment->isBreakable()) {
                $equipmentEvent = new EquipmentEvent($equipment, VisibilityEnum::PUBLIC, $event->getTime());
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
            }
        }

        $this->roomLogService->createLog(
            LogEnum::ELECTRIC_ARC,
            $room,
            VisibilityEnum::PUBLIC,
            'event_log',
            null,
            null,
            null,
            $event->getTime()
        );
    }

    public function onStartingFire(RoomEvent $event): void
    {
        $room = $event->getRoom();

        if ($room->getType() !== PlaceTypeEnum::ROOM) {
            return;
        }

        $this->statusService->createChargeStatus(StatusEnum::FIRE,
            $event->getRoom(),
            ChargeStrategyTypeEnum::CYCLE_INCREMENT,
            null,
            VisibilityEnum::PUBLIC,
            VisibilityEnum::HIDDEN
        );
    }
}
