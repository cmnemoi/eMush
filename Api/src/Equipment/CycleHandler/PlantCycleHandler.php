<?php

namespace Mush\Equipment\CycleHandler;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class PlantCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentMechanicEnum::PLANT;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private StatusServiceInterface $statusService;
    private DaedalusServiceInterface $daedalusService;
    private EquipmentEffectServiceInterface $equipmentEffectService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        StatusServiceInterface $statusService,
        DaedalusServiceInterface $daedalusService,
        EquipmentEffectServiceInterface $equipmentEffectService
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->equipmentEffectService = $equipmentEffectService;
        $this->statusService = $statusService;
        $this->daedalusService = $daedalusService;
    }

    public function handleNewCycle($object, $daedalus, \DateTime $dateTime, array $context = []): void
    {
        /** @var GameItem $gamePlant */
        $gamePlant = $object;

        if (!$gamePlant instanceof GameEquipment) {
            return;
        }

        $plantType = $gamePlant->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT);

        if (!$plantType instanceof Plant) {
            return;
        }

        $plantEffect = $this->equipmentEffectService->getPlantEffect($plantType, $daedalus);

        /** @var ChargeStatus $youngStatus */
        $youngStatus = $gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG);
        if ($youngStatus &&
            $youngStatus->getCharge() >= $plantEffect->getMaturationTime()
        ) {
            $place = $gamePlant->getCurrentPlace();

            $gamePlant->removeStatus($youngStatus);
            $this->roomLogService->createEquipmentLog(
                PlantLogEnum::PLANT_MATURITY,
                $place,
                null,
                $gamePlant,
                VisibilityEnum::PUBLIC,
                $dateTime
            );
        }

        $diseaseRate = $daedalus->getGameConfig()->getDifficultyConfig()->getPlantDiseaseRate();

        if ($this->randomService->isSuccessful($diseaseRate)) {
            $this->statusService->createCoreStatus(EquipmentStatusEnum::PLANT_DISEASED, $gamePlant);
        }

        $this->gameEquipmentService->persist($gamePlant);
    }

    public function handleNewDay($object, $daedalus, \DateTime $dateTime, array $context = []): void
    {
        /** @var GameItem $gamePlant */
        $gamePlant = $object;

        if (!$gamePlant instanceof GameEquipment) {
            return;
        }

        $plantType = $gamePlant->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT);

        if (!$plantType instanceof Plant) {
            return;
        }

        $plantEffect = $this->equipmentEffectService->getPlantEffect($plantType, $daedalus);

        $plantStatus = $gamePlant->getStatuses();

        //If plant is young, dried or diseased, do not produce oxygen
        if ($plantStatus->filter(
            fn (Status $status) => in_array(
                $status->getName(),
                [
                    EquipmentStatusEnum::PLANT_DRIED_OUT,
                    EquipmentStatusEnum::PLANT_DISEASED,
                    EquipmentStatusEnum::PLANT_YOUNG,
                ]
            )
        )->isEmpty()
        ) {
            $this->addOxygen($gamePlant, $plantEffect);
            if ($plantStatus->filter(fn (Status $status) => in_array(
                $status->getName(),
                [EquipmentStatusEnum::PLANT_THIRSTY]
            ))->isEmpty()
            ) {
                $this->addFruit($gamePlant, $plantType, $dateTime);
            }
        }

        $this->handleStatus($gamePlant, $dateTime);

        $this->gameEquipmentService->persist($gamePlant);
    }

    private function handleStatus(GameItem $gamePlant, \DateTime $dateTime): void
    {
        // If plant was thirsty, become dried
        if (($thirsty = $gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY)) !== null) {
            $gamePlant->removeStatus($thirsty);
            $driedStatus = $this->statusService
                ->createCoreStatus(EquipmentStatusEnum::PLANT_DRIED_OUT, $gamePlant)
            ;
            $gamePlant->addStatus($driedStatus);
        // If plant was dried, become hydropot
        } elseif ($gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_DRIED_OUT) !== null) {
            $this->handleDriedPlant($gamePlant, $dateTime);
        // If plant was not thirsty or dried become thirsty
        } else {
            $thirstyStatus = $this->statusService
                ->createCoreStatus(EquipmentStatusEnum::PLANT_THIRSTY, $gamePlant)
            ;
            $gamePlant->addStatus($thirstyStatus);
        }
    }

    private function handleDriedPlant(GameItem $gamePlant, \DateTime $dateTime): void
    {
        $place = $gamePlant->getCurrentPlace();

        // Create a new hydropot
        /** @var GameItem $hydropot */
        $hydropot = $this->gameEquipmentService->createGameEquipmentFromName(ItemEnum::HYDROPOT, $place->getDaedalus());

        if ($player = $gamePlant->getPlayer()) {
            $gamePlant->setPlayer(null);
            $hydropot->setPlayer($player);
        } else {
            $gamePlant->setPlace(null);
            $hydropot->setPlace($place);
        }
        $this->roomLogService->createEquipmentLog(
            PlantLogEnum::PLANT_DEATH,
            $place,
            null,
            $gamePlant,
            VisibilityEnum::PUBLIC,
            $dateTime
        );

        $gamePlant->removeLocation();
        $this->gameEquipmentService->delete($gamePlant); // Remove plant
        $this->gameEquipmentService->persist($hydropot); // Add hydropot
    }

    private function addFruit(GameItem $gamePlant, Plant $plantType, \DateTime $dateTime): void
    {
        //If plant is young, thirsty, dried or diseased, do not produce fruit
        if (!$gamePlant->getStatuses()
            ->filter(
                fn (Status $status) => in_array(
                    $status->getName(),
                    [
                        EquipmentStatusEnum::PLANT_DRIED_OUT,
                        EquipmentStatusEnum::PLANT_DISEASED,
                        EquipmentStatusEnum::PLANT_YOUNG,
                        EquipmentStatusEnum::PLANT_THIRSTY,
                    ]
                )
            )
            ->isEmpty()
        ) {
            return;
        }
        // If plant is not in a room, it is in player inventory
        $place = $gamePlant->getCurrentPlace();

        /** @var GameItem $gameFruit */
        $gameFruit = $this->gameEquipmentService->createGameEquipment($plantType->getFruit(), $place->getDaedalus());

        $gameFruit->setPlace($place);

        $this->gameEquipmentService->persist($gameFruit);

        $this->roomLogService->createEquipmentLog(
            PlantLogEnum::PLANT_NEW_FRUIT,
            $place,
            null,
            $gameFruit,
            VisibilityEnum::PUBLIC,
            $dateTime
        );
    }

    private function addOxygen(GameItem $gamePlant, PlantEffect $plantEffect): void
    {
        $daedalus = $gamePlant->getCurrentPlace()->getDaedalus();
        //Add Oxygen
        if (($oxygen = $plantEffect->getOxygen())) {
            $this->daedalusService->changeOxygenLevel($daedalus, 1);
        }
    }
}
