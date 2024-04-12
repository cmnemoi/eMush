<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Fruit;

class FruitDataLoader extends RationDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $fruitData) {
            if ($fruitData['type'] !== 'fruit') {
                continue;
            }

            $fruit = $this->mechanicsRepository->findOneBy(['name' => $fruitData['name']]);

            if ($fruit === null) {
                $fruit = new Fruit();
            } elseif (!$fruit instanceof Fruit) {
                $this->entityManager->remove($fruit);
                $fruit = new Fruit();
            }

            $fruit->setPlantName($fruitData['plantName']);

            $this->setRationAttributes($fruit, $fruitData);
            $this->setMechanicsActions($fruit, $fruitData);
            $this->entityManager->persist($fruit);
        }
        $this->entityManager->flush();
    }
}
