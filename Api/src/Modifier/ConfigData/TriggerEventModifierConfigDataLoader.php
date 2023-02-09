<?php

namespace Mush\Modifier\ConfigData;

use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;

class TriggerEventModifierConfigDataLoader extends ModifierConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::$dataArray as $modifierConfigData) {
            if ($modifierConfigData['type'] !== 'trigger_event_modifier') {
                continue;
            }

            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigData['name']]);

            if ($modifierConfig !== null) {
                continue;
            }

            $modifierConfig = new TriggerEventModifierConfig();
            $modifierConfig
                ->setTriggeredEvent($modifierConfigData['triggeredEvent'])
                ->setVisibility($modifierConfigData['visibility'])
                ->setName($modifierConfigData['name'])
                ->setModifierName($modifierConfigData['modifierName'])
                ->setTargetEvent($modifierConfigData['targetEvent'])
                ->setApplyOnParameterOnly($modifierConfigData['applyOnActionParameter'])
                ->setModifierHolderClass($modifierConfigData['modifierHolderClass'])
            ;
            $this->setModifierConfigActivationRequirements($modifierConfig, $modifierConfigData);

            $this->entityManager->persist($modifierConfig);
        }
        $this->entityManager->flush();
    }
}
