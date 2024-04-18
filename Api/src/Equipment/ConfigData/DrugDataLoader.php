<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Drug;

class DrugDataLoader extends RationDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $drugData) {
            if ($drugData['type'] !== 'drug') {
                continue;
            }

            $drug = $this->mechanicsRepository->findOneBy(['name' => $drugData['name']]);

            if ($drug === null) {
                $drug = new Drug();
            } elseif (!$drug instanceof Drug) {
                $this->entityManager->remove($drug);
                $drug = new Drug();
            }

            $this->setRationAttributes($drug, $drugData);
            $this->setMechanicsActions($drug, $drugData);
            $this->entityManager->persist($drug);
        }
        $this->entityManager->flush();
    }
}
