<?php

namespace Mush\Modifier\ConfigData;

use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;

class TriggerEventModifierConfigDataLoader extends EventModifierConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::$dataArray as $modifierConfigData) {
            if ($modifierConfigData['type'] !== 'trigger_event_modifier') {
                continue;
            }
            $configName = $modifierConfigData['name'];

            $modifierConfig = $this->modifierConfigRepository->findOneBy(['name' => $configName]);

            if ($modifierConfig === null) {
                $modifierConfig = new TriggerEventModifierConfig($configName);
            } elseif (!$modifierConfig instanceof TriggerEventModifierConfig) {
                $this->entityManager->remove($modifierConfig);
                $this->entityManager->flush();
                $modifierConfig = new TriggerEventModifierConfig($configName);
            }

            $modifierConfig = $this->setEventConfig($modifierConfig, $modifierConfigData['triggeredEvent']);
            $this->loadEventModifierData($modifierConfig, $modifierConfigData);
            $this->setModifierConfigActivationRequirements($modifierConfig, $modifierConfigData);

            $this->entityManager->persist($modifierConfig);
        }
        $this->entityManager->flush();
    }

    protected function setEventConfig(TriggerEventModifierConfig $modifierConfig, ?string $eventConfigName): TriggerEventModifierConfig
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
