<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
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

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var ArrayCollection $actions */
        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        /** @var Action $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);
        /** @var Action $repair1 */
        $repair1 = $this->getReference(TechnicianFixtures::REPAIR_1);
        /** @var Action $repair6 */
        $repair6 = $this->getReference(TechnicianFixtures::REPAIR_6);
        /** @var Action $repair12 */
        $repair12 = $this->getReference(TechnicianFixtures::REPAIR_12);
        /** @var Action $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        /** @var Action $sabotage1 */
        $sabotage1 = $this->getReference(TechnicianFixtures::SABOTAGE_1);
        /** @var Action $sabotage6 */
        $sabotage6 = $this->getReference(TechnicianFixtures::SABOTAGE_6);
        /** @var Action $sabotage12 */
        $sabotage12 = $this->getReference(TechnicianFixtures::SABOTAGE_12);
        /** @var Action $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        /** @var Action $dismantle12 */
        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);
        /** @var Action $dismantle25 */
        $dismantle25 = $this->getReference(TechnicianFixtures::DISMANTLE_3_25);
        /** @var StatusConfig $alienArtifactStatus */
        $alienArtifactStatus = $this->getReference(StatusFixtures::ALIEN_ARTEFACT_STATUS);
        /** @var StatusConfig $heavyStatus */
        $heavyStatus = $this->getReference(StatusFixtures::HEAVY_STATUS);

        $actions25 = clone $actions;
        $actions25->add($repair25);
        $actions25->add($sabotage25);
        $actions25->add($reportAction);

        $apronGear = $this->createGear([GearModifierConfigFixtures::APRON_MODIFIER], GearItemEnum::STAINPROOF_APRON);
        $apron = new ItemConfig();
        $apron
            ->setEquipmentName(GearItemEnum::STAINPROOF_APRON)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setActions($actions25)
            ->setMechanics([$apronGear])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($apron);

        /** @var ArrayCollection $plasteniteActions */
        $plasteniteActions = clone $actions;
        $plasteniteActions->add($dismantle12);
        $plasteniteActions->add($repair12);
        $plasteniteActions->add($sabotage12);
        $plasteniteActions->add($reportAction);

        $plasteniteGear = $this->createGear([GearModifierConfigFixtures::ARMOR_MODIFIER], GearItemEnum::PLASTENITE_ARMOR);

        $plasteniteArmor = new ItemConfig();
        $plasteniteArmor
            ->setEquipmentName(GearItemEnum::PLASTENITE_ARMOR)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics([$plasteniteGear])
            ->setActions($plasteniteActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($plasteniteArmor);

        $wrenchGear = $this->createGear([GearModifierConfigFixtures::WRENCH_MODIFIER], GearItemEnum::ADJUSTABLE_WRENCH);
        $wrench = new ItemConfig();
        $wrench
            ->setEquipmentName(GearItemEnum::ADJUSTABLE_WRENCH)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics([$wrenchGear])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($wrench);

        $glovesGear = $this->createGear([GearModifierConfigFixtures::GLOVES_MODIFIER], GearItemEnum::PROTECTIVE_GLOVES);
        $gloves = new ItemConfig();
        $gloves
            ->setEquipmentName(GearItemEnum::PROTECTIVE_GLOVES)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics([$glovesGear])
            ->setActions($actions25)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($gloves);

        $soapGear = $this->createGear(
            [GearModifierConfigFixtures::SOAP_MODIFIER],
            GearItemEnum::SOAP
        );
        $soap = new ItemConfig();
        $soap
            ->setEquipmentName(GearItemEnum::SOAP)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$soapGear])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($soap);

        /** @var ArrayCollection $sniperHelmetActions */
        $sniperHelmetActions = clone $actions;
        $sniperHelmetActions->add($dismantle12);
        $sniperHelmetActions->add($repair1); // @FIXME with the right %
        $sniperHelmetActions->add($sabotage1);
        $sniperHelmetActions->add($reportAction);

        $sniperHelmetGear = $this->createGear([GearModifierConfigFixtures::AIM_MODIFIER, GearModifierConfigFixtures::AIM_HUNTER_MODIFIER], GearItemEnum::SNIPER_HELMET);
        $sniperHelmet = new ItemConfig();
        $sniperHelmet
            ->setEquipmentName(GearItemEnum::SNIPER_HELMET)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics([$sniperHelmetGear])
            ->setActions($sniperHelmetActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($sniperHelmet);

        $alienBottleOpener = new ItemConfig();
        $alienBottleOpener
            ->setEquipmentName(GearItemEnum::ALIEN_BOTTLE_OPENER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics([$wrenchGear])
            ->setActions($actions)
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($alienBottleOpener);

        $antiGravScooterActions = clone $actions;
        $antiGravScooterActions->add($dismantle25);
        $antiGravScooterActions->add($repair6);
        $antiGravScooterActions->add($sabotage6);
        $antiGravScooterActions->add($reportAction);

        $antiGravScooterGear = $this->createGear([GearModifierConfigFixtures::SCOOTER_MODIFIER], GearItemEnum::ANTIGRAV_SCOOTER);

        /** @var ChargeStatusConfig $scooterCharge */
        $scooterCharge = $this->getReference(ChargeStatusFixtures::SCOOTER_CHARGE);

        $antiGravScooter = new ItemConfig();
        $antiGravScooter
            ->setEquipmentName(GearItemEnum::ANTIGRAV_SCOOTER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics([$antiGravScooterGear])
            ->setInitStatuses([$scooterCharge])
            ->setActions($antiGravScooterActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($antiGravScooter);

        $rollingBoulderGear = $this->createGear([GearModifierConfigFixtures::ROLLING_BOULDER], GearItemEnum::ROLLING_BOULDER);
        $rollingBoulder = new ItemConfig();
        $rollingBoulder
            ->setEquipmentName(GearItemEnum::ROLLING_BOULDER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
            ->setMechanics([$rollingBoulderGear])
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($rollingBoulder);
        $manager->persist($rollingBoulderGear);

        $actions12 = clone $actions;
        $actions12->add($repair12);
        $actions12->add($sabotage12);
        $actions12->add($reportAction);

        $lensesGear = $this->createGear([GearModifierConfigFixtures::AIM_MODIFIER, GearModifierConfigFixtures::AIM_HUNTER_MODIFIER], GearItemEnum::NCC_LENS);

        $lenses = new ItemConfig();
        $lenses
            ->setEquipmentName(GearItemEnum::NCC_LENS)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics([$lensesGear])
            ->setActions($actions12)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($lenses);

        $oscilloscopeGear = $this->createGear(
            [
                GearModifierConfigFixtures::OSCILLOSCOPE_REPAIR_MODIFIER,
                GearModifierConfigFixtures::OSCILLOSCOPE_SUCCESS_MODIFIER,
                GearModifierConfigFixtures::OSCILLOSCOPE_SUCCESS_MODIFIER_RENOVATE_ACTION,
            ],
            GearItemEnum::OSCILLOSCOPE
        );

        /** @var Action $dismantle6 */
        $dismantle6 = $this->getReference(TechnicianFixtures::DISMANTLE_4_6);
        /** @var ArrayCollection $oscilloscopeActions */
        $oscilloscopeActions = clone $actions;
        $oscilloscopeActions->add($sabotage6);
        $oscilloscopeActions->add($repair6);
        $oscilloscopeActions->add($dismantle6);

        $oscilloscope = new ItemConfig();
        $oscilloscope
            ->setEquipmentName(GearItemEnum::OSCILLOSCOPE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setMechanics([$oscilloscopeGear])
            ->setActions($oscilloscopeActions)
            ->setInitStatuses([$heavyStatus])
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 2])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($oscilloscope);

        $superSoaper = new ItemConfig();
        $superSoaper
            ->setEquipmentName(GearItemEnum::SUPER_SOAPER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($superSoaper);

        $printedCircuitJelly = new ItemConfig();
        $printedCircuitJelly
            ->setEquipmentName(GearItemEnum::PRINTED_CIRCUIT_JELLY)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions($actions)
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($printedCircuitJelly);

        $invertebrateShell = new ItemConfig();
        $invertebrateShell
            ->setEquipmentName(GearItemEnum::INVERTEBRATE_SHELL)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($actions)
            ->setInitStatuses([$alienArtifactStatus, $heavyStatus])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($invertebrateShell);

        /** @var ArrayCollection $actionsLiquidMap */
        $actionsLiquidMap = clone $actions;
        $actionsLiquidMap->add($repair1);
        $actionsLiquidMap->add($sabotage1);
        $actionsLiquidMap->add($reportAction);

        $liquidMap = new ItemConfig();
        $liquidMap
            ->setEquipmentName(GearItemEnum::MAGELLAN_LIQUID_MAP)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions($actionsLiquidMap)
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
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
            ->addEquipmentConfig($liquidMap)
        ;
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
            ->buildName(EquipmentMechanicEnum::GEAR . '_' . $name, GameConfigEnum::DEFAULT)
        ;

        $this->objectManager->persist($gear);

        return $gear;
    }
}
