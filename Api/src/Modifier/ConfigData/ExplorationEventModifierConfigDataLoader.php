<?php

declare(strict_types=1);

namespace Mush\Modifier\ConfigData;

use Mush\Modifier\Dto\ExplorationEventModifierConfigDto;
use Mush\Modifier\Entity\Config\ExplorationEventModifierConfig;

class ExplorationEventModifierConfigDataLoader extends EventModifierConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::getAll() as $explorationEventConfigDataDto) {
            if (!$explorationEventConfigDataDto instanceof ExplorationEventModifierConfigDto) {
                continue;
            }

            /**
             * @var ?ExplorationEventModifierConfig $configOrigin
             */
            $configOrigin = $this->modifierConfigRepository->findOneBy(['name' => $explorationEventConfigDataDto->key]);
            if ($configOrigin === null) {
                $config = ExplorationEventModifierConfig::fromDtoChild($explorationEventConfigDataDto);
            } else {
                $config = ExplorationEventModifierConfig::fromDtoChild($explorationEventConfigDataDto, $configOrigin);
            }

            $this->getModifierConfigActivationRequirements($config, $explorationEventConfigDataDto->modifierActivationRequirements);
            $this->entityManager->persist($config);
        }
        $this->entityManager->flush();
    }
}
