<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;

/** @codeCoverageIgnore */
final class RebelBaseModifierConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $siriusActionPointModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_1_ACTION_POINT_ON_CONSUME_ACTION_IF_STANDARD_RATION)
        );
        $manager->persist($siriusActionPointModifier);
        $this->addReference($siriusActionPointModifier->getName(), $siriusActionPointModifier);

        /** @var VariableEventConfig $plus6MoralEventConfig */
        $plus6MoralEventConfig = $this->getReference(EventConfigData::CHANGE_VARIABLE_PLAYER_PLUS_6_MORALE_POINTS);
        $kaladaanMoralModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLUS_6_MORALE_POINTS_FOR_ALL_PLAYERS)
        );
        $kaladaanMoralModifier->setTriggeredEvent($plus6MoralEventConfig);
        $manager->persist($kaladaanMoralModifier);
        $this->addReference($kaladaanMoralModifier->getName(), $kaladaanMoralModifier);

        $centauriActionPointModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::BLASTER_PLUS_1_STRENGTH_IN_EXPEDITION)
        );
        $manager->persist($centauriActionPointModifier);
        $this->addReference($centauriActionPointModifier->getName(), $centauriActionPointModifier);

        /** @var VariableEventConfig $plus3MoralEventConfig */
        $plus3MoralEventConfig = $this->getReference(EventConfigData::CHANGE_VARIABLE_PLAYER_PLUS_3_MORALE_POINT);
        $cygniMoralModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::CYGNI_PLUS_3_MORALE_POINTS_FOR_ALL_PLAYERS)
        );
        $cygniMoralModifier->setTriggeredEvent($plus3MoralEventConfig);
        $manager->persist($cygniMoralModifier);
        $this->addReference($cygniMoralModifier->getName(), $cygniMoralModifier);

        $cygniPatrolShipDamageModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::CYGNI_PLUS_1_DAMAGE_PATROL_SHIPS)
        );
        $manager->persist($cygniPatrolShipDamageModifier);
        $this->addReference($cygniPatrolShipDamageModifier->getName(), $cygniPatrolShipDamageModifier);

        $manager->flush();
    }
}
