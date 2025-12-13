<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\Mechanics\Blueprint;

class BlueprintConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $blueprintData) {
            if ($blueprintData['type'] !== 'blueprint') {
                continue;
            }

            $blueprint = new Blueprint();

            $blueprint
                ->setName($blueprintData['name'])
                ->setCraftedEquipmentName($blueprintData['craftedEquipmentName'])
                ->setIngredients($blueprintData['ingredients']);
            $this->setMechanicsActions($blueprint, $blueprintData, $manager);

            $manager->persist($blueprint);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
        ];
    }

    protected function setMechanicsActions(EquipmentMechanic $mechanics, array $mechanicsData, ObjectManager $manager): void
    {
        $actions = [];
        foreach ($mechanicsData['actions'] as $actionName) {
            /** @var ActionConfig $action */
            $action = $manager->getRepository(ActionConfig::class)->findOneBy(['name' => $actionName]);
            if ($action === null) {
                throw new \Exception('ActionConfig not found: ' . $actionName);
            }
            $actions[] = $action;
        }
        $mechanics->setActions(new ArrayCollection($actions));
    }
}
