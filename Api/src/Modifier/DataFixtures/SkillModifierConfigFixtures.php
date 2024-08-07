<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;

/** @codeCoverageIgnore */
final class SkillModifierConfigFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $pilotAlwaysCriticalSuccessPiloting = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_pilot_always_critical_success_piloting')
        );
        $this->addReference($pilotAlwaysCriticalSuccessPiloting->getName(), $pilotAlwaysCriticalSuccessPiloting);
        $manager->persist($pilotAlwaysCriticalSuccessPiloting);

        $pilotIncreasedShootHunterChances = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_pilot_increased_shoot_hunter_chances')
        );
        $this->addReference($pilotIncreasedShootHunterChances->getName(), $pilotIncreasedShootHunterChances);
        $manager->persist($pilotIncreasedShootHunterChances);

        $technicianDoubleRepairAndRenovateChance = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_technician_double_repair_and_renovate_chance')
        );
        $this->addReference($technicianDoubleRepairAndRenovateChance->getName(), $technicianDoubleRepairAndRenovateChance);
        $manager->persist($technicianDoubleRepairAndRenovateChance);

        $modifierForDaedalusPlus1MoralOnDayChange = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_for_daedalus_+1moral_on_day_change')
        );
        $this->addReference($modifierForDaedalusPlus1MoralOnDayChange->getName(), $modifierForDaedalusPlus1MoralOnDayChange);
        $manager->persist($modifierForDaedalusPlus1MoralOnDayChange);

        $modifierDoubleHackChance = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::DOUBLE_HACK_CHANCE)
        );
        $this->addReference($modifierDoubleHackChance->getName(), $modifierDoubleHackChance);
        $manager->persist($modifierDoubleHackChance);

        $modifierOneMoreSectionRevealedOnAnalyzePlanet = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_1_MORE_SECTION_REVEALED_ON_ANALYZE_PLANET)
        );
        $this->addReference($modifierOneMoreSectionRevealedOnAnalyzePlanet->getName(), $modifierOneMoreSectionRevealedOnAnalyzePlanet);
        $manager->persist($modifierOneMoreSectionRevealedOnAnalyzePlanet);

        $modifierMinusOneActionPointOnScan = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_MINUS_1_ACTION_POINT_ON_SCAN)
        );
        $this->addReference($modifierMinusOneActionPointOnScan->getName(), $modifierMinusOneActionPointOnScan);
        $manager->persist($modifierMinusOneActionPointOnScan);

        $modifierMinusOneActionPointOnScan = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_for_player_always_success_extinguish')
        );
        $this->addReference($modifierMinusOneActionPointOnScan->getName(), $modifierMinusOneActionPointOnScan);
        $manager->persist($modifierMinusOneActionPointOnScan);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigData::CHANGE_VARIABLE_PLAYER_PLUS_1_ACTION_POINT);
        $modifierForPlayerPlus1ActionPointOnPostActionIfSuccessful = TriggerEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_1_ACTION_POINT_ON_POST_ACTION_IF_FAILED)
        );
        $modifierForPlayerPlus1ActionPointOnPostActionIfSuccessful
            ->setTriggeredEvent($eventConfig)
            ->setModifierActivationRequirements([]);

        $this->addReference($modifierForPlayerPlus1ActionPointOnPostActionIfSuccessful->getName(), $modifierForPlayerPlus1ActionPointOnPostActionIfSuccessful);
        $manager->persist($modifierForPlayerPlus1ActionPointOnPostActionIfSuccessful);

        $modifierPlusOneHealthPointOnChangeVariableIfFromPlanetSectorEvent = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_1_HEALTH_POINT_ON_CHANGE_VARIABLE_IF_FROM_PLANET_SECTOR_EVENT)
        );
        $this->addReference($modifierPlusOneHealthPointOnChangeVariableIfFromPlanetSectorEvent->getName(), $modifierPlusOneHealthPointOnChangeVariableIfFromPlanetSectorEvent);
        $manager->persist($modifierPlusOneHealthPointOnChangeVariableIfFromPlanetSectorEvent);

        $manager->flush();
    }
}
