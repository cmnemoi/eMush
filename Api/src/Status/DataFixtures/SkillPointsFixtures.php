<?php

declare(strict_types=1);

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
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
            StatusConfigData::getByName(SkillPointsEnum::CONCEPTOR_POINTS->value)
        );
        $conceptorPoints->setModifierConfigs([$conceptorPointsModifier]);
        $manager->persist($conceptorPoints);

        $shooterPointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_shooter_skill_point')
        );
        $manager->persist($shooterPointsModifier);

        $shooterPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::SHOOTER_POINTS->value)
        );
        $shooterPoints->setModifierConfigs([$shooterPointsModifier]);
        $manager->persist($shooterPoints);

        $technicianPointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('modifier_skill_point_engineer')
        );
        $manager->persist($technicianPointsModifier);

        $technicianPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::TECHNICIAN_POINTS->value)
        );
        $technicianPoints->setModifierConfigs([$technicianPointsModifier]);
        $manager->persist($technicianPoints);

        $itExpertPointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::SKILL_POINT_IT_EXPERT)
        );
        $manager->persist($itExpertPointsModifier);

        $itExpertPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::IT_EXPERT_POINTS->value)
        );
        $itExpertPoints->setModifierConfigs([$itExpertPointsModifier]);
        $manager->persist($itExpertPoints);

        $botanistModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::SKILL_POINT_BOTANIST)
        );
        $manager->persist($botanistModifier);

        $botanistPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::BOTANIST_POINTS->value)
        );
        $botanistPoints->setModifierConfigs([$botanistModifier]);
        $manager->persist($botanistPoints);

        $pilgredPointsModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::SKILL_POINT_PILGRED)
        );
        $manager->persist($pilgredPointsModifier);

        $pilgredPoints = ChargeStatusConfig::fromConfigData(
            StatusConfigData::getByName(SkillPointsEnum::PILGRED_POINTS->value)
        );
        $pilgredPoints->setModifierConfigs([$pilgredPointsModifier]);
        $manager->persist($pilgredPoints);

        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $gameConfig
            ->addStatusConfig($conceptorPoints)
            ->addStatusConfig($shooterPoints)
            ->addStatusConfig($technicianPoints)
            ->addStatusConfig($itExpertPoints)
            ->addStatusConfig($botanistPoints)
            ->addStatusConfig($pilgredPoints);

        $manager->persist($gameConfig);

        $manager->flush();

        $this->addReference($conceptorPoints->getName(), $conceptorPoints);
        $this->addReference($shooterPoints->getName(), $shooterPoints);
        $this->addReference($technicianPoints->getName(), $technicianPoints);
        $this->addReference($itExpertPoints->getName(), $itExpertPoints);
        $this->addReference($botanistPoints->getName(), $botanistPoints);
        $this->addReference($pilgredPoints->getName(), $pilgredPoints);
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
