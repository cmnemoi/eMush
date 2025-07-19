<?php

namespace Mush\Equipment\CycleHandler;

use Mush\Daedalus\Enum\NeronFoodDestructionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class RationCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentMechanicEnum::RATION;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private DeleteEquipmentServiceInterface $deleteEquipmentService;
    private StatusServiceInterface $statusService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
        DeleteEquipmentServiceInterface $deleteEquipmentService
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
        $this->deleteEquipmentService = $deleteEquipmentService;
    }

    public function handleNewCycle(GameEquipment $gameEquipment, \DateTime $dateTime): void {}

    public function handleNewDay(GameEquipment $gameEquipment, \DateTime $dateTime): void
    {
        /** @var Ration $rationType */
        $rationType = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::RATION);
        if ($rationType === null) {
            return;
        }

        // @TODO destroy perishable item according to NERON BIOS
        $this->handleStatus($gameEquipment, $rationType);
        $this->gameEquipmentService->persist($gameEquipment);

        $this->handleBios($gameEquipment);
    }

    private function handleStatus(GameEquipment $gameRation, Ration $ration): void
    {
        // If ration is not perishable or frozen or decomposing or not in a room do nothing
        if (!$ration->getIsPerishable()
            || $gameRation->getStatuses()->exists(
                static fn (int $key, Status $status) => \in_array($status->getName(), [EquipmentStatusEnum::DECOMPOSING, EquipmentStatusEnum::FROZEN], true)
            )
            || ($gameRation->getHolder() instanceof Place && $gameRation->getPlace()->getType() !== PlaceTypeEnum::ROOM)
        ) {
            return;
        }

        if ($currentStatus = $gameRation->getStatusByName(EquipmentStatusEnum::UNSTABLE)) {
            $this->statusService->removeStatus(
                statusName: $currentStatus->getName(),
                holder: $gameRation,
                tags: [EventEnum::NEW_DAY],
                time: new \DateTime()
            );
            $nextStatus = EquipmentStatusEnum::HAZARDOUS;
        } elseif ($currentStatus = $gameRation->getStatusByName(EquipmentStatusEnum::HAZARDOUS)) {
            $this->statusService->removeStatus(
                statusName: $currentStatus->getName(),
                holder: $gameRation,
                tags: [EventEnum::NEW_DAY],
                time: new \DateTime()
            );
            $nextStatus = EquipmentStatusEnum::DECOMPOSING;
        } else {
            $nextStatus = EquipmentStatusEnum::UNSTABLE;
        }

        $this->statusService->createStatusFromName($nextStatus, $gameRation, [EventEnum::NEW_DAY], new \DateTime());
    }

    private function handleBios(GameEquipment $gameRation): void
    {
        $neron = $gameRation->getDaedalus()->getNeron();

        $statusThatCauseFoodToBeDestoyed = NeronFoodDestructionEnum::getStatus($neron->getFoodDestructionOption());
        $visibility = $gameRation->getHolder() instanceof Player ? VisibilityEnum::PRIVATE : VisibilityEnum::PUBLIC;

        if ($gameRation->hasAnyStatuses($statusThatCauseFoodToBeDestoyed)) {
            $this->deleteEquipmentService->execute(
                $gameRation,
                $visibility,
                [LogEnum::FOOD_DESTROYED_BY_NERON],
            );
        }
    }
}
