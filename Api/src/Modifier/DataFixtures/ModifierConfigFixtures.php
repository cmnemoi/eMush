<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\EventConfigFixtures;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\ConfigData\ModifierActivationRequirementData;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Dto\AbstractModifierConfigDto;
use Mush\Modifier\Dto\DirectModifierConfigDto;
use Mush\Modifier\Dto\EventModifierConfigDto;
use Mush\Modifier\Dto\TriggerEventModifierConfigDto;
use Mush\Modifier\Dto\VariableEventModifierConfigDto;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;

final class ModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    private $modifierActivationRequirements = [];

    public function load(ObjectManager $manager): void
    {
        $this->loadModifierActivationRequirementData($manager);

        /** @var AbstractModifierConfigDto $modifierConfigDto */
        foreach (ModifierConfigData::getAll() as $modifierConfigDto) {
            $config = match (true) {
                $modifierConfigDto instanceof TriggerEventModifierConfigDto => $this->loadTriggeredEventModifierConfig($modifierConfigDto),
                $modifierConfigDto instanceof VariableEventModifierConfigDto => $this->loadVariableEventModifierConfig($modifierConfigDto),
                $modifierConfigDto instanceof EventModifierConfigDto => $this->loadEventModifierConfig($modifierConfigDto),
                $modifierConfigDto instanceof DirectModifierConfigDto => $this->loadDirectModifierConfig($modifierConfigDto),
                default => null
            };

            if ($config === null) {
                continue;
            }

            $manager->persist($config);

            $this->addReference($config->getName(), $config);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EventConfigFixtures::class,
        ];
    }

    private function getModifierConfigActivationRequirements(array $modifierActivationRequirementsAsString): array
    {
        $modifierActivationRequirements = [];
        foreach ($modifierActivationRequirementsAsString as $activationRequirementName) {
            $modifierActivationRequirement = $this->modifierActivationRequirements[$activationRequirementName];

            if ($modifierActivationRequirement === null) {
                throw new \Exception('Modifier activation requirement not found: ' . $activationRequirementName);
            }
            $modifierActivationRequirements[] = $modifierActivationRequirement;
        }

        return $modifierActivationRequirements;
    }

    private function loadEventModifierConfig(EventModifierConfigDto $eventModifierConfigDto): EventModifierConfig
    {
        $config = EventModifierConfig::fromDto($eventModifierConfigDto);
        $config->setModifierActivationRequirements($this->getModifierConfigActivationRequirements($eventModifierConfigDto->modifierActivationRequirements));

        return $config;
    }

    private function loadTriggeredEventModifierConfig(TriggerEventModifierConfigDto $triggeredEventModifierConfigDto): TriggerEventModifierConfig
    {
        $config = TriggerEventModifierConfig::fromDtoChild($triggeredEventModifierConfigDto);
        $config->setModifierActivationRequirements($this->getModifierConfigActivationRequirements($triggeredEventModifierConfigDto->modifierActivationRequirements));

        $event = $this->getReference($triggeredEventModifierConfigDto->triggeredEvent);

        if (!$event instanceof AbstractEventConfig) {
            throw new \Exception("Event config {$triggeredEventModifierConfigDto->triggeredEvent} not found");
        }

        $config->setTriggeredEvent($event);

        return $config;
    }

    private function loadVariableEventModifierConfig(VariableEventModifierConfigDto $variableEventModifierConfigDto): VariableEventModifierConfig
    {
        $config = VariableEventModifierConfig::fromDtoChild($variableEventModifierConfigDto);
        $config->setModifierActivationRequirements($this->getModifierConfigActivationRequirements($variableEventModifierConfigDto->modifierActivationRequirements));

        return $config;
    }

    private function loadDirectModifierConfig(DirectModifierConfigDto $directModifierConfigDto): DirectModifierConfig
    {
        $config = DirectModifierConfig::fromDto($directModifierConfigDto);

        $config->setModifierActivationRequirements($this->getModifierConfigActivationRequirements($directModifierConfigDto->modifierActivationRequirements));
        $config->setEventActivationRequirements($this->getModifierConfigActivationRequirements($directModifierConfigDto->eventActivationRequirements));

        $event = $this->getReference($directModifierConfigDto->triggeredEvent);

        if (!$event instanceof AbstractEventConfig) {
            throw new \Exception("Event config {$directModifierConfigDto->triggeredEvent} not found");
        }

        $config->setTriggeredEvent($event);

        return $config;
    }

    private function loadModifierActivationRequirementData(ObjectManager $manager): void
    {
        foreach (ModifierActivationRequirementData::$dataArray as $modifierActivationRequirementData) {
            $modifierActivationRequirement = new ModifierActivationRequirement($modifierActivationRequirementData['activationRequirementName']);

            $modifierActivationRequirement
                ->setName($modifierActivationRequirementData['name'])
                ->setActivationRequirement($modifierActivationRequirementData['activationRequirement'])
                ->setValue($modifierActivationRequirementData['value']);

            $manager->persist($modifierActivationRequirement);
            $this->modifierActivationRequirements[$modifierActivationRequirement->getName()] = $modifierActivationRequirement;
        }
        $manager->flush();
    }
}
