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
                $weapon = Weapon::fromConfigData($weaponData);
            } elseif (!$weapon instanceof Weapon) {
                $this->entityManager->remove($weapon);
                $this->entityManager->flush();
                $weapon = Weapon::fromConfigData($weaponData);
            }

            $weapon->updateFromConfigData($weaponData);
            $this->setMechanicsActions($weapon, $weaponData);

            $this->entityManager->persist($weapon);
        }
        $this->entityManager->flush();
    }
}
