<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Modifier\DataFixtures\ModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;

class GearConfigFixtures extends BlueprintConfigFixtures implements DependentFixtureInterface
{
    private ObjectManager $objectManager;

    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $gearData) {
            if ($gearData['type'] !== 'gear') {
                continue;
            }

            $gear = new Gear();

            $gear->setName($gearData['name']);
            $this->setGearModifierConfigs($gear, $gearData, $manager);
            $this->setMechanicsActions($gear, $gearData, $manager);

            $manager->persist($gear);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            ModifierConfigFixtures::class,
        ];
    }

    private function setGearModifierConfigs(Gear $gear, array $gearData, ObjectManager $manager)
    {
        $modifierConfigs = [];
        foreach ($gearData['modifierConfigs'] as $modifierConfigName) {
            /** @var AbstractModifierConfig $modifierConfig */
            $modifierConfig = $manager->getRepository(AbstractModifierConfig::class)->findOneBy(['name' => $modifierConfigName]);
            if ($modifierConfig === null) {
                throw new \Exception('Modifier config not found: ' . $modifierConfigName);
            }
            $modifierConfigs[] = $modifierConfig;
        }
        $gear->setModifierConfigs($modifierConfigs);
    }
}
