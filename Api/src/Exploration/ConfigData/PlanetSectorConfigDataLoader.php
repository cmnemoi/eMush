<?php

namespace Mush\Exploration\ConfigData;

use Doctrine\ORM\EntityRepository;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Game\ConfigData\ConfigDataLoader;

class PlanetSectorConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository(PlanetSectorConfig::class);

        foreach (PlanetSectorConfigData::$dataArray as $data) {
            /** @var ?PlanetSectorConfig $planetSectorConfig */
            $planetSectorConfig = $repository->findOneBy(['name' => $data['name']]);

            if ($planetSectorConfig === null) {
                $planetSectorConfig = new PlanetSectorConfig();
            }

            $planetSectorConfig
                ->setName($data['name'])
                ->setWeightAtPlanetGeneration($data['weightAtPlanetGeneration'])
                ->setWeightAtPlanetAnalysis($data['weightAtPlanetAnalysis'])
                ->setWeightAtPlanetExploration($data['weightAtPlanetExploration'])
                ->setMaxPerPlanet($data['maxPerPlanet'])
                ->setExplorationEvents($data['explorationEvents'])
            ;
            $this->entityManager->persist($planetSectorConfig);
        }
        $this->entityManager->flush();
    }
}
