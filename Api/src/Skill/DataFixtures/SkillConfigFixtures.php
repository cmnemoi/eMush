<?php

declare(strict_types=1);

namespace Mush\Skill\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Modifier\DataFixtures\SkillModifierConfigFixtures;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Dto\SkillConfigDto;
use Mush\Skill\Entity\SkillConfig;

/** @codeCoverageIgnore */
final class SkillConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (SkillConfigData::getAll() as $skillConfigDto) {
            $skillConfig = new SkillConfig(
                name: $skillConfigDto->name,
                modifierConfigs: $this->getModifierConfigsFromDto($skillConfigDto),
            );
            $manager->persist($skillConfig);
            $this->addReference($skillConfigDto->name->value, $skillConfig);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SkillModifierConfigFixtures::class,
        ];
    }

    /**
     * @return ArrayCollection<int, ModifierConfig>
     */
    private function getModifierConfigsFromDto(SkillConfigDto $skillConfigDto): ArrayCollection
    {
        $modifierConfigs = new ArrayCollection();
        foreach ($skillConfigDto->modifierConfigs as $modifierConfigName) {
            $modifierConfig = $this->getReference($modifierConfigName);
            if (!$modifierConfig) {
                throw new \RuntimeException("ModifierConfig {$modifierConfigName} not found for SkillConfig {$skillConfigDto->name}");
            }
            $modifierConfigs->add($modifierConfig);
        }

        return $modifierConfigs;
    }
}
