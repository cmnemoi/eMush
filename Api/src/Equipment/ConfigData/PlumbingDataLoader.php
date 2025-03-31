<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Plumbing;

class PlumbingDataLoader extends ToolDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $plumbingData) {
            if ($plumbingData['type'] !== 'plumbing') {
                continue;
            }

            $waterSupply = $this->mechanicsRepository->findOneBy(['name' => $plumbingData['name']]);

            if ($waterSupply === null) {
                $waterSupply = new Plumbing();
            } elseif (!$waterSupply instanceof Plumbing) {
                $this->entityManager->remove($waterSupply);
                $waterSupply = new Plumbing();
            }

            $waterSupply->setName($plumbingData['name']);
            $waterSupply->setWaterDamage($plumbingData['waterDamage']);
            $this->setMechanicsActions($waterSupply, $plumbingData);
            $this->entityManager->persist($waterSupply);
        }
        $this->entityManager->flush();
    }
}
