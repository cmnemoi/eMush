<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Plumbing;

class PlumbingConfigFixtures extends BlueprintConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $plumbingData) {
            if ($plumbingData['type'] !== 'plumbing') {
                continue;
            }

            $waterSupply = new Plumbing();

            $waterSupply->setName($plumbingData['name']);
            $waterSupply->setWaterDamage($plumbingData['waterDamage']);
            $this->setMechanicsActions($waterSupply, $plumbingData, $manager);

            $manager->persist($waterSupply);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
        ];
    }
}
