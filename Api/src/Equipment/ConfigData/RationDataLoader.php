<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Ration;

class RationDataLoader extends MechanicsDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $rationData) {
            if ($rationData['type'] !== 'ration') {
                continue;
            }

            $ration = $this->mechanicsRepository->findOneBy(['name' => $rationData['name']]);

            if ($ration === null) {
                $ration = new Ration();
            } elseif (!$ration instanceof Ration) {
                $this->entityManager->remove($ration);
                $ration = new Ration();
            }

            $this->setRationAttributes($ration, $rationData);
            $this->setMechanicsActions($ration, $rationData);
            $this->entityManager->persist($ration);
        }
        $this->entityManager->flush();
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
