<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\PatrolShip;

class PatrolShipDataLoader extends MechanicsDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $patrolShipData) {
            if ($patrolShipData['type'] !== 'patrol_ship') {
                continue;
            }

            $patrolShip = $this->mechanicsRepository->findOneBy(['name' => $patrolShipData['name']]);

            if ($patrolShip === null) {
                $patrolShip = new PatrolShip();
            } elseif (!($patrolShip instanceof PatrolShip)) {
                $this->entityManager->remove($patrolShip);
                $this->entityManager->flush();
                $patrolShip = new PatrolShip();
            }

            $patrolShip
                ->setName($patrolShipData['name'])
                ->setCollectScrapNumber($patrolShipData['collectScrapNumber'])
                ->setCollectScrapPatrolShipDamage($patrolShipData['collectScrapPatrolShipDamage'])
                ->setCollectScrapPlayerDamage($patrolShipData['collectScrapPlayerDamage'])
                ->setDockingPlace($patrolShipData['dockingPlace'])
                ->setFailedManoeuvreDaedalusDamage($patrolShipData['failedManoeuvreDaedalusDamage'])
                ->setFailedManoeuvrePatrolShipDamage($patrolShipData['failedManoeuvrePatrolShipDamage'])
                ->setFailedManoeuvrePlayerDamage($patrolShipData['failedManoeuvrePlayerDamage'])
                ->setNumberOfExplorationSteps($patrolShipData['numberOfExplorationSteps'])
            ;
            $this->setMechanicsActions($patrolShip, $patrolShipData);

            $this->entityManager->persist($patrolShip);
        }
        $this->entityManager->flush();
    }
}
