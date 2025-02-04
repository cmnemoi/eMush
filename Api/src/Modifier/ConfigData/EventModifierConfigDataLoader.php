<?php

namespace Mush\Modifier\ConfigData;

use Mush\Modifier\Entity\Config\EventModifierConfig;

class EventModifierConfigDataLoader extends ModifierConfigDataLoader
{
    /**
     * @throws \Exception
     */
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::$dataArray as $modifierConfigData) {
            if ($modifierConfigData['type'] !== 'event_modifier') {
                continue;
            }

            $configName = $modifierConfigData['name'];
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $configName]);

            if ($modifierConfig === null) {
                $modifierConfig = new EventModifierConfig($configName);
            } elseif (!$modifierConfig instanceof EventModifierConfig) {
                $this->entityManager->remove($modifierConfig);
                $this->entityManager->flush();
                $modifierConfig = new EventModifierConfig($configName);
            }

            $modifierConfig->setModifierStrategy($modifierConfigData['strategy']);

            $this->loadEventModifierData($modifierConfig, $modifierConfigData);
            $modifierConfig->setModifierActivationRequirements($this->getModifierConfigActivationRequirements($modifierConfigData, 'modifierActivationRequirements'));

            $this->entityManager->persist($modifierConfig);
        }
        $this->entityManager->flush();
    }

    protected function loadEventModifierData(EventModifierConfig $modifierConfig, array $modifierConfigData): void
    {
        $modifierConfig
            ->setPriority($modifierConfigData['priority'])
            ->setTargetEvent($modifierConfigData['targetEvent'])
            ->setApplyWhenTargeted($modifierConfigData['applyOnTarget'])
            ->setTagConstraints($modifierConfigData['tagConstraints'])
            ->setModifierRange($modifierConfigData['modifierRange'])
            ->setModifierName($modifierConfigData['modifierName']);
    }
}
