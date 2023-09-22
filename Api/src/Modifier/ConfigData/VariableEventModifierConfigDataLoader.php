<?php

namespace Mush\Modifier\ConfigData;

use Mush\Modifier\Entity\Config\VariableEventModifierConfig;

class VariableEventModifierConfigDataLoader extends EventModifierConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::$dataArray as $modifierConfigData) {
            if ($modifierConfigData['type'] !== 'variable_event_modifier') {
                continue;
            }

            $configName = $modifierConfigData['name'];
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $configName]);

            if ($modifierConfig === null) {
                $modifierConfig = new VariableEventModifierConfig($configName);
            } elseif (!($modifierConfig instanceof VariableEventModifierConfig)) {
                $this->entityManager->remove($modifierConfig);
                $this->entityManager->flush();
                $modifierConfig = new VariableEventModifierConfig($configName);
            }

            $modifierConfig
                ->setMode($modifierConfigData['mode'])
                ->setDelta($modifierConfigData['delta'])
                ->setTargetVariable($modifierConfigData['targetVariable'])
            ;
            $this->loadEventModifierData($modifierConfig, $modifierConfigData);
            $this->setModifierConfigActivationRequirements($modifierConfig, $modifierConfigData);

            $this->entityManager->persist($modifierConfig);
        }
        $this->entityManager->flush();
    }
}
