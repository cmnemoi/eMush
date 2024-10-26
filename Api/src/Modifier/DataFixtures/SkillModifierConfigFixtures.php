<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Status\Enum\PlayerStatusEnum;

/** @codeCoverageIgnore */
final class SkillModifierConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
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

        $modifierAlwaysSucceedExtinguish = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_for_player_always_success_extinguish')
        );
        $this->addReference($modifierAlwaysSucceedExtinguish->getName(), $modifierAlwaysSucceedExtinguish);
        $manager->persist($modifierAlwaysSucceedExtinguish);

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

        $modifierPlayerPlus1MoralePointOnDayChange = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_1_MORALE_POINT_ON_DAY_CHANGE)
        );
        $this->addReference($modifierPlayerPlus1MoralePointOnDayChange->getName(), $modifierPlayerPlus1MoralePointOnDayChange);
        $manager->persist($modifierPlayerPlus1MoralePointOnDayChange);

        $modifierPlayerDoubleSuccessRateOnShootHunter = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_DOUBLE_SUCCESS_RATE_ON_SHOOT_HUNTER)
        );
        $this->addReference($modifierPlayerDoubleSuccessRateOnShootHunter->getName(), $modifierPlayerDoubleSuccessRateOnShootHunter);
        $manager->persist($modifierPlayerDoubleSuccessRateOnShootHunter);

        $modifierPlayerDoubleDamageOnShootHunter = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_DOUBLE_DAMAGE_ON_SHOOT_HUNTER)
        );
        $this->addReference($modifierPlayerDoubleDamageOnShootHunter->getName(), $modifierPlayerDoubleDamageOnShootHunter);
        $manager->persist($modifierPlayerDoubleDamageOnShootHunter);

        $playerPlusOneDamageOnHit = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_1_DAMAGE_ON_HIT)
        );
        $this->addReference($playerPlusOneDamageOnHit->getName(), $playerPlusOneDamageOnHit);
        $manager->persist($playerPlusOneDamageOnHit);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigData::CHANGE_VALUE_PLUS_1_MAX_DAEDALUS_SPORE);

        $modifierDaedalusPlus1MaxSpores = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::DAEDALUS_PLUS_1_MAX_SPORES)
        );
        $modifierDaedalusPlus1MaxSpores->setTriggeredEvent($eventConfig);
        $this->addReference($modifierDaedalusPlus1MaxSpores->getName(), $modifierDaedalusPlus1MaxSpores);
        $manager->persist($modifierDaedalusPlus1MaxSpores);

        $modifierPreventMushShowerMalus = EventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PREVENT_MUSH_SHOWER_MALUS)
        );
        $this->addReference($modifierPreventMushShowerMalus->getName(), $modifierPreventMushShowerMalus);
        $manager->persist($modifierPreventMushShowerMalus);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigData::CHANGE_VALUE_PLUS_1_CHARGE_MUSH_STATUS);

        $modifierPlayerPlus1Infection = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_1_INFECTION)
        );
        $modifierPlayerPlus1Infection->setTriggeredEvent($eventConfig);
        $this->addReference($modifierPlayerPlus1Infection->getName(), $modifierPlayerPlus1Infection);
        $manager->persist($modifierPlayerPlus1Infection);

        $modifierMinus25PercentageOnActionHitAndAttack = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_MINUS_25_PERCENTAGE_ON_ACTION_HIT_AND_ATTACK)
        );
        $this->addReference($modifierMinus25PercentageOnActionHitAndAttack->getName(), $modifierMinus25PercentageOnActionHitAndAttack);
        $manager->persist($modifierMinus25PercentageOnActionHitAndAttack);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference('change.variable_player_+1moralePoint');

        /** @var ModifierActivationRequirement $lyingDownActivationRequirement */
        $lyingDownActivationRequirement = $this->getReference(ModifierRequirementEnum::HOLDER_HAS_STATUS . '_' . PlayerStatusEnum::LYING_DOWN);

        $shrinkModifier = TriggerEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_for_player_+1morale_point_on_new_cycle_if_lying_down')
        );
        $shrinkModifier->setTriggeredEvent($eventConfig);
        $shrinkModifier->setEventTargetRequirements([
            $lyingDownActivationRequirement,
        ]);

        $this->addReference($shrinkModifier->getName(), $shrinkModifier);
        $manager->persist($shrinkModifier);

        $playerPlusTwoDamageOnHit = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_2_DAMAGE_ON_HIT)
        );
        $this->addReference($playerPlusTwoDamageOnHit->getName(), $playerPlusTwoDamageOnHit);
        $manager->persist($playerPlusTwoDamageOnHit);

        $sprinterModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_2_MOVEMENT_POINT_ON_EVENT_ACTION_MOVEMENT_CONVERSION_FOR_SPRINTER)
        );
        $this->addReference($sprinterModifier->getName(), $sprinterModifier);
        $manager->persist($sprinterModifier);

        $devotionModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_3_ACTION_POINT_ON_ACCEPT_MISSION)
        );
        $this->addReference($devotionModifier->getName(), $devotionModifier);
        $manager->persist($devotionModifier);

        $observantModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_MINUS_1_ACTION_POINT_ON_SEARCH)
        );
        $this->addReference($observantModifier->getName(), $observantModifier);
        $manager->persist($observantModifier);

        $caffeineJunkieModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_2_ACTION_POINTS_ON_CONSUME_ACTION_IF_COFFEE)
        );
        $this->addReference($caffeineJunkieModifier->getName(), $caffeineJunkieModifier);
        $manager->persist($caffeineJunkieModifier);

        $detachedCrewmemberModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_SET_0_MORALE_POINT_ON_DEATH)
        );
        $this->addReference($detachedCrewmemberModifier->getName(), $detachedCrewmemberModifier);
        $manager->persist($detachedCrewmemberModifier);

        $expertModifierOnActions = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_20_PERCENTAGE_ON_ACTIONS)
        );
        $this->addReference($expertModifierOnActions->getName(), $expertModifierOnActions);
        $manager->persist($expertModifierOnActions);

        $expertModifierOnClumsiness = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_20_PERCENTAGE_ON_CLUMSINESS)
        );
        $this->addReference($expertModifierOnClumsiness->getName(), $expertModifierOnClumsiness);
        $manager->persist($expertModifierOnClumsiness);

        $expertModifierOnDirtiness = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_20_PERCENTAGE_ON_DIRTINESS)
        );
        $this->addReference($expertModifierOnDirtiness->getName(), $expertModifierOnDirtiness);
        $manager->persist($expertModifierOnDirtiness);

        $frugivoreModifierForAlienFruits = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_2_ACTION_POINTS_ON_CONSUME_ACTION_IF_ALIEN_FRUIT)
        );
        $this->addReference($frugivoreModifierForAlienFruits->getName(), $frugivoreModifierForAlienFruits);
        $manager->persist($frugivoreModifierForAlienFruits);

        $frugivoreModifierForBanana = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_1_ACTION_POINTS_ON_CONSUME_ACTION_IF_BANANA)
        );
        $this->addReference($frugivoreModifierForBanana->getName(), $frugivoreModifierForBanana);
        $manager->persist($frugivoreModifierForBanana);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigData::CHANGE_VARIABLE_PLAYER_MINUS_1_SPORE);
        $mycologistModifier = TriggerEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_MINUS_1_SPORE_ON_HEAL)
        );
        $mycologistModifier->setTriggeredEvent($eventConfig);
        $this->addReference($mycologistModifier->getName(), $mycologistModifier);
        $manager->persist($mycologistModifier);

        $saboteurModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_DOUBLE_PERCENTAGE_ON_SABOTAGE)
        );
        $this->addReference($saboteurModifier->getName(), $saboteurModifier);
        $manager->persist($saboteurModifier);

        $coldBloodedModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_3_ACTION_POINT_ON_PLAYER_DEATH)
        );
        $this->addReference($coldBloodedModifier->getName(), $coldBloodedModifier);
        $manager->persist($coldBloodedModifier);

        $greenThumbModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLANT_SET_CHARGE_TO_ONE)
        );
        $this->addReference($greenThumbModifier->getName(), $greenThumbModifier);
        $manager->persist($greenThumbModifier);

        $strateguruModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_MINUS_33_PERCENTAGE_HUNTERS)
        );
        $this->addReference($strateguruModifier->getName(), $strateguruModifier);
        $manager->persist($strateguruModifier);

        $opportunistModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_PLUS_5_ACTION_POINT_ON_FIRST_TITLE_ATTRIBUTION)
        );
        $this->addReference($opportunistModifier->getName(), $opportunistModifier);
        $manager->persist($opportunistModifier);

        $crazyEyeModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::INCREASE_AGGRESIVE_ACTION_TO_SKILL_PLAYER_BY_2_AP)
        );
        $this->addReference($crazyEyeModifier->getName(), $crazyEyeModifier);
        $manager->persist($crazyEyeModifier);

        $radioExpertModifierOnSelf = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_X2_SIGNAL_ON_ACTION_CONTACT_SOL)
        );
        $this->addReference($radioExpertModifierOnSelf->getName(), $radioExpertModifierOnSelf);
        $manager->persist($radioExpertModifierOnSelf);

        $radioExpertModifierOnOtherPlayer = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLACE_X1_5_SIGNAL_ON_ACTION_CONTACT_SOL)
        );
        $this->addReference($radioExpertModifierOnOtherPlayer->getName(), $radioExpertModifierOnOtherPlayer);
        $manager->persist($radioExpertModifierOnOtherPlayer);

        $medicModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLUS_2_HEALTH_POINTS_ON_HEAL)
        );
        $this->addReference($medicModifier->getName(), $medicModifier);
        $manager->persist($medicModifier);

        $rebelModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::DOUBLE_DECODE_REBEL_SIGNAL)
        );
        $this->addReference($rebelModifier->getName(), $rebelModifier);
        $manager->persist($rebelModifier);

        $ocdModifier = EventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::OCD_MODIFIER_FOR_PLAYER_PREVENT_DIRTY)
        );
        $this->addReference($ocdModifier->getName(), $ocdModifier);
        $manager->persist($ocdModifier);

        /** @var VariableEventConfig $eventConfig */
        $eventConfig = $this->getReference(EventConfigData::CHANGE_VALUE_MAX_PLUS_12_MAX_ACTION_POINTS);

        $lethargyModifier = DirectModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::DOUBLE_MAX_ACTION_POINTS)
        );
        $lethargyModifier->setTriggeredEvent($eventConfig);
        $this->addReference($lethargyModifier->getName(), $lethargyModifier);
        $manager->persist($lethargyModifier);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DiseaseModifierConfigFixtures::class,
        ];
    }
}
