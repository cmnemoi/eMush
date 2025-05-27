<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Weapon;

class WeaponDataLoader extends MechanicsDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $weaponData) {
            if ($weaponData['type'] !== 'weapon') {
                continue;
            }

            $weapon = $this->mechanicsRepository->findOneBy(['name' => $weaponData['name']]);

            if ($weapon === null) {
                $weapon = new Weapon();
            } elseif (!$weapon instanceof Weapon) {
                $this->entityManager->remove($weapon);
                $weapon = new Weapon();
            }

            $weapon
                ->setName($weaponData['name'])
                ->setBaseAccuracy($weaponData['baseAccuracy'])
                ->setBaseDamageRange($weaponData['baseDamageRange'])
                ->setExpeditionBonus($weaponData['expeditionBonus'])
                ->setCriticalSuccessRate($weaponData['criticalSuccessRate'])
                ->setCriticalFailRate($weaponData['criticalFailRate'])
                ->setOneShotRate($weaponData['oneShotRate']);
            $this->setMechanicsActions($weapon, $weaponData);

            $this->entityManager->persist($weapon);
        }
        $this->entityManager->flush();
    }
}
