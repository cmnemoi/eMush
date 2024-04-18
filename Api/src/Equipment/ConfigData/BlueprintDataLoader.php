<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Blueprint;

class BlueprintDataLoader extends MechanicsDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $blueprintData) {
            if ($blueprintData['type'] !== 'blueprint') {
                continue;
            }

            $blueprint = $this->mechanicsRepository->findOneBy(['name' => $blueprintData['name']]);

            if ($blueprint === null) {
                $blueprint = new Blueprint();
            } elseif (!$blueprint instanceof Blueprint) {
                $this->entityManager->remove($blueprint);
                $blueprint = new Blueprint();
            }

            $blueprint
                ->setName($blueprintData['name'])
                ->setCraftedEquipmentName($blueprintData['craftedEquipmentName'])
                ->setIngredients($blueprintData['ingredients']);
            $this->setMechanicsActions($blueprint, $blueprintData);

            $this->entityManager->persist($blueprint);
        }
        $this->entityManager->flush();
    }
}
