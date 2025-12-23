<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Entity\Mechanics\Kit;

class BlueprintDataLoader extends MechanicsDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $blueprintData) {
            if ($blueprintData['type'] !== 'blueprint' && $blueprintData['type'] !== 'kit') {
                continue;
            }

            $blueprint = $this->mechanicsRepository->findOneBy(['name' => $blueprintData['name']]);

            if ($blueprint === null) {
                $blueprintData['type'] === 'kit' ? $blueprint = new Kit() : $blueprint = new Blueprint();
            } elseif (!$blueprint instanceof Blueprint) {
                $this->entityManager->remove($blueprint);
                $blueprintData['type'] === 'kit' ? $blueprint = new Kit() : $blueprint = new Blueprint();
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
