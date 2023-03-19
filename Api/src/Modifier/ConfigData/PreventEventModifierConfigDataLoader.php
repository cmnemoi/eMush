<?php

namespace Mush\Modifier\ConfigData;

use Mush\Modifier\Entity\Config\PreventEventModifierConfig;

class PreventEventModifierConfigDataLoader extends ModifierConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::$dataArray as $modifierConfigData) {
            if ($modifierConfigData['type'] !== 'prevent_event_modifier') {
                continue;
            }

            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $modifierConfigData['name']]);

            if ($modifierConfig === null) {
                $modifierConfig = new PreventEventModifierConfig();
            } elseif (!($modifierConfig instanceof PreventEventModifierConfig)) {
                $this->entityManager->remove($modifierConfig);
                $modifierConfig = new PreventEventModifierConfig();
            }

            $modifierConfig
                ->setTargetEvent($modifierConfigData['targetEvent'])
                ->setApplyOnTarget($modifierConfigData['applyOnTarget'])
                ->setTagConstraints($modifierConfigData['tagConstraints'])
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
