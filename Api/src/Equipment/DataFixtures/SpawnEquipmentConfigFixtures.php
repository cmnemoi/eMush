<?php

declare(strict_types=1);

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\ConfigData\SpawnEquipmentConfigData;

/** @codeCoverageIgnore */
final class SpawnEquipmentConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $projectConfigs = [];

        foreach (SpawnEquipmentConfigData::getAll() as $data) {
            $projectConfig = $data->toEntity();
            $projectConfigs[] = $projectConfig;

            $manager->persist($projectConfig);
        }

        $manager->flush();
    }
}
