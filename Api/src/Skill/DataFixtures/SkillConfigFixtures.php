<?php

declare(strict_types=1);

namespace Mush\Skill\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\DataFixtures\SkillModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Dto\SkillConfigDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Status\DataFixtures\SkillPointsFixtures;
use Mush\Status\Entity\Config\ChargeStatusConfig;

/** @codeCoverageIgnore */
final class SkillConfigFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @psalm-suppress InvalidArgument
     */
    public function load(ObjectManager $manager): void
    {
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (SkillConfigData::getAll() as $skillConfigDto) {
            $skillConfig = new SkillConfig(
                name: $skillConfigDto->name,
                modifierConfigs: $this->getModifierConfigsFromDto($skillConfigDto),
                actionConfigs: $this->getActionConfigsFromDto($skillConfigDto),
                skillPointsConfig: $this->getSkillPointsConfigFromDto($skillConfigDto),
            );
            $manager->persist($skillConfig);
            $this->addReference($skillConfigDto->name->value, $skillConfig);

            if ($skillConfig->getName()->isMushSkill()) {
                $gameConfig->addMushSkillConfig($skillConfig);
                $manager->persist($gameConfig);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            GameConfigFixtures::class,
            SkillModifierConfigFixtures::class,
            GearModifierConfigFixtures::class,
            SkillPointsFixtures::class,
        ];
    }

    /**
     * @return ArrayCollection<int, ActionConfig>
     */
    private function getActionConfigsFromDto(SkillConfigDto $skillConfigDto): ArrayCollection
    {
        /** @var ArrayCollection<int, ActionConfig> $actionConfigs */
        $actionConfigs = new ArrayCollection();
        foreach ($skillConfigDto->actionConfigs as $actionConfigName) {
            /** @var ActionConfig $actionConfig */
            $actionConfig = $this->getReference($actionConfigName->value);
            if (!$actionConfig) {
                throw new \RuntimeException("ActionConfig {$actionConfigName} not found for SkillConfig {$skillConfigDto->name->toString()}");
            }
            $actionConfigs->add($actionConfig);
        }

        return $actionConfigs;
    }

    /**
     * @return ArrayCollection<int, AbstractModifierConfig>
     */
    private function getModifierConfigsFromDto(SkillConfigDto $skillConfigDto): ArrayCollection
    {
        /** @var ArrayCollection<int, AbstractModifierConfig> $modifierConfigs */
        $modifierConfigs = new ArrayCollection();
        foreach ($skillConfigDto->modifierConfigs as $modifierConfigName) {
            /** @var AbstractModifierConfig $modifierConfig */
            $modifierConfig = $this->getReference($modifierConfigName);
            if (!$modifierConfig) {
                throw new \RuntimeException("ModifierConfig {$modifierConfigName} not found for SkillConfig {$skillConfigDto->name->toString()}");
            }
            $modifierConfigs->add($modifierConfig);
        }

        return $modifierConfigs;
    }

    /**
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     */
    private function getSkillPointsConfigFromDto(SkillConfigDto $skillConfigDto): ?ChargeStatusConfig
    {
        $configName = $skillConfigDto->skillPointsConfig?->value;
        if (!$configName) {
            return null;
        }

        return $this->getReference($configName);
    }
}
