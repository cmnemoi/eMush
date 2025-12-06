<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Tool;

class ToolConfigFixtures extends BlueprintConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $toolData) {
            if ($toolData['type'] !== 'tool') {
                continue;
            }

            $tool = new Tool();

            $tool->setName($toolData['name']);
            $this->setMechanicsActions($tool, $toolData, $manager);

            $manager->persist($tool);

            $this->addReference($tool->getName(), $tool);
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
