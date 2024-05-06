<?php

namespace Mush\Game\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\SpawnEquipmentEventConfig;
use Mush\Game\Repository\EventConfigRepository;

class SpawnEquipmentEventConfigDataLoader extends ConfigDataLoader
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly EventConfigRepository $spawnVariableEventConfigRepository
    ) {
        parent::__construct($entityManager);
    }

    public function loadConfigsData(): void
    {
        foreach (EventConfigData::$spawnEquipmentEventConfigData as $spawnEquipmentEventConfigData) {
            /** @var null|SpawnEquipmentEventConfig $spawnEquipmentEventConfig */
            $spawnEquipmentEventConfig = $this->spawnVariableEventConfigRepository->findOneBy(['name' => $spawnEquipmentEventConfigData['name']]);
            if (!$spawnEquipmentEventConfig) {
                $spawnEquipmentEventConfig = new SpawnEquipmentEventConfig(...$spawnEquipmentEventConfigData);
            } else {
                $spawnEquipmentEventConfig->updateFromConfigData($spawnEquipmentEventConfigData);
            }

            $this->entityManager->persist($spawnEquipmentEventConfig);
        }

        $this->entityManager->flush();
    }
}
