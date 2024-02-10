<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;

class ToolConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
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

        /** @var Action $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);
        /** @var Action $repair3 */
        $repair3 = $this->getReference(TechnicianFixtures::REPAIR_3);
        /** @var Action $repair6 */
        $repair6 = $this->getReference(TechnicianFixtures::REPAIR_6);
        /** @var Action $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);
        /** @var Action $repair50 */
        $repair50 = $this->getReference(TechnicianFixtures::REPAIR_50);

        /** @var Action $sabotage3 */
        $sabotage3 = $this->getReference(TechnicianFixtures::SABOTAGE_3);
        /** @var Action $sabotage6 */
        $sabotage6 = $this->getReference(TechnicianFixtures::SABOTAGE_6);
        /** @var Action $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);
        /** @var Action $sabotage50 */
        $sabotage50 = $this->getReference(TechnicianFixtures::SABOTAGE_50);

        /** @var StatusConfig $alienArtifactStatus */
        $alienArtifactStatus = $this->getReference(StatusFixtures::ALIEN_ARTEFACT_STATUS);
        /** @var StatusConfig $heavyStatus */
        $heavyStatus = $this->getReference(StatusFixtures::HEAVY_STATUS);

        /** @var Action $dismantle12 */
        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);
        /** @var Action $dismantle325 */
        $dismantle325 = $this->getReference(TechnicianFixtures::DISMANTLE_3_25);
        /** @var Action $dismantle425 */
        $dismantle425 = $this->getReference(TechnicianFixtures::DISMANTLE_4_25);

        // @TODO
        $hackerKitMechanic = new Tool();
        $hackerKitMechanic->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::HACKER_KIT, GameConfigEnum::DEFAULT);

        $hackerKit = new ItemConfig();
        $hackerKit
            ->setEquipmentName(ToolItemEnum::HACKER_KIT)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics([$hackerKitMechanic])
            ->setActions([
                $takeAction,
                $dropAction,
                $hideAction,
                $repair6,
                $sabotage6,
                $reportAction,
                $examineAction,
            ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($hackerKit);
        $manager->persist($hackerKitMechanic);

        /** @var Action $writeAction */
        $writeAction = $this->getReference(ActionsFixtures::WRITE);
        /** @var Action $exitTerminalAction */
        $exitTerminalAction = $this->getReference(ActionsFixtures::EXIT_TERMINAL);

        $blockOfPostIt = new ItemConfig();
        $blockOfPostIt
            ->setEquipmentName(ToolItemEnum::BLOCK_OF_POST_IT)
            ->setIsStackable(false)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions([$takeAction, $dropAction, $hideAction, $examineAction, $writeAction, $exitTerminalAction])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blockOfPostIt);

        /** @var Action $installCamera */
        $installCamera = $this->getReference(ActionsFixtures::INSTALL_CAMERA);
        $cameraActions = [
            $takeAction,
            $dismantle325,
            $repair25,
            $reportAction,
            $examineAction,
            $installCamera,
        ];

        $camera = new ItemConfig();
        $camera
            ->setEquipmentName(ItemEnum::CAMERA_ITEM)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions($cameraActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($camera);

        /** @var Action $extinguishAction */
        $extinguishAction = $this->getReference(ActionsFixtures::EXTINGUISH_DEFAULT);

        $extinguisher = new ItemConfig();
        $extinguisher
            ->setEquipmentName(ToolItemEnum::EXTINGUISHER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([
                $takeAction, $dropAction, $hideAction, $examineAction,
                $dismantle325, $repair25, $sabotage25, $reportAction,
                $extinguishAction,
                ])
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($extinguisher);

        /** @var Action $gagAction */
        $gagAction = $this->getReference(ActionsFixtures::GAG_DEFAULT);
        $ductTapeMechanic = new Tool();
        $ductTapeMechanic
            ->addAction($gagAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::DUCT_TAPE, GameConfigEnum::DEFAULT)
        ;

        $ductTape = new ItemConfig();
        $ductTape
            ->setEquipmentName(ToolItemEnum::DUCT_TAPE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$ductTapeMechanic])
            ->setActions([$takeAction, $dropAction, $hideAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($ductTape);
        $manager->persist($ductTapeMechanic);

        /** @var Action $tryKubeAction */
        $tryKubeAction = $this->getReference(ActionsFixtures::TRY_KUBE);

        $madKube = new ItemConfig();
        $madKube
            ->setEquipmentName(ToolItemEnum::MAD_KUBE)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions([$takeAction, $dropAction, $hideAction, $examineAction, $tryKubeAction])
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($madKube);

        $microwaveActions = [
            $takeAction, $dropAction, $hideAction, $examineAction,
            $dismantle425, $repair50, $sabotage50, $reportAction,
        ];

        /** @var Action $expressCookAction */
        $expressCookAction = $this->getReference(ActionsFixtures::COOK_EXPRESS);

        $microwaveMechanic = new Tool();
        $microwaveMechanic
            ->addAction($expressCookAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::MICROWAVE, GameConfigEnum::DEFAULT)
        ;

        /** @var ChargeStatusConfig $microwaveCharge */
        $microwaveCharge = $this->getReference(ChargeStatusFixtures::MICROWAVE_CHARGE);

        $microwave = new ItemConfig();
        $microwave
            ->setEquipmentName(ToolItemEnum::MICROWAVE)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics([$microwaveMechanic])
            ->setActions($microwaveActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 2])
            ->setInitStatuses([$heavyStatus, $microwaveCharge])
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($microwave);
        $manager->persist($microwaveMechanic);

        /** @var Action $hyperfreezeAction */
        $hyperfreezeAction = $this->getReference(ActionsFixtures::HYPERFREEZE_DEFAULT);

        $superFreezerMechanic = new Tool();
        $superFreezerMechanic
            ->addAction($hyperfreezeAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::SUPERFREEZER, GameConfigEnum::DEFAULT)
        ;

        $superfreezerActions = [
            $takeAction, $dropAction, $hideAction, $examineAction,
            $dismantle425, $repair25, $sabotage25, $reportAction,
        ];

        $superFreezer = new ItemConfig();
        $superFreezer
            ->setEquipmentName(ToolItemEnum::SUPERFREEZER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics([$superFreezerMechanic])
            ->setActions($superfreezerActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 2])
            ->setInitStatuses([$heavyStatus])
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($superFreezer);
        $manager->persist($superFreezerMechanic);

        /** @var Action $alienHolographicTVAction */
        $alienHolographicTVAction = $this->getReference(ActionsFixtures::PUBLIC_BROADCAST);

        $alienHolographicTV = new ItemConfig();
        $alienHolographicTV
            ->setEquipmentName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions([
                $takeAction, $dropAction, $hideAction,
                $repair3, $sabotage3, $reportAction, $examineAction,
                $alienHolographicTVAction,
                ])
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($alienHolographicTV);

        $medikit = new ItemConfig();
        $medikit
            ->setEquipmentName(ToolItemEnum::MEDIKIT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(false)
            ->setActions([$takeAction, $dropAction, $hideAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($medikit);

        /** @var Action $removeSporeAction */
        $removeSporeAction = $this->getReference(ActionsFixtures::REMOVE_SPORE);

        $sporeSucker = new ItemConfig();
        $sporeSucker
            ->setEquipmentName(ToolItemEnum::SPORE_SUCKER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions([
                $takeAction, $dropAction, $hideAction, $examineAction,
                $repair25, $sabotage25,
                $removeSporeAction,
                ])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($sporeSucker);

        /** @var Action $ultraHealAction */
        $ultraHealAction = $this->getReference(ActionsFixtures::HEAL_ULTRA);

        $alienModifierGear = new Gear();
        /** @var AbstractModifierConfig $alienOilModifier */
        $alienOilModifier = $this->getReference(GearModifierConfigFixtures::ALIEN_OIL_INCREASE_FUEL_INJECTED);
        $alienModifierGear
            ->setModifierConfigs([$alienOilModifier])
            ->setName('alien_oil_gear_default')
        ;
        $manager->persist($alienModifierGear);

        /** @var Action $insertFuelChamber */
        $insertFuelChamber = $this->getReference(ActionsFixtures::INSERT_FUEL_CHAMBER);
        $jarOfAlienOil = new ItemConfig();
        $jarOfAlienOil
            ->setEquipmentName(ToolItemEnum::JAR_OF_ALIEN_OIL)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions([$takeAction, $dropAction, $hideAction, $examineAction, $ultraHealAction, $insertFuelChamber])
            ->setInitStatuses([$alienArtifactStatus])
            ->setMechanics([$alienModifierGear])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($jarOfAlienOil);

        /** @var Action $bandageAction */
        $bandageAction = $this->getReference(ActionsFixtures::BANDAGE_DEFAULT);

        $bandage = new ItemConfig();
        $bandage
            ->setEquipmentName(ToolItemEnum::BANDAGE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions([$takeAction, $dropAction, $hideAction, $examineAction, $bandageAction])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($bandage);

        /** @var Action $serumAction */
        $serumAction = $this->getReference(ActionsFixtures::INJECT_SERUM);

        $retroFungalSerumMechanic = new Tool();
        $retroFungalSerumMechanic
            ->addAction($serumAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::RETRO_FUNGAL_SERUM, GameConfigEnum::DEFAULT)
        ;

        $retroFungalSerum = new ItemConfig();
        $retroFungalSerum
            ->setEquipmentName(ToolItemEnum::RETRO_FUNGAL_SERUM)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics([$retroFungalSerumMechanic])
            ->setActions([$takeAction, $dropAction, $hideAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($retroFungalSerum);
        $manager->persist($retroFungalSerumMechanic);

        /** @var Action $spaceCapsuleAction */
        $spaceCapsuleAction = $this->getReference(ActionsFixtures::OPEN_SPACE_CAPSULE);

        $spaceCapsule = new ItemConfig();
        $spaceCapsule
            ->setEquipmentName(ToolItemEnum::SPACE_CAPSULE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions([$takeAction, $dropAction, $hideAction, $examineAction, $spaceCapsuleAction])
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($spaceCapsule);

        /** @var Action $autoEjectAction */
        $autoEjectAction = $this->getReference(ActionsFixtures::AUTO_EJECT);

        $spaceSuitMechanic = new Tool();
        $spaceSuitMechanic
            ->addAction($autoEjectAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . GearItemEnum::SPACESUIT, GameConfigEnum::DEFAULT)
        ;

        $spaceSuit = new ItemConfig();
        $spaceSuit
            ->setEquipmentName(GearItemEnum::SPACESUIT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions([$takeAction, $dropAction, $hideAction, $examineAction, $dismantle12, $repair6, $sabotage6, $reportAction])
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->setMechanics([$spaceSuitMechanic])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($spaceSuitMechanic);
        $manager->persist($spaceSuit);

        $this->addReference(ToolItemEnum::EXTINGUISHER, $extinguisher);

        $gameConfig
            ->addEquipmentConfig($hackerKit)
            ->addEquipmentConfig($blockOfPostIt)
            ->addEquipmentConfig($camera)
            ->addEquipmentConfig($extinguisher)
            ->addEquipmentConfig($ductTape)
            ->addEquipmentConfig($madKube)
            ->addEquipmentConfig($microwave)
            ->addEquipmentConfig($superFreezer)
            ->addEquipmentConfig($alienHolographicTV)
            ->addEquipmentConfig($medikit)
            ->addEquipmentConfig($sporeSucker)
            ->addEquipmentConfig($jarOfAlienOil)
            ->addEquipmentConfig($bandage)
            ->addEquipmentConfig($retroFungalSerum)
            ->addEquipmentConfig($spaceCapsule)
            ->addEquipmentConfig($spaceSuit)
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
        ];
    }
}
