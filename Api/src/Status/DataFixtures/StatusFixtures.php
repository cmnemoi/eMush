<?php

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\DataFixtures\StatusModifierConfigFixtures;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Status\Entity\Config\ContentStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class StatusFixtures extends Fixture implements DependentFixtureInterface
{
    public const ALIEN_ARTEFACT_STATUS = 'alien_artefact_status';
    public const HEAVY_STATUS = 'heavy_status';
    public const MODULE_ACCESS_STATUS = 'module_access_status';
    public const HIDDEN_STATUS = 'hidden_status';
    public const BROKEN_STATUS = 'broken_status';
    public const UNSTABLE_STATUS = 'unstable_status';
    public const HAZARDOUS_STATUS = 'hazardous_status';
    public const DECOMPOSING_STATUS = 'decomposing_status';
    public const FROZEN_STATUS = 'frozen_status';
    public const PLANT_THIRSTY_STATUS = 'plant_thirsty_status';
    public const PLANT_DRY_STATUS = 'plant_dry_status';
    public const PLANT_DISEASED_STATUS = 'plant_diseased_status';
    public const DOCUMENT_CONTENT_STATUS = 'document_content_status';
    public const REINFORCED_STATUS = 'reinforced_status';
    public const ANTISOCIAL_STATUS = 'antisocial_status';
    public const BERZERK_STATUS = 'berzerk_status';
    public const BRAINSYNC_STATUS = 'brainsync_status';
    public const BURDENED_STATUS = 'burdened_status';
    public const DEMORALIZED_STATUS = 'demoralized_status';
    public const DIRTY_STATUS = 'dirty_status';
    public const DISABLED_STATUS = 'disabled_status';
    public const FOCUSED_STATUS = 'focused_status';
    public const FULL_STOMACH_STATUS = 'full_stomach_status';
    public const GAGGED_STATUS = 'gagged_status';
    public const GERMAPHOBE_STATUS = 'germaphobe_status';
    public const GUARDIAN_STATUS = 'guardian_status';
    public const HIGHLY_INACTIVE_STATUS = 'highly_inactive_status';
    public const HYPERACTIVE_STATUS = 'hyperactive_status';
    public const IMMUNIZED_STATUS = 'immunized_status';
    public const INACTIVE_STATUS = 'inactive_status';
    public const LOST_STATUS = 'lost_status';
    public const LYING_DOWN_STATUS = 'lying_down_status';
    public const MULTI_TEAMSTER_STATUS = 'multi_teamster_status';
    public const OUTCAST_STATUS = 'outcast_status';
    public const PACIFIST_STATUS = 'pacifist_status';
    public const PREGNANT_STATUS = 'pregnant_status';
    public const STARVING_STATUS = 'starving_status';
    public const STUCK_IN_THE_SHIP_STATUS = 'stuck_in_the_ship_status';
    public const SUICIDAL_STATUS = 'suicidal_status';
    public const WATCHED_PUBLIC_BROADCAST_STATUS = 'watched_public_broadcast_status';
    public const TALKIE_SCREWED_STATUS = 'talkie_screwed_status';
    public const IN_ORBIT_STATUS = 'in_orbit_status';
    public const POC_PILOT_SKILL_STATUS = 'poc_pilot_skill_status';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var VariableEventModifierConfig $increaseCycleDiseaseChances30 */
        $increaseCycleDiseaseChances30 = $this->getReference(StatusModifierConfigFixtures::INCREASE_CYCLE_DISEASE_CHANCES_30);

        $alienArtefact = new StatusConfig();
        $alienArtefact
            ->setStatusName(EquipmentStatusEnum::ALIEN_ARTEFACT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($alienArtefact);

        $heavy = new StatusConfig();
        $heavy
            ->setStatusName(EquipmentStatusEnum::HEAVY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($heavy);

        $moduleAccess = new StatusConfig();
        $moduleAccess
            ->setStatusName(EquipmentStatusEnum::MODULE_ACCESS)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($moduleAccess);

        $hidden = new StatusConfig();
        $hidden
            ->setStatusName(EquipmentStatusEnum::HIDDEN)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($hidden);

        $broken = new StatusConfig();
        $broken
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($broken);

        $unstable = new StatusConfig();
        $unstable
            ->setStatusName(EquipmentStatusEnum::UNSTABLE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($unstable);

        $hazardous = new StatusConfig();
        $hazardous
            ->setStatusName(EquipmentStatusEnum::HAZARDOUS)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($hazardous);

        $decomposing = new StatusConfig();
        $decomposing
            ->setStatusName(EquipmentStatusEnum::DECOMPOSING)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($decomposing);

        /** @var VariableEventModifierConfig $frozenModifier */
        $frozenModifier = $this->getReference(StatusModifierConfigFixtures::FROZEN_MODIFIER);

        $frozen = new StatusConfig();
        $frozen
            ->setStatusName(EquipmentStatusEnum::FROZEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$frozenModifier])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($frozen);

        $plantThirsty = new StatusConfig();
        $plantThirsty
            ->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($plantThirsty);

        $plantDry = new StatusConfig();
        $plantDry
            ->setStatusName(EquipmentStatusEnum::PLANT_DRY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($plantDry);

        $plantDiseased = new StatusConfig();
        $plantDiseased
            ->setStatusName(EquipmentStatusEnum::PLANT_DISEASED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($plantDiseased);

        $documentContent = new ContentStatusConfig();
        $documentContent
            ->setStatusName(EquipmentStatusEnum::DOCUMENT_CONTENT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($documentContent);

        $reinforced = new StatusConfig();
        $reinforced
            ->setStatusName(EquipmentStatusEnum::REINFORCED)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($reinforced);

        /** @var VariableEventModifierConfig $antisocialModifier */
        $antisocialModifier = $this->getReference(StatusModifierConfigFixtures::ANTISOCIAL_MODIFIER);

        $antisocial = new StatusConfig();
        $antisocial
            ->setStatusName(PlayerStatusEnum::ANTISOCIAL)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$antisocialModifier])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($antisocial);

        $berzerk = new StatusConfig();
        $berzerk
            ->setStatusName(PlayerStatusEnum::BERZERK)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($berzerk);

        $brainsync = new StatusConfig();
        $brainsync
            ->setStatusName(PlayerStatusEnum::BRAINSYNC)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($brainsync);

        /** @var VariableEventModifierConfig $burdenedModifier */
        $burdenedModifier = $this->getReference(StatusModifierConfigFixtures::BURDENED_MODIFIER);
        $burdened = new StatusConfig();
        $burdened
            ->setStatusName(PlayerStatusEnum::BURDENED)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setModifierConfigs([$burdenedModifier])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($burdened);

        $demoralized = new StatusConfig();
        $demoralized
            ->setStatusName(PlayerStatusEnum::DEMORALIZED)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setModifierConfigs([$increaseCycleDiseaseChances30])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($demoralized);

        $dirty = new StatusConfig();
        $dirty
            ->setStatusName(PlayerStatusEnum::DIRTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($dirty);

        /** @var VariableEventModifierConfig $disabledConversionModifier */
        $disabledConversionModifier = $this->getReference(StatusModifierConfigFixtures::DISABLED_CONVERSION_MODIFIER);
        /** @var VariableEventModifierConfig $disabledNotAloneModifier */
        $disabledNotAloneModifier = $this->getReference(StatusModifierConfigFixtures::DISABLED_NOT_ALONE_MODIFIER);
        $disabled = new StatusConfig();
        $disabled
            ->setStatusName(PlayerStatusEnum::DISABLED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$disabledNotAloneModifier, $disabledConversionModifier])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($disabled);

        $focused = new StatusConfig();
        $focused
            ->setStatusName(PlayerStatusEnum::FOCUSED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($focused);

        $fullStomach = new StatusConfig();
        $fullStomach
            ->setStatusName(PlayerStatusEnum::FULL_STOMACH)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($fullStomach);

        $gagged = new StatusConfig();
        $gagged
            ->setStatusName(PlayerStatusEnum::GAGGED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($gagged);

        $germaphobe = new StatusConfig();
        $germaphobe
            ->setStatusName(PlayerStatusEnum::GERMAPHOBE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($germaphobe);

        $guardian = new StatusConfig();
        $guardian
            ->setStatusName(PlayerStatusEnum::GUARDIAN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($guardian);

        $highlyInactive = new StatusConfig();
        $highlyInactive
            ->setStatusName(PlayerStatusEnum::HIGHLY_INACTIVE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($highlyInactive);

        $hyperactive = new StatusConfig();
        $hyperactive
            ->setStatusName(PlayerStatusEnum::HYPERACTIVE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($hyperactive);

        $immunized = new StatusConfig();
        $immunized
            ->setStatusName(PlayerStatusEnum::IMMUNIZED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($immunized);

        $inactive = new StatusConfig();
        $inactive
            ->setStatusName(PlayerStatusEnum::INACTIVE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($inactive);

        /** @var VariableEventModifierConfig $lostModifier */
        $lostModifier = $this->getReference(StatusModifierConfigFixtures::LOST_MODIFIER);

        $lost = new StatusConfig();
        $lost
            ->setStatusName(PlayerStatusEnum::LOST)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$lostModifier])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($lost);

        /** @var VariableEventModifierConfig $lyingDownModifier */
        $lyingDownModifier = $this->getReference(StatusModifierConfigFixtures::LYING_DOWN_MODIFIER);
        $lyingDown = new StatusConfig();
        $lyingDown
            ->setStatusName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$lyingDownModifier])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($lyingDown);

        $multiTeamster = new StatusConfig();
        $multiTeamster
            ->setStatusName(PlayerStatusEnum::MULTI_TEAMSTER)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($multiTeamster);

        $outcast = new StatusConfig();
        $outcast
            ->setStatusName(PlayerStatusEnum::OUTCAST)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($outcast);

        /** @var VariableEventModifierConfig $pacifistModifier */
        $pacifistModifier = $this->getReference(StatusModifierConfigFixtures::PACIFIST_MODIFIER);
        $pacifist = new StatusConfig();
        $pacifist
            ->setStatusName(PlayerStatusEnum::PACIFIST)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$pacifistModifier])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($pacifist);

        $pregnant = new StatusConfig();
        $pregnant
            ->setStatusName(PlayerStatusEnum::PREGNANT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($pregnant);

        /** @var VariableEventModifierConfig $starvingModifier */
        $starvingModifier = $this->getReference(StatusModifierConfigFixtures::STARVING_MODIFIER);
        $starving = new StatusConfig();
        $starving
            ->setStatusName(PlayerStatusEnum::STARVING)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setModifierConfigs([$starvingModifier])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($starving);

        $stuckInTheShip = new StatusConfig();
        $stuckInTheShip
            ->setStatusName(PlayerStatusEnum::STUCK_IN_THE_SHIP)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($stuckInTheShip);

        $suicidal = new StatusConfig();
        $suicidal
            ->setStatusName(PlayerStatusEnum::SUICIDAL)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setModifierConfigs([$increaseCycleDiseaseChances30])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($suicidal);

        $watched_public_broadcast = new StatusConfig();
        $watched_public_broadcast
            ->setStatusName(PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($watched_public_broadcast);

        $screwedTalkie = new StatusConfig();
        $screwedTalkie
            ->setStatusName(PlayerStatusEnum::TALKIE_SCREWED)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($screwedTalkie);

        /** @var VariableEventModifierConfig $gravityConversionModifier */
        $gravityConversionModifier = $this->getReference(GearModifierConfigFixtures::GRAVITY_CONVERSION_MODIFIER);
        /** @var VariableEventModifierConfig $gravityCycleModifier */
        $gravityCycleModifier = $this->getReference(GearModifierConfigFixtures::GRAVITY_CYCLE_MODIFIER);
        $noGravity = new StatusConfig();
        $noGravity
            ->setStatusName(DaedalusStatusEnum::NO_GRAVITY)
            ->setModifierConfigs([$gravityConversionModifier, $gravityCycleModifier])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($noGravity);

        $inOrbit = new StatusConfig();
        $inOrbit
            ->setStatusName(DaedalusStatusEnum::IN_ORBIT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($inOrbit);

        $pocPilotSkill = new StatusConfig();
        $pocPilotSkill
            ->setStatusName(PlayerStatusEnum::POC_PILOT_SKILL)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($pocPilotSkill);

        $gameConfig
            ->addStatusConfig($noGravity)
            ->addStatusConfig($alienArtefact)
            ->addStatusConfig($heavy)
            ->addStatusConfig($moduleAccess)
            ->addStatusConfig($hidden)
            ->addStatusConfig($broken)
            ->addStatusConfig($unstable)
            ->addStatusConfig($hazardous)
            ->addStatusConfig($decomposing)
            ->addStatusConfig($frozen)
            ->addStatusConfig($plantThirsty)
            ->addStatusConfig($plantDry)
            ->addStatusConfig($plantDiseased)
            ->addStatusConfig($documentContent)
            ->addStatusConfig($reinforced)
            ->addStatusConfig($antisocial)
            ->addStatusConfig($berzerk)
            ->addStatusConfig($brainsync)
            ->addStatusConfig($burdened)
            ->addStatusConfig($demoralized)
            ->addStatusConfig($dirty)
            ->addStatusConfig($disabled)
            ->addStatusConfig($focused)
            ->addStatusConfig($fullStomach)
            ->addStatusConfig($gagged)
            ->addStatusConfig($germaphobe)
            ->addStatusConfig($guardian)
            ->addStatusConfig($highlyInactive)
            ->addStatusConfig($hyperactive)
            ->addStatusConfig($immunized)
            ->addStatusConfig($inactive)
            ->addStatusConfig($lost)
            ->addStatusConfig($lyingDown)
            ->addStatusConfig($multiTeamster)
            ->addStatusConfig($outcast)
            ->addStatusConfig($pacifist)
            ->addStatusConfig($pregnant)
            ->addStatusConfig($starving)
            ->addStatusConfig($stuckInTheShip)
            ->addStatusConfig($suicidal)
            ->addStatusConfig($watched_public_broadcast)
            ->addStatusConfig($screwedTalkie)
            ->addStatusConfig($inOrbit)
            ->addStatusConfig($pocPilotSkill)
        ;
        $manager->persist($gameConfig);

        $this->addReference(self::ALIEN_ARTEFACT_STATUS, $alienArtefact);
        $this->addReference(self::HEAVY_STATUS, $heavy);
        $this->addReference(self::MODULE_ACCESS_STATUS, $moduleAccess);
        $this->addReference(self::HIDDEN_STATUS, $hidden);
        $this->addReference(self::BROKEN_STATUS, $broken);
        $this->addReference(self::UNSTABLE_STATUS, $unstable);
        $this->addReference(self::HAZARDOUS_STATUS, $hazardous);
        $this->addReference(self::DECOMPOSING_STATUS, $decomposing);
        $this->addReference(self::FROZEN_STATUS, $frozen);
        $this->addReference(self::PLANT_THIRSTY_STATUS, $plantThirsty);
        $this->addReference(self::PLANT_DRY_STATUS, $plantDry);
        $this->addReference(self::PLANT_DISEASED_STATUS, $plantDiseased);
        $this->addReference(self::DOCUMENT_CONTENT_STATUS, $documentContent);
        $this->addReference(self::REINFORCED_STATUS, $reinforced);
        $this->addReference(self::ANTISOCIAL_STATUS, $antisocial);
        $this->addReference(self::BERZERK_STATUS, $berzerk);
        $this->addReference(self::BRAINSYNC_STATUS, $brainsync);
        $this->addReference(self::BURDENED_STATUS, $burdened);
        $this->addReference(self::DEMORALIZED_STATUS, $demoralized);
        $this->addReference(self::DIRTY_STATUS, $dirty);
        $this->addReference(self::DISABLED_STATUS, $disabled);
        $this->addReference(self::FOCUSED_STATUS, $focused);
        $this->addReference(self::FULL_STOMACH_STATUS, $fullStomach);
        $this->addReference(self::GAGGED_STATUS, $gagged);
        $this->addReference(self::GERMAPHOBE_STATUS, $germaphobe);
        $this->addReference(self::GUARDIAN_STATUS, $guardian);
        $this->addReference(self::HIGHLY_INACTIVE_STATUS, $highlyInactive);
        $this->addReference(self::HYPERACTIVE_STATUS, $hyperactive);
        $this->addReference(self::IMMUNIZED_STATUS, $immunized);
        $this->addReference(self::INACTIVE_STATUS, $inactive);
        $this->addReference(self::LOST_STATUS, $lost);
        $this->addReference(self::LYING_DOWN_STATUS, $lyingDown);
        $this->addReference(self::MULTI_TEAMSTER_STATUS, $multiTeamster);
        $this->addReference(self::OUTCAST_STATUS, $outcast);
        $this->addReference(self::PACIFIST_STATUS, $pacifist);
        $this->addReference(self::PREGNANT_STATUS, $pregnant);
        $this->addReference(self::STARVING_STATUS, $starving);
        $this->addReference(self::STUCK_IN_THE_SHIP_STATUS, $stuckInTheShip);
        $this->addReference(self::SUICIDAL_STATUS, $suicidal);
        $this->addReference(self::WATCHED_PUBLIC_BROADCAST_STATUS, $watched_public_broadcast);
        $this->addReference(self::TALKIE_SCREWED_STATUS, $screwedTalkie);
        $this->addReference(self::IN_ORBIT_STATUS, $inOrbit);
        $this->addReference(self::POC_PILOT_SKILL_STATUS, $pocPilotSkill);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            StatusModifierConfigFixtures::class,
            GearModifierConfigFixtures::class,
        ];
    }
}
