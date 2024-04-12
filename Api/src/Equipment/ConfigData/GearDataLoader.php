<?php

namespace Mush\Equipment\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Repository\ActionRepository;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Repository\MechanicsRepository;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Repository\ModifierConfigRepository;

class GearDataLoader extends MechanicsDataLoader
{
    private ModifierConfigRepository $modifierConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        MechanicsRepository $mechanicsRepository,
        ActionRepository $actionRepository,
        ModifierConfigRepository $modifierConfigRepository
    ) {
        parent::__construct($entityManager, $mechanicsRepository, $actionRepository);

        $this->modifierConfigRepository = $modifierConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $gearData) {
            if ($gearData['type'] !== 'gear') {
                continue;
            }

            $gear = $this->mechanicsRepository->findOneBy(['name' => $gearData['name']]);

            if ($gear === null) {
                $gear = new Gear();
            } elseif (!$gear instanceof Gear) {
                $this->entityManager->remove($gear);
                $gear = new Gear();
            }

            $gear->setName($gearData['name']);

            $this->setGearModifierConfigs($gear, $gearData);
            $this->setMechanicsActions($gear, $gearData);

            $this->entityManager->persist($gear);
        }
        $this->entityManager->flush();
    }

    private function setGearModifierConfigs(Gear $gear, array $gearData)
    {
        $modifierConfigs = [];
        foreach ($gearData['modifierConfigs'] as $modifierConfigName) {
            /** @var AbstractModifierConfig $modifierConfig */
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigName]);
            if ($modifierConfig === null) {
                throw new \Exception('Modifier config not found: ' . $modifierConfigName);
            }
            $modifierConfigs[] = $modifierConfig;
        }
        $gear->setModifierConfigs($modifierConfigs);
    }
}
