<?php

namespace Mush\Modifier\ConfigData;

use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\Dto\DirectModifierConfigDto;
use Mush\Modifier\Entity\Config\DirectModifierConfig;

class DirectModifierConfigDataLoader extends ModifierConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (ModifierConfigData::getAll() as $modifierConfigDataDto) {
            if (!$modifierConfigDataDto instanceof DirectModifierConfigDto) {
                continue;
            }

            $config = DirectModifierConfig::fromDto($modifierConfigDataDto);
            $this->setEventConfig($config, $modifierConfigDataDto->triggeredEvent);
            $this->getModifierConfigActivationRequirements($config, $modifierConfigDataDto->modifierActivationRequirements);
            $this->getEventConfigActivationRequirements($config, $modifierConfigDataDto->eventActivationRequirements);
            $this->entityManager->persist($config);
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

    protected function getEventConfigActivationRequirements(DirectModifierConfig $modifierConfigData, array $modifierActivationRequirementsAsString): void
    {
        $modifierActivationRequirements = [];
        foreach ($modifierActivationRequirementsAsString as $activationRequirementName) {
            $modifierActivationRequirement = $this->modifierActivationRequirementRepository->findOneBy(['name' => $activationRequirementName]);

            if ($modifierActivationRequirement === null) {
                throw new \Exception('Modifier activation requirement not found: ' . $activationRequirementName);
            }
            $modifierActivationRequirements[] = $modifierActivationRequirement;
        }

        $modifierConfigData->setEventActivationRequirements($modifierActivationRequirements);
    }
}
