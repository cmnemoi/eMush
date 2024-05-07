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
        $spawnEquipmentConfigs = [];

        foreach (SpawnEquipmentConfigData::getAll() as $data) {
            $spawnEquipmentConfig = $data->toEntity();
            $spawnEquipmentConfigs[] = $spawnEquipmentConfig;

            $manager->persist($spawnEquipmentConfig);
            $this->addReference($spawnEquipmentConfig->getName(), $spawnEquipmentConfig);
        }

        $manager->flush();
    }
}
