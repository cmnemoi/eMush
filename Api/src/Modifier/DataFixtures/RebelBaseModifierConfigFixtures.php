<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;

/** @codeCoverageIgnore */
final class RebelBaseModifierConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /** @var VariableEventConfig $plus8TriumphEventConfig */
        $plus8TriumphEventConfig = $this->getReference(EventConfigData::CHANGE_VARIABLE_PLAYER_PLUS_8_TRIUMPH_POINTS);
        $wolfTriumphModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLUS_8_TRIUMPH_POINTS_FOR_ALL_PLAYERS)
        );
        $wolfTriumphModifier->setTriggeredEvent($plus8TriumphEventConfig);
        $manager->persist($wolfTriumphModifier);
        $this->addReference($wolfTriumphModifier->getName(), $wolfTriumphModifier);

        /** @var VariableEventConfig $plus6MoralEventConfig */
        $plus6MoralEventConfig = $this->getReference(EventConfigData::CHANGE_VARIABLE_PLAYER_PLUS_6_MORALE_POINTS);
        $kaladaanMoralModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLUS_6_MORALE_POINTS_FOR_ALL_PLAYERS)
        );
        $kaladaanMoralModifier->setTriggeredEvent($plus6MoralEventConfig);
        $manager->persist($kaladaanMoralModifier);
        $this->addReference($kaladaanMoralModifier->getName(), $kaladaanMoralModifier);

        $manager->flush();
    }
}
