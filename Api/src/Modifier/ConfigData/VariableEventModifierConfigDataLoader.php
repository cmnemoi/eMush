<?php

namespace Mush\Modifier\ConfigData;

use Mush\Modifier\Dto\VariableEventModifierConfigDto;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;

class VariableEventModifierConfigDataLoader extends EventModifierConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::getAll() as $triggerEventConfigDataDto) {
            if (!$triggerEventConfigDataDto instanceof VariableEventModifierConfigDto) {
                continue;
            }

            /**
             * @var ?VariableEventModifierConfig $configOrigin
             */
            $configOrigin = $this->modifierConfigRepository->findOneBy(['name' => $triggerEventConfigDataDto->key]);
            if ($configOrigin === null) {
                $config = VariableEventModifierConfig::fromDtoChild($triggerEventConfigDataDto);
            } else {
                $config = VariableEventModifierConfig::fromDtoChild($triggerEventConfigDataDto, $configOrigin);
            }

            $this->getModifierConfigActivationRequirements($config, $triggerEventConfigDataDto->modifierActivationRequirements);
            $this->entityManager->persist($config);
        }
        $this->entityManager->flush();
    }
}
