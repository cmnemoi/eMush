<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;

class GearConfigFixtures extends Fixture implements DependentFixtureInterface
{
    private ObjectManager $objectManager;

    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;

        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ActionConfig $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);

        /** @var ActionConfig $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        /** @var ActionConfig $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var ArrayCollection $actions */
        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        /** @var ActionConfig $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);

        /** @var ActionConfig $repair1 */
        $repair1 = $this->getReference(TechnicianFixtures::REPAIR_1);

        /** @var ActionConfig $repair6 */
        $repair6 = $this->getReference(TechnicianFixtures::REPAIR_6);

        /** @var ActionConfig $repair12 */
        $repair12 = $this->getReference(TechnicianFixtures::REPAIR_12);

        /** @var ActionConfig $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        /** @var ActionConfig $sabotage1 */
        $sabotage1 = $this->getReference(TechnicianFixtures::SABOTAGE_1);

        /** @var ActionConfig $sabotage6 */
        $sabotage6 = $this->getReference(TechnicianFixtures::SABOTAGE_6);

        /** @var ActionConfig $sabotage12 */
        $sabotage12 = $this->getReference(TechnicianFixtures::SABOTAGE_12);

        /** @var ActionConfig $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        /** @var ActionConfig $dismantle12 */
        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);

        /** @var ActionConfig $dismantle25 */
        $dismantle25 = $this->getReference(TechnicianFixtures::DISMANTLE_3_25);

        /** @var StatusConfig $alienArtifactStatus */
        $alienArtifactStatus = $this->getReference(StatusFixtures::ALIEN_ARTEFACT_STATUS);

        /** @var StatusConfig $heavyStatus */
        $heavyStatus = $this->getReference(StatusFixtures::HEAVY_STATUS);

        $actions25 = clone $actions;
        $actions25->add($repair25);
        $actions25->add($sabotage25);
        $actions25->add($reportAction);

        $apronGear = $this->createGear([ModifierNameEnum::APRON_MODIFIER_FOR_PLAYER_PREVENT_DIRTY], GearItemEnum::STAINPROOF_APRON);
        $apron = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::STAINPROOF_APRON));
        $apron
            ->setActionConfigs($actions25)
            ->setMechanics([$apronGear])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($apron);

        /** @var ArrayCollection $plasteniteActions */
        $plasteniteActions = clone $actions;
        $plasteniteActions->add($dismantle12);
        $plasteniteActions->add($repair12);
        $plasteniteActions->add($sabotage12);
        $plasteniteActions->add($reportAction);

        $plasteniteGear = $this->createGear([GearModifierConfigFixtures::ARMOR_MODIFIER], GearItemEnum::PLASTENITE_ARMOR);

        $plasteniteArmor = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::PLASTENITE_ARMOR));
        $plasteniteArmor
            ->setMechanics([$plasteniteGear])
            ->setActionConfigs($plasteniteActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($plasteniteArmor);

        $wrenchGear = $this->createGear([GearModifierConfigFixtures::WRENCH_MODIFIER], GearItemEnum::ADJUSTABLE_WRENCH);
        $wrench = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::ADJUSTABLE_WRENCH));
        $wrench
            ->setMechanics([$wrenchGear])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($wrench);

        $glovesGear = $this->createGear([GearModifierConfigFixtures::GLOVES_MODIFIER], GearItemEnum::PROTECTIVE_GLOVES);
        $gloves = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::PROTECTIVE_GLOVES));
        $gloves
            ->setMechanics([$glovesGear])
            ->setActionConfigs($actions25)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($gloves);

        $soapGear = $this->createGear(
            [GearModifierConfigFixtures::SOAP_MODIFIER],
            GearItemEnum::SOAP
        );
        $soap = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::SOAP));
        $soap
            ->setMechanics([$soapGear])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($soap);

        /** @var ArrayCollection $sniperHelmetActions */
        $sniperHelmetActions = clone $actions;
        $sniperHelmetActions->add($dismantle12);
        $sniperHelmetActions->add($repair1); // @FIXME with the right %
        $sniperHelmetActions->add($sabotage1);
        $sniperHelmetActions->add($reportAction);

        $sniperHelmetGear = $this->createGear([GearModifierConfigFixtures::AIM_MODIFIER, GearModifierConfigFixtures::AIM_HUNTER_MODIFIER], GearItemEnum::SNIPER_HELMET);
        $sniperHelmet = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::SNIPER_HELMET));
        $sniperHelmet
            ->setMechanics([$sniperHelmetGear])
            ->setActionConfigs($sniperHelmetActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($sniperHelmet);

        $alienBottleOpener = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::ALIEN_BOTTLE_OPENER));
        $alienBottleOpener
            ->setMechanics([$wrenchGear])
            ->setActionConfigs($actions)
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($alienBottleOpener);

        $antiGravScooterActions = clone $actions;
        $antiGravScooterActions->add($dismantle25);
        $antiGravScooterActions->add($repair6);
        $antiGravScooterActions->add($sabotage6);
        $antiGravScooterActions->add($reportAction);

        $antiGravScooterGear = $this->createGear(['modifier_for_player_+2movementPoint_on_event_action_movement_conversion'], GearItemEnum::ANTIGRAV_SCOOTER);

        /** @var ChargeStatusConfig $scooterCharge */
        $scooterCharge = $this->getReference(ChargeStatusFixtures::SCOOTER_CHARGE);

        $antiGravScooter = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::ANTIGRAV_SCOOTER));
        $antiGravScooter
            ->setMechanics([$antiGravScooterGear])
            ->setInitStatuses([$scooterCharge])
            ->setActionConfigs($antiGravScooterActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($antiGravScooter);

        $rollingBoulderGear = $this->createGear([GearModifierConfigFixtures::ROLLING_BOULDER], GearItemEnum::ROLLING_BOULDER);
        $rollingBoulder = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::ROLLING_BOULDER));
        $rollingBoulder
            ->setActionConfigs($actions)
            ->setMechanics([$rollingBoulderGear])
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($rollingBoulder);
        $manager->persist($rollingBoulderGear);

        $actions12 = clone $actions;
        $actions12->add($repair12);
        $actions12->add($sabotage12);
        $actions12->add($reportAction);

        $lensesGear = $this->createGear([GearModifierConfigFixtures::AIM_MODIFIER, GearModifierConfigFixtures::AIM_HUNTER_MODIFIER], GearItemEnum::NCC_LENS);

        $lenses = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::NCC_LENS));
        $lenses
            ->setMechanics([$lensesGear])
            ->setActionConfigs($actions12)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($lenses);

        $oscilloscopeGear = $this->createGear(
            [
                GearModifierConfigFixtures::OSCILLOSCOPE_REPAIR_MODIFIER,
                GearModifierConfigFixtures::OSCILLOSCOPE_SUCCESS_MODIFIER,
                GearModifierConfigFixtures::OSCILLOSCOPE_SUCCESS_MODIFIER_RENOVATE_ACTION,
            ],
            GearItemEnum::OSCILLOSCOPE
        );

        /** @var ActionConfig $dismantle6 */
        $dismantle6 = $this->getReference(TechnicianFixtures::DISMANTLE_4_6);

        /** @var ArrayCollection $oscilloscopeActions */
        $oscilloscopeActions = clone $actions;
        $oscilloscopeActions->add($sabotage6);
        $oscilloscopeActions->add($repair6);
        $oscilloscopeActions->add($dismantle6);

        $oscilloscope = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::OSCILLOSCOPE));
        $oscilloscope
            ->setMechanics([$oscilloscopeGear])
            ->setActionConfigs($oscilloscopeActions)
            ->setInitStatuses([$heavyStatus])
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 2])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($oscilloscope);

        $superSoaperGear = $this->createGear(
            [
                GearModifierConfigFixtures::SOAP_MODIFIER,
                ModifierNameEnum::MINUS_1_SPORE_ON_TAKE_SHOWER,
            ],
            GearItemEnum::SUPER_SOAPER
        );

        $superSoaper = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::SUPER_SOAPER));
        $superSoaper
            ->setActionConfigs($actions)
            ->setMechanics([$superSoaperGear])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($superSoaper);

        $printedCircuitJelly = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::PRINTED_CIRCUIT_JELLY));
        $printedCircuitJelly
            ->setActionConfigs($actions)
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($printedCircuitJelly);

        $shellGear = $this->createGear([GearModifierConfigFixtures::INVERTEBRATE_SHELL_DOUBLES_DAMAGE], GearItemEnum::INVERTEBRATE_SHELL);

        $invertebrateShell = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::INVERTEBRATE_SHELL));
        $invertebrateShell
            ->setActionConfigs($actions)
            ->setInitStatuses([$alienArtifactStatus, $heavyStatus])
            ->setMechanics([$shellGear])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($invertebrateShell);

        /** @var ArrayCollection $actionsLiquidMap */
        $actionsLiquidMap = clone $actions;
        $actionsLiquidMap->add($repair1);
        $actionsLiquidMap->add($sabotage1);
        $actionsLiquidMap->add($reportAction);

        $liquidMapGear = $this->createGear(
            [
                GearModifierConfigFixtures::LIQUID_MAP_MODIFIER,
            ],
            GearItemEnum::MAGELLAN_LIQUID_MAP
        );

        $liquidMap = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::MAGELLAN_LIQUID_MAP));
        $liquidMap
            ->setActionConfigs($actionsLiquidMap)
            ->setMechanics([$liquidMapGear])
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($liquidMap);

        $this->addReference(GearItemEnum::OSCILLOSCOPE, $oscilloscope);
        $this->addReference(GearItemEnum::SNIPER_HELMET, $sniperHelmet);

        $gameConfig
            ->addEquipmentConfig($wrench)
            ->addEquipmentConfig($plasteniteArmor)
            ->addEquipmentConfig($apron)
            ->addEquipmentConfig($gloves)
            ->addEquipmentConfig($soap)
            ->addEquipmentConfig($alienBottleOpener)
            ->addEquipmentConfig($antiGravScooter)
            ->addEquipmentConfig($sniperHelmet)
            ->addEquipmentConfig($lenses)
            ->addEquipmentConfig($rollingBoulder)
            ->addEquipmentConfig($oscilloscope)
            ->addEquipmentConfig($superSoaper)
            ->addEquipmentConfig($printedCircuitJelly)
            ->addEquipmentConfig($invertebrateShell)
            ->addEquipmentConfig($liquidMap);
        $manager->persist($gameConfig);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            GameConfigFixtures::class,
            ChargeStatusFixtures::class,
            StatusFixtures::class,
            GearModifierConfigFixtures::class,
        ];
    }

    private function createGear(array $modifierConfigNames, string $name): Gear
    {
        $gear = new Gear();

        $modifierConfigs = [];
        foreach ($modifierConfigNames as $modifierConfigName) {
            $currentModifierConfig = $this->getReference($modifierConfigName);
            if ($currentModifierConfig instanceof AbstractModifierConfig) {
                $modifierConfigs[] = $currentModifierConfig;
            }
        }

        $gear
            ->setModifierConfigs($modifierConfigs)
            ->buildName(EquipmentMechanicEnum::GEAR . '_' . $name, GameConfigEnum::DEFAULT);

        $this->objectManager->persist($gear);

        return $gear;
    }
}
