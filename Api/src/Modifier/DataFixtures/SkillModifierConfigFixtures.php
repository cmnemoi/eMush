<?php

declare(strict_types=1);

namespace Mush\Modifier\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;

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

        $manager->flush();
    }
}
