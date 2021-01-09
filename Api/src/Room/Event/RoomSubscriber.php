<?php

namespace Mush\Room\Event;

use Mush\Room\Service\RoomServiceInterface;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private RoomServiceInterface $roomService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(RoomServiceInterface $roomService, EventDispatcherInterface $eventDispatcher)
    {
        $this->roomService = $roomService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::TREMOR => 'onTremor',
            RoomEvent::ELECTRIC_ARC => 'onElectricArc',
            RoomEvent::STARTING_FIRE => 'onStartingFire',
            RoomEvent::FIRE => 'onFire',
        ];
    }

    public function onTremor(RoomEvent $event): void
    {
        $room=$event->getRoom();
        foreach ($room->getPlayers() as $player){

            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta($this->randomService->random(1,3))
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
                ->setReason(EndCauseEnum::INJURY)
            ;
            $playerEvent = new PlayerEvent($player, $event->getTime());
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);  
        }

        $this->roomLogService->createRoomLog(
            LogEnum::TREMOR,
            $room,
            null,
            VisibilityEnum::PUBLIC,
            $event->getTime()
        );

    }

    public function onElectricArc(RoomEvent $event): void
    {
        $room=$event->getRoom();
        foreach ($room->getPlayers() as $player){
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta(3)
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
                ->setReason(EndCauseEnum::ELECTROCUTED)
            ;
            $playerEvent = new PlayerEvent($player, $event->getTime());
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);  
        }

        foreach ($room->getEquipments() as $equipment){
            if (!$equipment->isBroken() &&
                $equipment->getEquipment()->getBreakableRate()>0)
                {
                    $this->statusService->createCoreEquipmentStatus(EquipmentStatusEnum::BROKEN, $equipment);
                    $this->gameEquipmentService->persist($equipment);
                }
        }

        $this->roomLogService->createRoomLog(
            LogEnum::ELECTRIC_ARC,
            $room,
            null,
            VisibilityEnum::PUBLIC,
            $event->getTime()
        );
    }

    public function onStartingFire(RoomEvent $event): void
    {
        $fireStatus = $this->statusService->createChargeRoomStatus(StatusEnum::FIRE,
                    $event->getRoom(),
                    ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                    VisibilityEnum::PUBLIC,
                    1);

        if ($event->getReason()===RoomEventEnum::CYCLE_FIRE){
            $fireStatus->setCharge(0);
        }
    }

    public function onFire(RoomEvent $event): void
    {
        $room=$event->getRoom();
        foreach ($room->getPlayers() as $player){
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta(2)
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
                ->setReason(EndCauseEnum::BURNT)
            ;
            $playerEvent = new PlayerEvent($player, $event->getTime());
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);  
        }

        foreach ($room->getEquipments() as $equipment){
            if (!$equipment->isBroken() &&
                $equipment->getEquipment()->getBreakableRate()>0)
                {
                    $this->statusService->createCoreEquipmentStatus(EquipmentStatusEnum::BROKEN, $equipment);
                    $this->gameEquipmentService->persist($equipment);
                }
        }
    }
}
