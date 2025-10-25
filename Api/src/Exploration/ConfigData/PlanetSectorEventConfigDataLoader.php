<?php

namespace Mush\Exploration\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\Repository\EventConfigRepository;

class PlanetSectorEventConfigDataLoader extends ConfigDataLoader
{
    private EventConfigRepository $planetSectorEventConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventConfigRepository $planetSectorEventConfigRepository,
    ) {
        parent::__construct($entityManager);
        $this->planetSectorEventConfigRepository = $planetSectorEventConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (PlanetSectorEventConfigData::getAll() as $planetSectorEventConfigDto) {
            /** @var null|PlanetSectorEventConfig $planetSectorEventConfig */
            $planetSectorEventConfig = $this->planetSectorEventConfigRepository->findOneBy(['name' => $planetSectorEventConfigDto->name]);
            if ($planetSectorEventConfig === null) {
                $planetSectorEventConfig = PlanetSectorEventConfig::fromDto($planetSectorEventConfigDto);
            } else {
                $planetSectorEventConfig->updateFromDto($planetSectorEventConfigDto);
            }

            $this->entityManager->persist($planetSectorEventConfig);
        }

        $this->entityManager->flush();
    }
}
