<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\ConfigData\ModifierActivationRequirementData;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;

/** @codeCoverageIgnore */
final class XylophModifierConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $kivancModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::DOUBLE_DECODE_BASE_SIGNAL)
        );
        $manager->persist($kivancModifier);
        $this->addReference($kivancModifier->getName(), $kivancModifier);

        /** @var ModifierActivationRequirement $playerIsNotMushRequirement */
        $playerIsPaolaRequirement = ModifierActivationRequirement::fromConfigData(
            ModifierActivationRequirementData::getByName(ModifierRequirementEnum::PLAYER_IS_PAOLA)
        );
        $manager->persist($playerIsPaolaRequirement);

        /** @var VariableEventConfig $plus8TriumphEventConfig */
        $plus8TriumphEventConfig = $this->getReference(EventConfigData::CHANGE_VARIABLE_PLAYER_PLUS_8_TRIUMPH_POINTS);
        $kivancTriumphModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLUS_8_TRIUMPH_POINTS_FOR_PAOLA)
        );
        $kivancTriumphModifier
            ->setTriggeredEvent($plus8TriumphEventConfig)
            ->addModifierRequirement($playerIsPaolaRequirement);
        $manager->persist($kivancTriumphModifier);
        $this->addReference($kivancTriumphModifier->getName(), $kivancTriumphModifier);

        $manager->flush();
    }
}
