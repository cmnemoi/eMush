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

            $configName = $modifierConfigData['name'];
            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $configName]);

            if ($modifierConfig === null) {
                $modifierConfig = new DirectModifierConfig($configName);
            } elseif (!($modifierConfig instanceof DirectModifierConfig)) {
                $this->entityManager->remove($modifierConfig);
                $this->entityManager->flush();
                $modifierConfig = new DirectModifierConfig($configName);
            }

            $modifierConfig
                ->setRevertOnRemove($modifierConfigData['revertOnRemove'])
                ->setModifierName($modifierConfigData['modifierName'])
                ->setModifierRange($modifierConfigData['modifierRange'])
                ->setModifierStrategy($modifierConfigData['modifierStrategy'])
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
