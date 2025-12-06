<?php

namespace Mush\Modifier\ConfigData;

use Mush\Modifier\Dto\EventModifierConfigDto;
use Mush\Modifier\Dto\TriggerEventModifierConfigDto;
use Mush\Modifier\Dto\VariableEventModifierConfigDto;
use Mush\Modifier\Entity\Config\EventModifierConfig;

class EventModifierConfigDataLoader extends ModifierConfigDataLoader
{
    /**
     * @throws \Exception
     */
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::getAll() as $modifierConfigDataDto) {
            if (!$modifierConfigDataDto instanceof EventModifierConfigDto
                || $modifierConfigDataDto instanceof TriggerEventModifierConfigDto
                || $modifierConfigDataDto instanceof VariableEventModifierConfigDto) {
                continue;
            }

            $config = EventModifierConfig::fromDto($modifierConfigDataDto);
            $this->getModifierConfigActivationRequirements($config, $modifierConfigDataDto->modifierActivationRequirements);
            $this->entityManager->persist($config);
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
