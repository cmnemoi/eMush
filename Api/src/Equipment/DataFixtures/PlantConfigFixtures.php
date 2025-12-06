<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Plant;

class PlantConfigFixtures extends BlueprintConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $plantData) {
            if ($plantData['type'] !== 'plant') {
                continue;
            }

            $plant = new Plant();

            $plant
                ->setName($plantData['name'])
                ->setFruitName($plantData['fruitName'])
                ->setMaturationTime($plantData['maturationTime'])
                ->setOxygen($plantData['oxygen']);

            $this->setMechanicsActions($plant, $plantData, $manager);

            $manager->persist($plant);

            $this->addReference($plant->getName(), $plant);
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
