<?php

namespace Mush\Modifier\ConfigData;

use Mush\Modifier\Entity\Config\VariableEventModifierConfig;

class VariableEventModifierConfigDataLoader extends ModifierConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::$dataArray as $modifierConfigData) {
            if ($modifierConfigData['type'] !== 'variable_event_modifier') {
                continue;
            }

            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigData['name']]);

            if ($modifierConfig !== null) {
                continue;
            }

            $modifierConfig = new VariableEventModifierConfig();
            $modifierConfig
                ->setDelta($modifierConfigData['delta'])
                ->setTargetVariable($modifierConfigData['targetVariable'])
                ->setMode($modifierConfigData['mode'])
                ->setAppliesOn($modifierConfigData['appliesOn'])
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
