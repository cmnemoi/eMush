<?php

namespace Mush\Modifier\ConfigData;

use Mush\Game\Entity\AbstractEventConfig;
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

            if ($modifierConfig === null) {
                $modifierConfig = new TriggerEventModifierConfig();
            } elseif (!($modifierConfig instanceof TriggerEventModifierConfig)) {
                $this->entityManager->remove($modifierConfig);
                $modifierConfig = new TriggerEventModifierConfig();
            }

            $modifierConfig
                ->setVisibility($modifierConfigData['visibility'])
                ->setTargetEvent($modifierConfigData['targetEvent'])
                ->setApplyOnTarget($modifierConfigData['applyOnActionParameter'])
                ->setModifierRange($modifierConfigData['modifierRange'])
                ->setName($modifierConfigData['name'])
                ->setModifierName($modifierConfigData['modifierName'])
            ;
            $modifierConfig = $this->setEventConfig($modifierConfig, $modifierConfigData['triggeredEvent']);

            $this->setModifierConfigActivationRequirements($modifierConfig, $modifierConfigData);

            $this->entityManager->persist($modifierConfig);
        }
        $this->entityManager->flush();
    }

    protected function setEventConfig(TriggerEventModifierConfig $modifierConfig, string $eventConfigName): TriggerEventModifierConfig
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
