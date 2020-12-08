<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class GameEquipmentService implements GameEquipmentServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameEquipmentRepository $repository;
    private EquipmentServiceInterface $equipmentService;
    private StatusServiceInterface $statusService;
    private EquipmentEffectServiceInterface $EquipmentEffectService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameEquipmentRepository $repository,
        EquipmentServiceInterface $equipmentService,
        StatusServiceInterface $statusService,
        EquipmentEffectServiceInterface $EquipmentEffectService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->equipmentService = $equipmentService;
        $this->statusService = $statusService;
        $this->EquipmentEffectService = $EquipmentEffectService;
    }

    public function persist(GameEquipment $equipment): GameEquipment
    {
        $this->entityManager->persist($equipment);
        $this->entityManager->flush();

        return $equipment;
    }

    public function delete(GameEquipment $equipment): void
    {
        $this->entityManager->remove($equipment);
        $this->entityManager->flush();
    }

    public function findById(int $id): ?GameEquipment
    {
        return $this->repository->find($id);
    }

    public function createGameEquipmentFromName(string $equipmentName, Daedalus $daedalus): GameEquipment
    {
        $equipment = $this->equipmentService->findByNameAndDaedalus($equipmentName, $daedalus);

        return $this->createGameEquipment($equipment, $daedalus);
    }

    public function createGameEquipment(EquipmentConfig $equipment, Daedalus $daedalus): GameEquipment
    {
        if ($equipment instanceof ItemConfig) {
            $gameEquipment = $equipment->createGameItem();
        } else {
            $gameEquipment = $equipment->createGameEquipment();
        }

        /** @var EquipmentMechanic $mechanic */
        foreach ($equipment->getMechanics() as $mechanic) {
            switch ($mechanic->getMechanic()) {
                case EquipmentMechanicEnum::PLANT:
                    $this->initPlant($gameEquipment, $mechanic, $daedalus);
                    break;
                case EquipmentMechanicEnum::CHARGED:
                    $this->initCharged($gameEquipment, $mechanic);
                    break;
                case EquipmentMechanicEnum::DOCUMENT:
                    if ($mechanic->getContent()){
                         $this->initDocument($gameEquipment, $mechanic);
                    }
                    break;
            }
        }

        return $this->persist($gameEquipment);
    }

    // @TODO maybe remove those init functions to directly include them in createGameEquipment
    private function initPlant(GameEquipment $gameEquipment, Plant $plant, Daedalus $daedalus): GameEquipment
    {
        $plantStatus = $this->statusService->createChargeEquipmentStatus(
            EquipmentStatusEnum::PLANT_YOUNG,
            $gameEquipment,
            ChargeStrategyTypeEnum::GROWING_PLANT,
            0,
            $this->EquipmentEffectService->getPlantEffect($plant, $daedalus)->getMaturationTime()
        );

        return $gameEquipment;
    }

    private function initCharged(GameEquipment $gameEquipment, Charged $charged): GameEquipment
    {
        $chargeStatus = $this->statusService->createChargeEquipmentStatus(
            EquipmentStatusEnum::CHARGES,
            $gameEquipment,
            $charged->getChargeStrategy(),
            $charged->getStartCharge(),
            $charged->getMaxCharge()
        );

        return $gameEquipment;
    }

    private function initDocument(GameEquipment $gameEquipment, Document $document): GameEquipment
    {
        $contentStatus = new ContentStatus();
        $contentStatus
            ->setName(EquipmentStatusEnum::DOCUMENT_CONTENT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setGameEquipment($this->gameEquipment)
            ->setContent($document->getContent())
        ;

        return $gameEquipment;
    }

    //Implement accessibility to Equipment (for tool and gear)
    public function getOperationalEquipmentsByName(string $equipmentName, Player $player, string $reach): Collection
    {
        //reach can be set to inventory, shelve, shelve only or any room of the Daedalus
        return $player->getReachableEquipmentsByName($equipmentName, $reach)
            ->filter(fn (GameEquipment $gameEquipment) => $this->isOperational($gameEquipment))
            ;
    }

    public function isOperational(GameEquipment $gameEquipment): bool
    {
        return !($gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN) ||
            (($chargedStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::CHARGES)) &&
                $chargedStatus->getCharge() > 0)
        );
    }
}
