<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Ration;

class RationConfigFixtures extends BlueprintConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $rationData) {
            if ($rationData['type'] !== 'ration') {
                continue;
            }

            $ration = new Ration();

            $this->setRationAttributes($ration, $rationData);
            $this->setMechanicsActions($ration, $rationData, $manager);

            $manager->persist($ration);

            $this->addReference($ration->getName(), $ration);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
        ];
    }

    protected function setRationAttributes(Ration $ration, array $rationData)
    {
        $ration
            ->setName($rationData['name'])
            ->setActionPoints($rationData['actionPoints'])
            ->setMoralPoints($rationData['moralPoints'])
            ->setMovementPoints($rationData['movementPoints'])
            ->setHealthPoints($rationData['healthPoints'])
            ->setSatiety($rationData['satiety'])
            ->setExtraEffects($rationData['extraEffects'])
            ->setIsPerishable($rationData['isPerishable']);
    }
}
