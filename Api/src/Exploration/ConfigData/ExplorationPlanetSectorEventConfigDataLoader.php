<?php

namespace Mush\Exploration\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationPlanetSectorEventConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Repository\EventConfigRepository;

class ExplorationPlanetSectorEventConfigDataLoader extends ConfigDataLoader
{
    private EventConfigRepository $explorationPlanetSectorEventConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventConfigRepository $explorationPlanetSectorEventConfigRepository,
    ) {
        parent::__construct($entityManager);
        $this->explorationPlanetSectorEventConfigRepository = $explorationPlanetSectorEventConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (EventConfigData::$dataArray as $explorationPlanetSectorEventConfigData) {
            if ($explorationPlanetSectorEventConfigData['type'] !== 'exploration_planet_sector_event_config') {
                continue;
            }

            $explorationPlanetSectorEventConfig = $this->explorationPlanetSectorEventConfigRepository->findOneBy(['name' => $explorationPlanetSectorEventConfigData['name']]);

            if ($explorationPlanetSectorEventConfig !== null) {
                continue;
            }

            $explorationPlanetSectorEventConfig = new ExplorationPlanetSectorEventConfig();
            $explorationPlanetSectorEventConfig
                ->setName($explorationPlanetSectorEventConfigData['name'])
                ->setEventName($explorationPlanetSectorEventConfigData['eventName'])
            ;
            if (array_key_exists('outputQuantityTable', $explorationPlanetSectorEventConfigData)) {
                $explorationPlanetSectorEventConfig->setOutputQuantityTable($explorationPlanetSectorEventConfigData['outputQuantityTable']);
            }

            $this->entityManager->persist($explorationPlanetSectorEventConfig);
        }

        $this->entityManager->flush();
    }
}
