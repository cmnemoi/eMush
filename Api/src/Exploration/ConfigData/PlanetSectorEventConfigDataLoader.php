<?php

namespace Mush\Exploration\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\EventConfigData;
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
        foreach (EventConfigData::$dataArray as $planetSectorEventConfigData) {
            if ($planetSectorEventConfigData['type'] !== 'planet_sector_event_config') {
                continue;
            }

            $planetSectorEventConfig = $this->planetSectorEventConfigRepository->findOneBy(['name' => $planetSectorEventConfigData['name']]);
            if ($planetSectorEventConfig === null) {
                $planetSectorEventConfig = new PlanetSectorEventConfig();
            }

            $planetSectorEventConfig->setName($planetSectorEventConfigData['name']);
            $planetSectorEventConfig->setEventName($planetSectorEventConfigData['eventName']);

            if (array_key_exists('outputQuantityTable', $planetSectorEventConfigData)) {
                $planetSectorEventConfig->setOutputQuantityTable($planetSectorEventConfigData['outputQuantityTable']);
            }

            $this->entityManager->persist($planetSectorEventConfig);
        }

        $this->entityManager->flush();
    }
}
