<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Plant;

class PlantDataLoader extends RationDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $plantData) {
            if ($plantData['type'] !== 'plant') {
                continue;
            }

            $plant = $this->mechanicsRepository->findOneBy(['name' => $plantData['name']]);

            if ($plant === null) {
                $plant = new Plant();
            } elseif (!$plant instanceof Plant) {
                $this->entityManager->remove($plant);
                $plant = new Plant();
            }

            $plant
                ->setName($plantData['name'])
                ->setFruitName($plantData['fruitName'])
                ->setMaturationTime($plantData['maturationTime'])
                ->setOxygen($plantData['oxygen']);

            $this->setMechanicsActions($plant, $plantData);
            $this->entityManager->persist($plant);
        }
        $this->entityManager->flush();
    }
}
