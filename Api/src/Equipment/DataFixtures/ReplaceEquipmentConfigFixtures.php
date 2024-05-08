<?php

declare(strict_types=1);

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Equipment\ConfigData\ReplaceEquipmentConfigData;
use Mush\Equipment\ConfigData\SpawnEquipmentConfigData;

/** @codeCoverageIgnore */
final class ReplaceEquipmentConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (ReplaceEquipmentConfigData::getAll() as $data) {
            $replaceEquipmentConfig = $data->toEntity();

            $manager->persist($replaceEquipmentConfig);
            $this->addReference($replaceEquipmentConfig->getName(), $replaceEquipmentConfig);
        }

        $manager->flush();
    }
}
