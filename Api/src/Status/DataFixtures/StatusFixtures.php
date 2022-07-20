<?php

namespace Mush\Status\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\DataFixtures\StatusModifierConfigFixtures;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Status\Entity\Config\StatusConfig;
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

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $alienArtefact = new StatusConfig();
        $alienArtefact
            ->setName(EquipmentStatusEnum::ALIEN_ARTEFACT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($alienArtefact);

        $heavy = new StatusConfig();
        $heavy
            ->setName(EquipmentStatusEnum::HEAVY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($heavy);

        $moduleAccess = new StatusConfig();
        $moduleAccess
            ->setName(EquipmentStatusEnum::MODULE_ACCESS)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($moduleAccess);

        $hidden = new StatusConfig();
        $hidden
            ->setName(EquipmentStatusEnum::HIDDEN)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($hidden);

        $broken = new StatusConfig();
        $broken
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($broken);

        $unstable = new StatusConfig();
        $unstable
            ->setName(EquipmentStatusEnum::UNSTABLE)
            ->setVisibility(VisibilityEnum::COOK_RESTRICTED)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($unstable);

        $hazardous = new StatusConfig();
        $hazardous
            ->setName(EquipmentStatusEnum::HAZARDOUS)
            ->setVisibility(VisibilityEnum::COOK_RESTRICTED)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($hazardous);

        $decomposing = new StatusConfig();
        $decomposing
            ->setName(EquipmentStatusEnum::DECOMPOSING)
            ->setVisibility(VisibilityEnum::COOK_RESTRICTED)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($decomposing);

        /** @var ModifierConfig $frozenModifier */
        $frozenModifier = $this->getReference(StatusModifierConfigFixtures::FROZEN_MODIFIER);

        $frozen = new StatusConfig();
        $frozen
            ->setName(EquipmentStatusEnum::FROZEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs(new ArrayCollection([$frozenModifier]))
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($frozen);

        $plantThirsty = new StatusConfig();
        $plantThirsty
            ->setName(EquipmentStatusEnum::PLANT_THIRSTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($plantThirsty);

        $plantDry = new StatusConfig();
        $plantDry
            ->setName(EquipmentStatusEnum::PLANT_DRY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($plantDry);

        $plantDiseased = new StatusConfig();
        $plantDiseased
            ->setName(EquipmentStatusEnum::PLANT_DISEASED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($plantDiseased);

        $documentContent = new StatusConfig();
        $documentContent
            ->setName(EquipmentStatusEnum::DOCUMENT_CONTENT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($documentContent);

        $reinforced = new StatusConfig();
        $reinforced
            ->setName(EquipmentStatusEnum::REINFORCED)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($reinforced);

        /** @var ModifierConfig $frozenModifier */
        $antisocialModifier = $this->getReference(StatusModifierConfigFixtures::ANTISOCIAL_MODIFIER);

        $antisocial = new StatusConfig();
        $antisocial
            ->setName(PlayerStatusEnum::ANTISOCIAL)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs(new ArrayCollection([$antisocialModifier]))
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($antisocial);

        $berzerk = new StatusConfig();
        $berzerk
            ->setName(PlayerStatusEnum::BERZERK)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($berzerk);

        $brainsync = new StatusConfig();
        $brainsync
            ->setName(PlayerStatusEnum::BRAINSYNC)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($brainsync);

        /** @var ModifierConfig $burdenedModifier */
        $burdenedModifier = $this->getReference(StatusModifierConfigFixtures::BURDENED_MODIFIER);
        $burdened = new StatusConfig();
        $burdened
            ->setName(PlayerStatusEnum::BURDENED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs(new ArrayCollection([$burdenedModifier]))
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($burdened);

        $demoralized = new StatusConfig();
        $demoralized
            ->setName(PlayerStatusEnum::DEMORALIZED)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($demoralized);

        $dirty = new StatusConfig();
        $dirty
            ->setName(PlayerStatusEnum::DIRTY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($dirty);

        /** @var ModifierConfig $disabledConversionModifier */
        $disabledConversionModifier = $this->getReference(StatusModifierConfigFixtures::DISABLED_CONVERSION_MODIFIER);
        /** @var ModifierConfig $disabledNotAloneModifier */
        $disabledNotAloneModifier = $this->getReference(StatusModifierConfigFixtures::DISABLED_NOT_ALONE_MODIFIER);
        $disabled = new StatusConfig();
        $disabled
            ->setName(PlayerStatusEnum::DISABLED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs(new ArrayCollection([$disabledNotAloneModifier, $disabledConversionModifier]))
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($disabled);

        $focused = new StatusConfig();
        $focused
            ->setName(PlayerStatusEnum::FOCUSED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($focused);

        $fullStomach = new StatusConfig();
        $fullStomach
            ->setName(PlayerStatusEnum::FULL_STOMACH)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($fullStomach);

        $gagged = new StatusConfig();
        $gagged
            ->setName(PlayerStatusEnum::GAGGED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($gagged);

        $germaphobe = new StatusConfig();
        $germaphobe
            ->setName(PlayerStatusEnum::GERMAPHOBE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($germaphobe);

        $guardian = new StatusConfig();
        $guardian
            ->setName(PlayerStatusEnum::GUARDIAN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($guardian);

        $highlyInactive = new StatusConfig();
        $highlyInactive
            ->setName(PlayerStatusEnum::HIGHLY_INACTIVE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($highlyInactive);

        $hyperactive = new StatusConfig();
        $hyperactive
            ->setName(PlayerStatusEnum::HYPERACTIVE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($hyperactive);

        $immunized = new StatusConfig();
        $immunized
            ->setName(PlayerStatusEnum::IMMUNIZED)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($immunized);

        $inactive = new StatusConfig();
        $inactive
            ->setName(PlayerStatusEnum::INACTIVE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($inactive);

        /** @var ModifierConfig $lostModifier */
        $lostModifier = $this->getReference(StatusModifierConfigFixtures::LOST_MODIFIER);

        $lost = new StatusConfig();
        $lost
            ->setName(PlayerStatusEnum::LOST)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs(new ArrayCollection([$lostModifier]))
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($lost);

        /** @var ModifierConfig $disabledConversionModifier */
        $lyingDownModifier = $this->getReference(StatusModifierConfigFixtures::LYING_DOWN_MODIFIER);
        $lyingDown = new StatusConfig();
        $lyingDown
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs(new ArrayCollection([$lyingDownModifier]))
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($lyingDown);

        $multiTeamster = new StatusConfig();
        $multiTeamster
            ->setName(PlayerStatusEnum::MULTI_TEAMSTER)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($multiTeamster);

        $outcast = new StatusConfig();
        $outcast
            ->setName(PlayerStatusEnum::OUTCAST)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($outcast);

        /** @var ModifierConfig $pacifistModifier */
        $pacifistModifier = $this->getReference(StatusModifierConfigFixtures::PACIFIST_MODIFIER);
        $pacifist = new StatusConfig();
        $pacifist
            ->setName(PlayerStatusEnum::PACIFIST)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setModifierConfigs(new ArrayCollection([$pacifistModifier]))
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($pacifist);

        $pregnant = new StatusConfig();
        $pregnant
            ->setName(PlayerStatusEnum::PREGNANT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($pregnant);

        /** @var ModifierConfig $pacifistModifier */
        $starvingModifier = $this->getReference(StatusModifierConfigFixtures::STARVING_MODIFIER);
        $starving = new StatusConfig();
        $starving
            ->setName(PlayerStatusEnum::STARVING)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setModifierConfigs(new ArrayCollection([$starvingModifier]))
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($starving);

        $stuckInTheShip = new StatusConfig();
        $stuckInTheShip
            ->setName(PlayerStatusEnum::STUCK_IN_THE_SHIP)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($stuckInTheShip);

        $suicidal = new StatusConfig();
        $suicidal
            ->setName(PlayerStatusEnum::SUICIDAL)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($suicidal);

        $watched_public_broadcast = new StatusConfig();
        $watched_public_broadcast
            ->setName(PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST)
            ->setVisibility(VisibilityEnum::PRIVATE)
            ->setGameConfig($gameConfig)
        ;
        $manager->persist($watched_public_broadcast);

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

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            StatusModifierConfigFixtures::class,
        ];
    }
}
