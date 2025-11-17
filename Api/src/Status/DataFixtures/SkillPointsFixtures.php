<?php

declare(strict_types=1);

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Modifier\ConfigData\ModifierActivationRequirementData;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Status\ConfigData\StatusConfigData;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\SkillPointsEnum;

final class SkillPointsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $conceptorPointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_skill_point_core')
        );
        $manager->persist($conceptorPointsModifier);

        $conceptorPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::CONCEPTOR_POINTS->toString())
        );
        $conceptorPoints->setModifierConfigs([$conceptorPointsModifier]);
        $manager->persist($conceptorPoints);

        $shooterPointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_shooter_skill_point')
        );
        $manager->persist($shooterPointsModifier);

        $shooterPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::SHOOTER_POINTS->toString())
        );
        $shooterPoints->setModifierConfigs([$shooterPointsModifier]);
        $manager->persist($shooterPoints);

        $technicianPointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_skill_point_engineer')
        );
        $manager->persist($technicianPointsModifier);

        $technicianPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::TECHNICIAN_POINTS->toString())
        );
        $technicianPoints->setModifierConfigs([$technicianPointsModifier]);
        $manager->persist($technicianPoints);

        $itExpertPointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_skill_point_it_expert')
        );
        $manager->persist($itExpertPointsModifier);

        $itExpertPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::IT_EXPERT_POINTS->toString())
        );
        $itExpertPoints->setModifierConfigs([$itExpertPointsModifier]);
        $manager->persist($itExpertPoints);

        $botanistModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::SKILL_POINT_BOTANIST)
        );
        $manager->persist($botanistModifier);

        $botanistPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::BOTANIST_POINTS->toString())
        );
        $botanistPoints->setModifierConfigs([$botanistModifier]);
        $manager->persist($botanistPoints);

        $pilgredPointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::SKILL_POINT_PILGRED)
        );
        $manager->persist($pilgredPointsModifier);

        $pilgredPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::PILGRED_POINTS->toString())
        );
        $pilgredPoints->setModifierConfigs([$pilgredPointsModifier]);
        $manager->persist($pilgredPoints);

        $nursePointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::SKILL_POINT_NURSE)
        );
        $manager->persist($nursePointsModifier);

        $nursePoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::NURSE_POINTS->toString())
        );
        $nursePoints->setModifierConfigs([$nursePointsModifier]);
        $manager->persist($nursePoints);

        $hasNotUsedFertileTodayRequirement = ModifierActivationRequirement::fromConfigData(
            ModifierActivationRequirementData::getByName(ModifierRequirementEnum::PLAYER_HAS_NOT_USED_FERTILE_TODAY)
        );
        $manager->persist($hasNotUsedFertileTodayRequirement);

        $sporePointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::SKILL_POINT_SPORE)
        );
        $sporePointsModifier->setModifierActivationRequirements([$hasNotUsedFertileTodayRequirement]);
        $manager->persist($sporePointsModifier);

        $sporePoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::SPORE_POINTS->toString())
        );
        $sporePoints->setModifierConfigs([$sporePointsModifier]);
        $manager->persist($sporePoints);

        $chefPointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::SKILL_POINT_CHEF)
        );
        $manager->persist($chefPointsModifier);

        $chefPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::CHEF_POINTS->toString())
        );
        $chefPoints->setModifierConfigs([$chefPointsModifier]);
        $manager->persist($chefPoints);

        $polymathItPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::POLYMATH_IT_POINTS->toString())
        );
        $polymathItPoints->setModifierConfigs([$itExpertPointsModifier]);
        $manager->persist($polymathItPoints);

        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $gameConfig
            ->addStatusConfig($conceptorPoints)
            ->addStatusConfig($shooterPoints)
            ->addStatusConfig($technicianPoints)
            ->addStatusConfig($itExpertPoints)
            ->addStatusConfig($botanistPoints)
            ->addStatusConfig($pilgredPoints)
            ->addStatusConfig($nursePoints)
            ->addStatusConfig($sporePoints)
            ->addStatusConfig($chefPoints)
            ->addStatusConfig($polymathItPoints);

        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference($conceptorPoints->getName(), $conceptorPoints);
        $this->addReference($shooterPoints->getName(), $shooterPoints);
        $this->addReference($technicianPoints->getName(), $technicianPoints);
        $this->addReference($itExpertPoints->getName(), $itExpertPoints);
        $this->addReference($botanistPoints->getName(), $botanistPoints);
        $this->addReference($pilgredPoints->getName(), $pilgredPoints);
        $this->addReference($nursePoints->getName(), $nursePoints);
        $this->addReference($sporePoints->getName(), $sporePoints);
        $this->addReference($chefPoints->getName(), $chefPoints);
        $this->addReference($polymathItPoints->getName(), $polymathItPoints);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
