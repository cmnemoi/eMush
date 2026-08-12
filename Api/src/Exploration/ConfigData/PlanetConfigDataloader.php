<?php

declare(strict_types=1);

namespace Mush\Exploration\ConfigData;

use Doctrine\ORM\EntityRepository;
use Mush\Exploration\Entity\PlanetConfig;
use Mush\Game\ConfigData\ConfigDataLoader;

class PlanetConfigDataloader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository(PlanetConfig::class);

        foreach (PlanetConfigData::$dataArray as $data) {
            /** @var ?PlanetConfig $planetConfig */
            $planetConfig = $repository->findOneBy(['name' => $data['name']]);

            if ($planetConfig === null) {
                $planetConfig = PlanetConfig::fromConfigData($data);
            } else {
                $planetConfig->update($data);
            }

            $this->entityManager->persist($planetConfig);
        }
        $this->entityManager->flush();
    }
}
