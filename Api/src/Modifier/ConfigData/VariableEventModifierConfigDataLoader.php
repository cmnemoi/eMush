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

            if ($modifierConfig === null) {
                $modifierConfig = new VariableEventModifierConfig();
            } elseif (!($modifierConfig instanceof VariableEventModifierConfig)) {
                $this->entityManager->remove($modifierConfig);
                $modifierConfig = new VariableEventModifierConfig();
            }

            $modifierConfig
                ->setMode($modifierConfigData['mode'])
                ->setDelta($modifierConfigData['delta'])
                ->setTargetVariable($modifierConfigData['targetVariable'])
                ->setTargetEvent($modifierConfigData['targetEvent'])
                ->setApplyOnParameterOnly($modifierConfigData['applyOnActionParameter'])
                ->setModifierRange($modifierConfigData['modifierRange'])
                ->setName($modifierConfigData['name'])
                ->setModifierName($modifierConfigData['modifierName'])
            ;
            $this->setModifierConfigActivationRequirements($modifierConfig, $modifierConfigData);

            $this->entityManager->persist($modifierConfig);
        }
        $this->entityManager->flush();
    }
}
