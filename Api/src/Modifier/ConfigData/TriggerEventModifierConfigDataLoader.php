<?php

namespace Mush\Modifier\ConfigData;

use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\Dto\TriggerEventModifierConfigDto;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;

class TriggerEventModifierConfigDataLoader extends EventModifierConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::getAll() as $triggerEventConfigDataDto) {
            if (!$triggerEventConfigDataDto instanceof TriggerEventModifierConfigDto) {
                continue;
            }

            /**
             * @var ?TriggerEventModifierConfig $configOrigin
             */
            $configOrigin = $this->modifierConfigRepository->findOneBy(['name' => $triggerEventConfigDataDto->key]);
            if ($configOrigin === null) {
                $config = TriggerEventModifierConfig::fromDtoChild($triggerEventConfigDataDto);
            } else {
                $config = TriggerEventModifierConfig::fromDtoChild($triggerEventConfigDataDto, $configOrigin);
            }

            $this->getModifierConfigActivationRequirements($config, $triggerEventConfigDataDto->modifierActivationRequirements);
            $this->setEventConfig($config, $triggerEventConfigDataDto->triggeredEvent);
            $this->entityManager->persist($config);
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
