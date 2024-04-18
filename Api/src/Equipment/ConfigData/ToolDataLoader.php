<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Tool;

class ToolDataLoader extends MechanicsDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $toolData) {
            if ($toolData['type'] !== 'tool') {
                continue;
            }

            $tool = $this->mechanicsRepository->findOneBy(['name' => $toolData['name']]);

            if ($tool === null) {
                $tool = new Tool();
            } elseif (!$tool instanceof Tool) {
                $this->entityManager->remove($tool);
                $tool = new Tool();
            }

            $tool->setName($toolData['name']);
            $this->setMechanicsActions($tool, $toolData);

            $this->entityManager->persist($tool);
        }
        $this->entityManager->flush();
    }
}
