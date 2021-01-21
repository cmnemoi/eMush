<?php

namespace Mush\Room\Event;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Room\Service\RoomEventServiceInterface;
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
    private RoomEventServiceInterface $roomEventService;
    private StatusServiceInterface $statusService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        RoomEventServiceInterface $roomEventService,
        StatusServiceInterface $statusService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->roomEventService = $roomEventService;
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
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        foreach ($room->getPlayers() as $player) {
            $damage = $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getTremorPlayerDamage());
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta(-$damage)
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ;
            $playerEvent = new PlayerEvent($player, $event->getTime());
            $playerEvent->setReason(EndCauseEnum::INJURY);
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }

        //@TODO add the log in case gravity is broken
        $this->roomLogService->createRoomLog(
            LogEnum::TREMOR_GRAVITY,
            $room,
            VisibilityEnum::PUBLIC,
            $event->getTime()
        );
    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room = $event->getRoom();
        $difficultyConfig = $room->getDaedalus()->getGameConfig()->getDifficultyConfig();
        foreach ($room->getPlayers() as $player) {
            $damage = $this->randomService->getSingleRandomElementFromProbaArray($difficultyConfig->getElectricArcPlayerDamage());
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta(-$damage)
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ;
            $playerEvent = new PlayerEvent($player, $event->getTime());
            $playerEvent->setReason(EndCauseEnum::ELECTROCUTED);
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }

        foreach ($room->getEquipments() as $equipment) {
            if (!$equipment->isBroken() &&
                !($equipment instanceof Door) &&
                !($equipment instanceof GameItem) &&
                $equipment->getEquipment()->getBreakableRate() > 0) {
                $equipmentEvent = new EquipmentEvent($equipment, VisibilityEnum::PUBLIC, $event->getTime());
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
            }
        }

        $this->roomLogService->createRoomLog(
            LogEnum::ELECTRIC_ARC,
            $room,
            VisibilityEnum::PUBLIC,
            $event->getTime()
        );
    }

    public function onStartingFire(RoomEvent $event): void
    {
        $room = $event->getRoom();
        if (!$room->hasStatus(StatusEnum::FIRE)) {
            $fireStatus = $this->statusService->createChargeRoomStatus(StatusEnum::FIRE,
                $event->getRoom(),
                ChargeStrategyTypeEnum::CYCLE_INCREMENT,
                VisibilityEnum::PUBLIC,
                VisibilityEnum::HIDDEN
            );
        }
    }
}
