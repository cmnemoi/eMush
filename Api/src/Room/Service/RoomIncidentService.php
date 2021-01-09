<?php

namespace Mush\Room\Service;

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
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class RoomIncidentService implements RoomIncidentServiceInterface
{
    private StatusServiceInterface $statusService;
    private RoomServiceInterface $roomService;
    private RandomServiceInterface $randomService;
    private GameConfig $gameConfig;

    /**
     * RoomService constructor.
     */
    public function __construct(
        GameEquipmentServiceInterface $equipmentService,
        StatusServiceInterface $statusService,
        RoomServiceInterface $roomService,
        RandomServiceInterface $randomService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->equipmentService = $equipmentService;
        $this->statusService = $statusService;
        $this->roomService = $roomService;
        $this->randomService = $randomService;
        $this->gameConfig = $gameConfigService->getConfig();
    }


    public function handleIncident(Room $room): Room
    {
        

        return $room;
    }

    public function handleTremor(Room $room): Room
    {
        if ($this->randomService->isSuccessfull($this->gameConfig->getDifficultyConfig()->getTremorRate())){
            foreach ($room->getPlayers() as $player){

                $actionModifier = new Modifier();
                $actionModifier
                    ->setDelta($this->randomService->random(1,3))
                    ->setTarget(ModifierTargetEnum::HEALTH_POINT)
                    ->setReason(EndCauseEnum::ELECTROCUTED)
                ;
                $playerEvent = new PlayerEvent($player, $date);
                $playerEvent->setModifier($actionModifier);
                $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
                
            }
        }

        return $room;
    }

    public function handleElectricArc(Room $room): Room
    {
        if ($this->randomService->isSuccessfull($this->gameConfig->getDifficultyConfig()->getElectricArcRate())){

        }

        return $room;
    }


    public function handleFire(Room $room): Room
    {
        $fireStatus = $room->getStatusByName(StatusEnum::FIRE);

        if ($fireStatus && !$fireStatus instanceof ChargeStatus) {
            throw new \LogicException('Fire is not a ChargedStatus');
        }

        if ($fireStatus && $fireStatus->getCharge() === 0) {
            $this->propagateFire($room);
        } elseif ($this->randomService->isSuccessfull($this->gameConfig->getDifficultyConfig()->getStartingFireRate())) {
            $fireStatus = $this->startFire($room);

            //primary fire deal damage on the first cycle
            $fireStatus->setCharge(0);
            $this->propagateFire($room);
        }

        return $room;
    }

    public function startFire(Room $room): ChargeStatus
    {
        if (!$room->hasStatus(StatusEnum::FIRE)) {
            $fireStatus = $this->statusService->createChargeRoomStatus(StatusEnum::FIRE,
                    $room,
                    ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                    VisibilityEnum::PUBLIC,
                    1);
        } else {
            $fireStatus = $room->getStatusByName(StatusEnum::FIRE);

            if (!$fireStatus instanceof ChargeStatus) {
                throw new \LogicException('Fire is not a charged Status');
            }

            $fireStatus->setCharge(0);
        }

        $this->roomService->persist($room);

        return $fireStatus;
    }

    public function propagateFire(Room $room): Room
    {
        foreach ($room->getDoors() as $door) {
            $adjacentRoom = $door->getOtherRoom($room);

            if ($this->randomService->isSuccessfull($this->gameConfig->getDifficultyConfig()->getPropagatingFireRate())) {
                $this->startFire($adjacentRoom);
            }
        }
        $this->roomService->persist($room);

        return $room;
    }
}
