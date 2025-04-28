<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\MetaGame\Entity\Skin\Skin;
use Mush\MetaGame\Entity\Skin\SkinableEntityInterface;
use Mush\MetaGame\Entity\Skin\SkinSlot;
use Mush\Place\Service\PlaceServiceInterface;

final class SkinService implements SkinServiceInterface
{
    private EntityManagerInterface $entityManager;
    private PlaceServiceInterface $placeService;
    private GameEquipmentServiceInterface $equipmentService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PlaceServiceInterface $placeService,
        GameEquipmentServiceInterface $equipmentService,
    ) {
        $this->entityManager = $entityManager;
        $this->placeService = $placeService;
        $this->equipmentService = $equipmentService;
    }

    public function applySkinToAllDaedalus(Skin $skin, Daedalus $daedalus): void
    {
        $skinConfig = $skin->getSkinSlotConfig();

        $skinableClass = $skinConfig->getSkinableClass();
        $skinableName = $skinConfig->getSkinableName();

        switch ($skinableClass) {
            case 'place':
                $place = $this->placeService->findByNameAndDaedalus($skinableName, $daedalus);

                if ($place !== null) {
                    $this->applySkinToEntity($place, $skin);
                }

                break;

            case 'equipment':
                if ($skinableName === EquipmentEnum::PATROL_SHIP) {
                    $equipments = [];
                    foreach (EquipmentEnum::getPatrolShips() as $patrolShipName) {
                        $equipments = array_merge($equipments, $this->equipmentService->findByNameAndDaedalus($patrolShipName, $daedalus)->toArray());
                    }
                } else {
                    $equipments = $this->equipmentService->findByNameAndDaedalus($skinableName, $daedalus);
                }

                foreach ($equipments as $equipment) {
                    $this->applySkinToEntity($equipment, $skin);
                }

                break;
        }
    }

    public function applySkinToEntity(SkinableEntityInterface $skinableEntity, Skin $skin): void
    {
        $skinConfig = $skin->getSkinSlotConfig();
        $skinSlotName = $skinConfig->getName();

        $skinSlot = $skinableEntity->getSkinSlotByName($skinSlotName);

        if ($skinSlot !== null) {
            $skinSlot->setSkin($skin);
            $this->entityManager->persist($skinSlot);
        }
    }

    private function persist(SkinSlot $skinSlot): SkinSlot
    {
        $this->entityManager->persist($skinSlot);
        $this->entityManager->flush();

        return $skinSlot;
    }
}
