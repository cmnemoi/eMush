<?php

namespace Mush\Modifier\ConfigData;

use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\Entity\Config\DirectModifierConfig;

class DirectModifierConfigDataLoader extends ModifierConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::$dataArray as $modifierConfigData) {
            if ($modifierConfigData['type'] !== 'direct_modifier') {
                continue;
            }

            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigData['name']]);

            if ($modifierConfig !== null) {
                continue;
            }

            $modifierConfig = new DirectModifierConfig();
            $modifierConfig
                ->setRevertOnRemove($modifierConfigData['revertOnRemove'])
                ->setName($modifierConfigData['name'])
                ->setModifierName($modifierConfigData['modifierName'])
                ->setModifierRange($modifierConfigData['modifierRange'])
            ;

            $modifierConfig = $this->setEventConfig($modifierConfig, $modifierConfigData['triggeredEvent']);
            $this->setModifierConfigActivationRequirements($modifierConfig, $modifierConfigData);

            $this->entityManager->persist($modifierConfig);
        }
        $this->entityManager->flush();
    }

    protected function setEventConfig(DirectModifierConfig $modifierConfig, string $eventConfigName): DirectModifierConfig
    {
        /** @var AbstractEventConfig $eventConfig */
        $eventConfig = $this->eventConfigRepository->findOneBy(['name' => $eventConfigName]);

        if ($eventConfig === null) {
            throw new \Exception("Event config {$eventConfigName} not found");
        }

        $modifierConfig->setTriggeredEvent($eventConfig);

        return $modifierConfig;
    }
}
