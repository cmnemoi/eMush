<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Weapon;

class WeaponConfigFixtures extends BlueprintConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $weaponData) {
            if ($weaponData['type'] !== 'weapon') {
                continue;
            }

            $weapon = Weapon::fromConfigData($weaponData);

            $weapon->updateFromConfigData($weaponData);
            $this->setMechanicsActions($weapon, $weaponData, $manager);

            $manager->persist($weapon);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
        ];
    }
}
