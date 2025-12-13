<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Drug;

class DrugConfigFixtures extends RationConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $drugData) {
            if ($drugData['type'] !== 'drug') {
                continue;
            }

            $drug = new Drug();

            $this->setRationAttributes($drug, $drugData);
            $this->setMechanicsActions($drug, $drugData, $manager);

            $manager->persist($drug);
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
