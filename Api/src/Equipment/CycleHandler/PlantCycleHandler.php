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
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
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
    private GameConfig $gameConfig;
    private StatusServiceInterface $statusService;
    private DaedalusServiceInterface $daedalusService;
    private EquipmentEffectServiceInterface $equipmentEffectService;

    private const DISEASE_PERCENTAGE = 3;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService,
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
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function handleNewCycle($object, $daedalus, \DateTime $dateTime): void
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

        if ($this->randomService->randomPercent() <= self::DISEASE_PERCENTAGE) {
            $diseased = new Status();
            $diseased
                ->setName(EquipmentStatusEnum::PLANT_DISEASED)
                ->setVisibility(VisibilityEnum::PUBLIC)
                ->setGameEquipment($gamePlant)
            ;
        }

        $plantEffect = $this->equipmentEffectService->getPlantEffect($plantType, $daedalus);

        /** @var ChargeStatus $youngStatus */
        $youngStatus = $gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG);
        if ($youngStatus &&
            $youngStatus->getCharge() >= $plantEffect->getMaturationTime()
        ) {
            $room = $this->getRoom($gamePlant);

            $gamePlant->removeStatus($youngStatus);
            $this->statusService->delete($youngStatus);
            $this->roomLogService->createEquipmentLog(
                PlantLogEnum::PLANT_MATURITY,
                $room,
                null,
                $gamePlant,
                VisibilityEnum::PUBLIC,
                $dateTime
            );
        }

        $this->gameEquipmentService->persist($gamePlant);
    }

    public function handleNewDay($object, $daedalus, \DateTime $dateTime): void
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
            $this->statusService->delete($thirsty);
            $driedStatus = $this->statusService
                ->createCoreEquipmentStatus(EquipmentStatusEnum::PLANT_DRIED_OUT, $gamePlant)
            ;
            $gamePlant->addStatus($driedStatus);
        // If plant was dried, become hydropot
        } elseif ($gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_DRIED_OUT) !== null) {
            $this->handleDriedPlant($gamePlant, $dateTime);
        // If plant was not thirsty or dried become thirsty
        } else {
            $thirstyStatus = $this->statusService
                ->createCoreEquipmentStatus(EquipmentStatusEnum::PLANT_THIRSTY, $gamePlant)
            ;
            $gamePlant->addStatus($thirstyStatus);
        }
    }

    private function handleDriedPlant(GameItem $gamePlant, \DateTime $dateTime): void
    {
        $room = $this->getRoom($gamePlant);

        // Create a new hydropot
        /** @var GameItem $hydropot */
        $hydropot = $this->gameEquipmentService->createGameEquipmentFromName(ItemEnum::HYDROPOT, $room->getDaedalus());

        if ($player = $gamePlant->getPlayer()) {
            $gamePlant->setPlayer(null);
            $hydropot->setPlayer($player);
        } else {
            $gamePlant->setRoom(null);
            $hydropot->setRoom($room);
        }
        $this->roomLogService->createEquipmentLog(
            PlantLogEnum::PLANT_DEATH,
            $room,
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
        $room = $this->getRoom($gamePlant);

        /** @var GameItem $gameFruit */
        $gameFruit = $this->gameEquipmentService->createGameEquipment($plantType->getFruit(), $room->getDaedalus());

        $gameFruit->setRoom($room);
        

        $this->gameEquipmentService->persist($gameFruit);

        $this->roomLogService->createEquipmentLog(
            PlantLogEnum::PLANT_NEW_FRUIT,
            $room,
            null,
            $gameFruit,
            VisibilityEnum::PUBLIC,
            $dateTime
        );
    }

    private function addOxygen(GameItem $gamePlant, PlantEffect $plantEffect): void
    {
        $daedalus = $this->getRoom($gamePlant)->getDaedalus();
        //Add Oxygen
        if (($oxygen = $plantEffect->getOxygen())) {
            $this->daedalusService->changeOxygenLevel($daedalus, 1);
        }
    }

    private function getRoom(GameItem $gamePlant): Room
    {
        if ($player = $gamePlant->getPlayer()) {
            $room = $player->getRoom();
        } else {
            $room = $gamePlant->getRoom();
        }

        if ($room === null) {
            throw new \LogicException('Cannot find room of game plant');
        }

        return $room;
    }
}
