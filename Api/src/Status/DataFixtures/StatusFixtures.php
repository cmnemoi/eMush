<?php

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\DataFixtures\InjuryModifierConfigFixtures;
use Mush\Modifier\DataFixtures\StatusModifierConfigFixtures;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Status\ConfigData\StatusConfigData;
use Mush\Status\Entity\Config\ContentStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class StatusFixtures extends Fixture implements DependentFixtureInterface
{
    public const string ALIEN_ARTEFACT_STATUS = 'alien_artefact_status';
    public const string HEAVY_STATUS = 'heavy_status';
    public const string MODULE_ACCESS_STATUS = 'module_access_status';
    public const string HIDDEN_STATUS = 'hidden_status';
    public const string BROKEN_STATUS = 'broken_status';
    public const string UNSTABLE_STATUS = 'unstable_status';
    public const string HAZARDOUS_STATUS = 'hazardous_status';
    public const string DECOMPOSING_STATUS = 'decomposing_status';
    public const string FROZEN_STATUS = 'frozen_status';
    public const string PLANT_THIRSTY_STATUS = 'plant_thirsty_status';
    public const string PLANT_DRY_STATUS = 'plant_dry_status';
    public const string PLANT_DISEASED_STATUS = 'plant_diseased_status';
    public const string DOCUMENT_CONTENT_STATUS = 'document_content_status';
    public const string REINFORCED_STATUS = 'reinforced_status';
    public const string ANTISOCIAL_STATUS = 'antisocial_status';
    public const string BERZERK_STATUS = 'berzerk_status';
    public const string BRAINSYNC_STATUS = 'brainsync_status';
    public const string BURDENED_STATUS = 'burdened_status';
    public const string DEMORALIZED_STATUS = 'demoralized_status';
    public const string DIRTY_STATUS = 'dirty_status';
    public const string DISABLED_STATUS = 'disabled_status';
    public const string FOCUSED_STATUS = 'focused_status';
    public const string FULL_STOMACH_STATUS = 'full_stomach_status';
    public const string GAGGED_STATUS = 'gagged_status';
    public const string GERMAPHOBE_STATUS = 'germaphobe_status';
    public const string GUARDIAN_STATUS = 'guardian_status';
    public const string HIGHLY_INACTIVE_STATUS = 'highly_inactive_status';
    public const string HYPERACTIVE_STATUS = 'hyperactive_status';
    public const string IMMUNIZED_STATUS = 'immunized_status';
    public const string INACTIVE_STATUS = 'inactive_status';
    public const string LOST_STATUS = 'lost_status';
    public const string MULTI_TEAMSTER_STATUS = 'multi_teamster_status';
    public const string OUTCAST_STATUS = 'outcast_status';
    public const string PACIFIST_STATUS = 'pacifist_status';
    public const string PREGNANT_STATUS = 'pregnant_status';
    public const string STARVING_WARNING_STATUS = 'starving_warning_status';
    public const string STARVING_STATUS = 'starving_status';
    public const string STUCK_IN_THE_SHIP_STATUS = 'stuck_in_the_ship_status';
    public const string SUICIDAL_STATUS = 'suicidal_status';
    public const string WATCHED_PUBLIC_BROADCAST_STATUS = 'watched_public_broadcast_status';
    public const string TALKIE_SCREWED_STATUS = 'talkie_screwed_status';
    public const string IN_ORBIT_STATUS = 'in_orbit_status';
    public const string POC_PILOT_SKILL_STATUS = 'poc_pilot_skill_status';
    public const string ASTRONAVIGATION_NERON_CPU_PRIORITY_STATUS = 'astronavigation_neron_cpu_priority_status';

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
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($alienArtefact);

        $heavy = new StatusConfig();
        $heavy
            ->setStatusName(EquipmentStatusEnum::HEAVY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($heavy);

        $moduleAccess = new StatusConfig();
        $moduleAccess
            ->setStatusName(EquipmentStatusEnum::MODULE_ACCESS)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($moduleAccess);

        $hidden = new StatusConfig();
        $hidden
            ->setStatusName(EquipmentStatusEnum::HIDDEN)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($hidden);

        $broken = new StatusConfig();
        $broken
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($broken);

        $unstable = new StatusConfig();
        $unstable
            ->setStatusName(EquipmentStatusEnum::UNSTABLE)
            ->setVisibility(VisibilityEnum::CHEF)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($unstable);

        $hazardous = new StatusConfig();
        $hazardous
            ->setStatusName(EquipmentStatusEnum::HAZARDOUS)
            ->setVisibility(VisibilityEnum::CHEF)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($hazardous);

        $decomposing = new StatusConfig();
        $decomposing
            ->setStatusName(EquipmentStatusEnum::DECOMPOSING)
            ->setVisibility(VisibilityEnum::CHEF)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($decomposing);

        /** @var VariableEventModifierConfig $frozenModifier */
        $frozenModifier = $this->getReference(StatusModifierConfigFixtures::FROZEN_MODIFIER);

        $frozen = new StatusConfig();
        $frozen
            ->setStatusName(EquipmentStatusEnum::FROZEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$frozenModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($frozen);

        $plantThirsty = new StatusConfig();
        $plantThirsty
            ->setStatusName(EquipmentStatusEnum::PLANT_THIRSTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($plantThirsty);

        $plantDry = new StatusConfig();
        $plantDry
            ->setStatusName(EquipmentStatusEnum::PLANT_DRY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($plantDry);

        $plantDiseased = new StatusConfig();
        $plantDiseased
            ->setStatusName(EquipmentStatusEnum::PLANT_DISEASED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($plantDiseased);

        $documentContent = new ContentStatusConfig();
        $documentContent
            ->setStatusName(EquipmentStatusEnum::DOCUMENT_CONTENT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($documentContent);

        $reinforced = new StatusConfig();
        $reinforced
            ->setStatusName(EquipmentStatusEnum::REINFORCED)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($reinforced);

        /** @var TriggerEventModifierConfig $antisocialModifier */
        $antisocialModifier = $this->getReference(StatusModifierConfigFixtures::ANTISOCIAL_MODIFIER);

        $antisocial = new StatusConfig();
        $antisocial
            ->setStatusName(PlayerStatusEnum::ANTISOCIAL)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$antisocialModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($antisocial);

        /** @var VariableEventModifierConfig $playerPlusOneDamageOnHit */
        $playerPlusOneDamageOnHit = $this->getReference(ModifierNameEnum::PLAYER_PLUS_1_DAMAGE_ON_HIT);

        $berzerk = new StatusConfig();
        $berzerk
            ->setStatusName(PlayerStatusEnum::BERZERK)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$playerPlusOneDamageOnHit])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($berzerk);

        $brainsync = new StatusConfig();
        $brainsync
            ->setStatusName(PlayerStatusEnum::BRAINSYNC)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($brainsync);

        /** @var VariableEventModifierConfig $burdenedModifier */
        $burdenedModifier = $this->getReference(StatusModifierConfigFixtures::BURDENED_MODIFIER);
        $burdened = new StatusConfig();
        $burdened
            ->setStatusName(PlayerStatusEnum::BURDENED)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setModifierConfigs([$burdenedModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($burdened);

        $demoralized = new StatusConfig();
        $demoralized
            ->setStatusName(PlayerStatusEnum::DEMORALIZED)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setModifierConfigs([$increaseCycleDiseaseChances30])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($demoralized);

        $dirty = new StatusConfig();
        $dirty
            ->setStatusName(PlayerStatusEnum::DIRTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($disabled);

        $focused = new StatusConfig();
        $focused
            ->setStatusName(PlayerStatusEnum::FOCUSED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($focused);

        $fullStomach = new StatusConfig();
        $fullStomach
            ->setStatusName(PlayerStatusEnum::FULL_STOMACH)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($fullStomach);

        /** @var EventModifierConfig $mutePreventSpokenAction */
        $mutePreventSpokenAction = $this->getReference(InjuryModifierConfigFixtures::PREVENT_SPOKEN);

        /** @var EventModifierConfig $muteModifier */
        $muteModifier = $this->getReference(InjuryModifierConfigFixtures::MUTE_MODIFIER);

        $gagged = new StatusConfig();
        $gagged
            ->setStatusName(PlayerStatusEnum::GAGGED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$mutePreventSpokenAction, $muteModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($gagged);

        /** @var TriggerEventModifierConfig $germaphobeModifier */
        $germaphobeModifier = $this->getReference(StatusModifierConfigFixtures::GERMAPHOBE_MODIFIER);
        $germaphobe = new StatusConfig();
        $germaphobe
            ->setStatusName(PlayerStatusEnum::GERMAPHOBE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$germaphobeModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($germaphobe);

        $guardian = new StatusConfig();
        $guardian
            ->setStatusName(PlayerStatusEnum::GUARDIAN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($guardian);

        /** @var VariableEventModifierConfig $inactiveModifier */
        $inactiveModifier = $this->getReference('modifier_for_player_x1.5percentage_on_action_attack_hit_shoot');

        $putThroughDoorModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName(ModifierNameEnum::PLAYER_MINUS_1_ACTION_POINT_ON_PUT_THROUGH_DOOR)
        );
        $manager->persist($putThroughDoorModifier);

        $highlyInactive = new StatusConfig();
        $highlyInactive
            ->setStatusName(PlayerStatusEnum::HIGHLY_INACTIVE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$inactiveModifier, $putThroughDoorModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($highlyInactive);

        /** @var VariableEventModifierConfig $hyperactiveModifier */
        $hyperactiveModifier = VariableEventModifierConfig::fromConfigData(
            ModifierConfigData::getByName('hyperactive_modifier_for_player_+1movementPoint_on_new_cycle')
        );
        $manager->persist($hyperactiveModifier);

        $hyperactive = new StatusConfig();
        $hyperactive
            ->setStatusName(PlayerStatusEnum::HYPERACTIVE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$hyperactiveModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($hyperactive);

        /** @var VariableEventModifierConfig $immunizedModifierSet0SporesOnChangeVariable */
        $immunizedModifierSet0SporesOnChangeVariable = $this->getReference(StatusModifierConfigFixtures::IMMUNIZED_MODIFIER_SET_0_SPORES_ON_CHANGE_VARIABLE);

        $immunized = new StatusConfig();
        $immunized
            ->setStatusName(PlayerStatusEnum::IMMUNIZED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$immunizedModifierSet0SporesOnChangeVariable])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($immunized);

        $inactive = new StatusConfig();
        $inactive
            ->setStatusName(PlayerStatusEnum::INACTIVE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$inactiveModifier, $putThroughDoorModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($inactive);

        /** @var VariableEventModifierConfig $lostModifier */
        $lostModifier = $this->getReference(StatusModifierConfigFixtures::LOST_MODIFIER);

        $lost = new StatusConfig();
        $lost
            ->setStatusName(PlayerStatusEnum::LOST)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$lostModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($lost);

        $multiTeamster = new StatusConfig();
        $multiTeamster
            ->setStatusName(PlayerStatusEnum::MULTI_TEAMSTER)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($multiTeamster);

        $outcast = new StatusConfig();
        $outcast
            ->setStatusName(PlayerStatusEnum::OUTCAST)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($outcast);

        /** @var VariableEventModifierConfig $pacifistModifier */
        $pacifistModifier = $this->getReference(StatusModifierConfigFixtures::PACIFIST_MODIFIER);
        $pacifist = new StatusConfig();
        $pacifist
            ->setStatusName(PlayerStatusEnum::PACIFIST)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs([$pacifistModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($pacifist);

        $pregnant = new StatusConfig();
        $pregnant
            ->setStatusName(PlayerStatusEnum::PREGNANT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($pregnant);

        $starvingWarning = new StatusConfig();
        $starvingWarning
            ->setStatusName(PlayerStatusEnum::STARVING_WARNING)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($starvingWarning);

        /** @var VariableEventModifierConfig $starvingModifier */
        $starvingModifier = $this->getReference(StatusModifierConfigFixtures::STARVING_MODIFIER);
        $starving = new StatusConfig();
        $starving
            ->setStatusName(PlayerStatusEnum::STARVING)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setModifierConfigs([$starvingModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($starving);

        $stuckInTheShip = new StatusConfig();
        $stuckInTheShip
            ->setStatusName(PlayerStatusEnum::STUCK_IN_THE_SHIP)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($stuckInTheShip);

        $suicidal = new StatusConfig();
        $suicidal
            ->setStatusName(PlayerStatusEnum::SUICIDAL)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setModifierConfigs([$increaseCycleDiseaseChances30])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($suicidal);

        $watched_public_broadcast = new StatusConfig();
        $watched_public_broadcast
            ->setStatusName(PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($watched_public_broadcast);

        $screwedTalkie = new StatusConfig();
        $screwedTalkie
            ->setStatusName(PlayerStatusEnum::TALKIE_SCREWED)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($screwedTalkie);

        /** @var VariableEventModifierConfig $gravityConversionModifier */
        $gravityConversionModifier = $this->getReference(GearModifierConfigFixtures::GRAVITY_CONVERSION_MODIFIER);

        /** @var VariableEventModifierConfig $gravityCycleModifier */
        $gravityCycleModifier = $this->getReference(GearModifierConfigFixtures::GRAVITY_CYCLE_MODIFIER);
        $noGravity = new StatusConfig();
        $noGravity
            ->setStatusName(DaedalusStatusEnum::NO_GRAVITY)
            ->setModifierConfigs([$gravityConversionModifier, $gravityCycleModifier])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($noGravity);

        /** @var VariableEventModifierConfig $astronavigatioNeronCpuPriorityModifierPlus1Section */
        $astronavigationNeronCpuPriorityModifierPlus1Section = $this->getReference(StatusModifierConfigFixtures::ASTRONAVIGATION_NERON_CPU_PRIORITY_MODIFIER_PLUS_1_SECTION);

        /** @var VariableEventModifierConfig $astronavigatioNeronCpuPriorityModifierMinus1ActionPoint */
        $astronavigationNeronCpuPriorityModifierMinus1ActionPoint = $this->getReference(StatusModifierConfigFixtures::ASTRONAVIGATION_NERON_CPU_PRIORITY_MODIFIER_MINUS_1_ACTION_POINT);

        /** @var array<int, VariableEventModifierConfig> $modifierConfigs */
        $modifierConfigs = [
            $astronavigationNeronCpuPriorityModifierPlus1Section,
            $astronavigationNeronCpuPriorityModifierMinus1ActionPoint,
        ];
        $inOrbit = new StatusConfig();
        $inOrbit
            ->setStatusName(DaedalusStatusEnum::IN_ORBIT)
            ->setModifierConfigs($modifierConfigs)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($inOrbit);

        /** @var array<int, VariableEventModifierConfig> $modifierConfigs */
        $modifierConfigs = [
            $astronavigationNeronCpuPriorityModifierPlus1Section,
        ];
        $astronavigationNeronCpuPriority = new StatusConfig();
        $astronavigationNeronCpuPriority
            ->setStatusName(DaedalusStatusEnum::ASTRONAVIGATION_NERON_CPU_PRIORITY)
            ->setModifierConfigs($modifierConfigs)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($astronavigationNeronCpuPriority);

        $mushTrapped = new StatusConfig();
        $mushTrapped
            ->setStatusName(PlaceStatusEnum::MUSH_TRAPPED->value)
            ->setVisibility(VisibilityEnum::MUSH)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($mushTrapped);

        $jukeboxSongStatus = new StatusConfig();
        $jukeboxSongStatus
            ->setStatusName(EquipmentStatusEnum::JUKEBOX_SONG)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($jukeboxSongStatus);

        $hasCeasefiredStatus = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_CEASEFIRED . '_default')
        );
        $manager->persist($hasCeasefiredStatus);

        $hasLearnedSkill = StatusConfig::fromConfigData(StatusConfigData::getByName(PlayerStatusEnum::HAS_LEARNED_SKILL . '_default'));
        $manager->persist($hasLearnedSkill);

        $hasUsedGeniusStatus = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_USED_GENIUS . '_default')
        );
        $manager->persist($hasUsedGeniusStatus);

        $previousRoom = StatusConfig::fromConfigData(
            StatusConfigData::getByName(\sprintf('%s_default', PlayerStatusEnum::PREVIOUS_ROOM))
        );
        $manager->persist($previousRoom);

        $hasExchangedBodyStatus = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_EXCHANGED_BODY . '_default')
        );
        $manager->persist($hasExchangedBodyStatus);

        $hasPrintedZeList = StatusConfig::fromConfigData(
            StatusConfigData::getByName(DaedalusStatusEnum::ZE_LIST_HAS_BEEN_PRINTED . '_default')
        );
        $manager->persist($hasPrintedZeList);

        $hasUsedPutsch = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_USED_PUTSCH . '_default')
        );
        $manager->persist($hasUsedPutsch);

        /** @var VariableEventModifierConfig $pariahModifier */
        $pariahModifier = $this->getReference(ModifierNameEnum::PLAYER_MINUS_20_PERCENTAGE_ON_ACTIONS);
        $pariahStatus = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::PARIAH . '_default')
        );
        $pariahStatus->setModifierConfigs([$pariahModifier]);
        $manager->persist($pariahStatus);

        $hasUsedMassGgedon = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_USED_MASS_GGEDON . '_default')
        );
        $manager->persist($hasUsedMassGgedon);

        $hasReadMageBook = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_READ_MAGE_BOOK . '_default')
        );
        $manager->persist($hasReadMageBook);

        $hasUsedOpportunistAsCommander = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_USED_OPPORTUNIST_AS_COMMANDER . '_default')
        );
        $manager->persist($hasUsedOpportunistAsCommander);

        $hasUsedOpportunistAsNeronManager = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_USED_OPPORTUNIST_AS_NERON_MANAGER . '_default')
        );
        $manager->persist($hasUsedOpportunistAsNeronManager);

        $hasUsedOpportunistAsComManager = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_USED_OPPORTUNIST_AS_COM_MANAGER . '_default')
        );
        $manager->persist($hasUsedOpportunistAsComManager);

        $upgradedFirefighter = StatusConfig::fromConfigData(
            StatusConfigData::getByName(EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE . '_default')
        );
        $manager->persist($upgradedFirefighter);

        /** @var VariableEventModifierConfig $pilotDroneModifier */
        $pilotDroneModifier = $this->getReference(ModifierNameEnum::DRONE_PLUS_20_PERCENTAGE_ON_SHOOT_HUNTER);

        $pilotDroneUpgrade = StatusConfig::fromConfigData(
            StatusConfigData::getByName(EquipmentStatusEnum::PILOT_DRONE_UPGRADE . '_default')
        );
        $pilotDroneUpgrade->setModifierConfigs([$pilotDroneModifier]);
        $manager->persist($pilotDroneUpgrade);

        $sensorDroneUpgrade = StatusConfig::fromConfigData(
            StatusConfigData::getByName(EquipmentStatusEnum::SENSOR_DRONE_UPGRADE . '_default')
        );
        $manager->persist($sensorDroneUpgrade);

        /** @var VariableEventModifierConfig $catOwnerModifierNiceCat */
        $catOwnerModifierNiceCat = $this->getReference('cat_owner_modifier_for_player_set_schrodinger_cant_hurt');

        /** @var VariableEventModifierConfig $catOwnerModifierSadCatDeath */
        $catOwnerModifierSadCatDeath = $this->getReference('cat_owner_modifier_-4morale_on_cat_death');

        $catOwner = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::CAT_OWNER . '_default')
        );
        $catOwner->setModifierConfigs([$catOwnerModifierNiceCat, $catOwnerModifierSadCatDeath]);
        $manager->persist($catOwner);

        $catInfected = StatusConfig::fromConfigData(
            StatusConfigData::getByName(EquipmentStatusEnum::CAT_INFECTED . '_default')
        );
        $manager->persist($catInfected);

        $hasPettedCat = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_PETTED_CAT . '_default')
        );
        $manager->persist($hasPettedCat);

        /** @var VariableEventModifierConfig $fitfulSleepModifier */
        $fitfulSleepModifier = $this->getReference(ModifierNameEnum::FITFUL_SLEEP_MINUS_ONE_ACTION_POINT);
        $fitfulSleep = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::FITFUL_SLEEP . '_default')
        );
        $fitfulSleep->setModifierConfigs([$fitfulSleepModifier]);
        $manager->persist($fitfulSleep);

        $edenComputed = StatusConfig::fromConfigData(
            StatusConfigData::getByName(DaedalusStatusEnum::EDEN_COMPUTED . '_default')
        );
        $manager->persist($edenComputed);

        /** @var TriggerEventModifierConfig $firstContactWithSolModifier */
        $firstContactWithSolModifier = $this->getReference(ModifierNameEnum::PLUS_3_MORALE_POINTS_FOR_ALL_PLAYERS);
        $linkWithSolEstablishedOnce = StatusConfig::fromConfigData(
            StatusConfigData::getByName(DaedalusStatusEnum::LINK_WITH_SOL_ESTABLISHED_ONCE . '_default')
        );
        $linkWithSolEstablishedOnce->setModifierConfigs([$firstContactWithSolModifier]);
        $manager->persist($linkWithSolEstablishedOnce);

        $ghostSample = StatusConfig::fromConfigData(
            StatusConfigData::getByName(DaedalusStatusEnum::GHOST_SAMPLE . '_default')
        );
        $manager->persist($ghostSample);

        $ghostChun = StatusConfig::fromConfigData(
            StatusConfigData::getByName(DaedalusStatusEnum::GHOST_CHUN . '_default')
        );
        $manager->persist($ghostChun);

        $aggroed = StatusConfig::fromConfigData(
            StatusConfigData::getByName(HunterStatusEnum::AGGROED . '_default')
        );
        $manager->persist($aggroed);

        $beginner = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::BEGINNER . '_default')
        );
        $manager->persist($beginner);

        $hasGainedCommanderTitle = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_GAINED_COMMANDER_TITLE . '_default')
        );
        $manager->persist($hasGainedCommanderTitle);

        $hasGainedNeronManagerTitle = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_GAINED_NERON_MANAGER_TITLE . '_default')
        );
        $manager->persist($hasGainedNeronManagerTitle);

        $hasGainedComManagerTitle = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::HAS_GAINED_COM_MANAGER_TITLE . '_default')
        );
        $manager->persist($hasGainedComManagerTitle);

        $pointlessPlayer = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::POINTLESS_PLAYER . '_default')
        );
        $manager->persist($pointlessPlayer);

        $firstStarmapFragment = StatusConfig::fromConfigData(
            StatusConfigData::getByName(DaedalusStatusEnum::FIRST_STARMAP_FRAGMENT . '_default')
        );
        $manager->persist($firstStarmapFragment);

        $first = StatusConfig::fromConfigData(
            StatusConfigData::getByName(PlayerStatusEnum::FIRST . '_default')
        );
        $manager->persist($first);

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
            ->addStatusConfig($multiTeamster)
            ->addStatusConfig($outcast)
            ->addStatusConfig($pacifist)
            ->addStatusConfig($pregnant)
            ->addStatusConfig($starvingWarning)
            ->addStatusConfig($starving)
            ->addStatusConfig($stuckInTheShip)
            ->addStatusConfig($suicidal)
            ->addStatusConfig($watched_public_broadcast)
            ->addStatusConfig($screwedTalkie)
            ->addStatusConfig($inOrbit)
            ->addStatusConfig($astronavigationNeronCpuPriority)
            ->addStatusConfig($mushTrapped)
            ->addStatusConfig($jukeboxSongStatus)
            ->addStatusConfig($hasLearnedSkill)
            ->addStatusConfig($hasUsedGeniusStatus)
            ->addStatusConfig($previousRoom)
            ->addStatusConfig($jukeboxSongStatus)
            ->addStatusConfig($hasCeasefiredStatus)
            ->addStatusConfig($hasExchangedBodyStatus)
            ->addStatusConfig($hasPrintedZeList)
            ->addStatusConfig($hasReadMageBook)
            ->addStatusConfig($hasUsedMassGgedon)
            ->addStatusConfig($hasUsedPutsch)
            ->addStatusConfig($pariahStatus)
            ->addStatusConfig($catInfected)
            ->addStatusConfig($hasPettedCat)
            ->addStatusConfig($upgradedFirefighter)
            ->addStatusConfig($pilotDroneUpgrade)
            ->addStatusConfig($hasUsedOpportunistAsCommander)
            ->addStatusConfig($hasUsedOpportunistAsNeronManager)
            ->addStatusConfig($hasUsedOpportunistAsComManager)
            ->addStatusConfig($catOwner)
            ->addStatusConfig($catInfected)
            ->addStatusConfig($hasPettedCat)
            ->addStatusConfig($fitfulSleep)
            ->addStatusConfig($edenComputed)
            ->addStatusConfig($linkWithSolEstablishedOnce)
            ->addStatusConfig($ghostSample)
            ->addStatusConfig($ghostChun)
            ->addStatusConfig($aggroed)
            ->addStatusConfig($beginner)
            ->addStatusConfig($sensorDroneUpgrade)
            ->addStatusConfig($hasGainedCommanderTitle)
            ->addStatusConfig($hasGainedNeronManagerTitle)
            ->addStatusConfig($hasGainedComManagerTitle)
            ->addStatusConfig($pointlessPlayer)
            ->addStatusConfig($firstStarmapFragment)
            ->addStatusConfig($first);
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
        $this->addReference(self::MULTI_TEAMSTER_STATUS, $multiTeamster);
        $this->addReference(self::OUTCAST_STATUS, $outcast);
        $this->addReference(self::PACIFIST_STATUS, $pacifist);
        $this->addReference(self::PREGNANT_STATUS, $pregnant);
        $this->addReference(self::STARVING_WARNING_STATUS, $starvingWarning);
        $this->addReference(self::STARVING_STATUS, $starving);
        $this->addReference(self::STUCK_IN_THE_SHIP_STATUS, $stuckInTheShip);
        $this->addReference(self::SUICIDAL_STATUS, $suicidal);
        $this->addReference(self::WATCHED_PUBLIC_BROADCAST_STATUS, $watched_public_broadcast);
        $this->addReference(self::TALKIE_SCREWED_STATUS, $screwedTalkie);
        $this->addReference(self::IN_ORBIT_STATUS, $inOrbit);
        $this->addReference(self::ASTRONAVIGATION_NERON_CPU_PRIORITY_STATUS, $astronavigationNeronCpuPriority);
        $this->addReference(PlaceStatusEnum::MUSH_TRAPPED->value, $mushTrapped);
        $this->addReference(EquipmentStatusEnum::JUKEBOX_SONG, $jukeboxSongStatus);
        $this->addReference(PlayerStatusEnum::HAS_LEARNED_SKILL, $hasLearnedSkill);
        $this->addReference(PlayerStatusEnum::HAS_USED_GENIUS, $hasUsedGeniusStatus);
        $this->addReference(PlayerStatusEnum::HAS_CEASEFIRED, $hasCeasefiredStatus);
        $this->addReference(PlayerStatusEnum::PREVIOUS_ROOM, $previousRoom);
        $this->addReference(PlayerStatusEnum::HAS_EXCHANGED_BODY, $hasExchangedBodyStatus);
        $this->addReference(DaedalusStatusEnum::ZE_LIST_HAS_BEEN_PRINTED, $hasPrintedZeList);
        $this->addReference(PlayerStatusEnum::HAS_USED_PUTSCH, $hasUsedPutsch);
        $this->addReference(PlayerStatusEnum::PARIAH, $pariahStatus);
        $this->addReference(PlayerStatusEnum::HAS_USED_MASS_GGEDON, $hasUsedMassGgedon);
        $this->addReference(PlayerStatusEnum::HAS_READ_MAGE_BOOK, $hasReadMageBook);
        $this->addReference(EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE, $upgradedFirefighter);
        $this->addReference(EquipmentStatusEnum::PILOT_DRONE_UPGRADE, $pilotDroneUpgrade);
        $this->addReference(EquipmentStatusEnum::SENSOR_DRONE_UPGRADE, $sensorDroneUpgrade);
        $this->addReference(PlayerStatusEnum::CAT_OWNER, $catOwner);
        $this->addReference(EquipmentStatusEnum::CAT_INFECTED, $catInfected);
        $this->addReference(PlayerStatusEnum::HAS_PETTED_CAT, $hasPettedCat);
        $this->addReference(PlayerStatusEnum::FITFUL_SLEEP, $fitfulSleep);
        $this->addReference(DaedalusStatusEnum::EDEN_COMPUTED, $edenComputed);
        $this->addReference(DaedalusStatusEnum::GHOST_SAMPLE, $ghostSample);
        $this->addReference(DaedalusStatusEnum::GHOST_CHUN, $ghostChun);
        $this->addReference(HunterStatusEnum::AGGROED, $aggroed);
        $this->addReference(PlayerStatusEnum::BEGINNER, $beginner);
        $this->addReference(PlayerStatusEnum::HAS_GAINED_COMMANDER_TITLE, $hasGainedCommanderTitle);
        $this->addReference(PlayerStatusEnum::HAS_GAINED_NERON_MANAGER_TITLE, $hasGainedNeronManagerTitle);
        $this->addReference(PlayerStatusEnum::HAS_GAINED_COM_MANAGER_TITLE, $hasGainedComManagerTitle);
        $this->addReference(PlayerStatusEnum::POINTLESS_PLAYER, $pointlessPlayer);
        $this->addReference(DaedalusStatusEnum::FIRST_STARMAP_FRAGMENT, $firstStarmapFragment);
        $this->addReference(PlayerStatusEnum::FIRST, $first);

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
