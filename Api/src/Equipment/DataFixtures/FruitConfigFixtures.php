<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Fruit;

class FruitConfigFixtures extends RationConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $fruitData) {
            if ($fruitData['type'] !== 'fruit') {
                continue;
            }

            // making sure they don't fail test by having 2 or 3 AP
            $fruitData['actionPoints'] = [1 => 1];

            $fruit = new Fruit();

            $fruit->setPlantName($fruitData['plantName']);

            $this->setRationAttributes($fruit, $fruitData);
            $this->setMechanicsActions($fruit, $fruitData, $manager);

            $manager->persist($fruit);
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
