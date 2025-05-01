<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\ConfigData\EquipmentConfigData;
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

        /** @var ActionConfig $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);

        /** @var ActionConfig $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        /** @var ActionConfig $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var ActionConfig $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);

        /** @var ActionConfig $repair3 */
        $repair3 = $this->getReference(TechnicianFixtures::REPAIR_3);

        /** @var ActionConfig $repair6 */
        $repair6 = $this->getReference(TechnicianFixtures::REPAIR_6);

        /** @var ActionConfig $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        /** @var ActionConfig $repair50 */
        $repair50 = $this->getReference(TechnicianFixtures::REPAIR_50);

        /** @var ActionConfig $sabotage3 */
        $sabotage3 = $this->getReference(TechnicianFixtures::SABOTAGE_3);

        /** @var ActionConfig $sabotage6 */
        $sabotage6 = $this->getReference(TechnicianFixtures::SABOTAGE_6);

        /** @var ActionConfig $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        /** @var ActionConfig $sabotage50 */
        $sabotage50 = $this->getReference(TechnicianFixtures::SABOTAGE_50);

        /** @var StatusConfig $alienArtifactStatus */
        $alienArtifactStatus = $this->getReference(StatusFixtures::ALIEN_ARTEFACT_STATUS);

        /** @var StatusConfig $heavyStatus */
        $heavyStatus = $this->getReference(StatusFixtures::HEAVY_STATUS);

        /** @var ActionConfig $dismantle12 */
        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);

        /** @var ActionConfig $dismantle325 */
        $dismantle325 = $this->getReference(TechnicianFixtures::DISMANTLE_3_25);

        /** @var ActionConfig $dismantle425 */
        $dismantle425 = $this->getReference(TechnicianFixtures::DISMANTLE_4_25);

        /** @TODO */
        $hackerKitMechanic = new Tool();
        $hackerKitMechanic->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::HACKER_KIT, GameConfigEnum::DEFAULT);

        $hackerKit = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::HACKER_KIT));
        $hackerKit
            ->setMechanics([$hackerKitMechanic])
            ->setActionConfigs([
                $takeAction,
                $dropAction,
                $hideAction,
                $repair6,
                $sabotage6,
                $reportAction,
                $examineAction,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($hackerKit);
        $manager->persist($hackerKitMechanic);

        /** @var ActionConfig $writeAction */
        $writeAction = $this->getReference(ActionsFixtures::WRITE);

        /** @var ActionConfig $exitTerminalAction */
        $exitTerminalAction = $this->getReference(ActionsFixtures::EXIT_TERMINAL);

        $blockOfPostIt = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::BLOCK_OF_POST_IT));
        $blockOfPostIt
            ->setActionConfigs([$takeAction, $dropAction, $hideAction, $examineAction, $writeAction, $exitTerminalAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blockOfPostIt);

        /** @var ActionConfig $installCamera */
        $installCamera = $this->getReference(ActionsFixtures::INSTALL_CAMERA);
        $cameraActions = [
            $takeAction,
            $dismantle325,
            $repair25,
            $reportAction,
            $examineAction,
            $installCamera,
        ];

        $camera = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::CAMERA_ITEM));
        $camera
            ->setActionConfigs($cameraActions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($camera);

        /** @var ActionConfig $extinguishAction */
        $extinguishAction = $this->getReference(ActionEnum::EXTINGUISH->value);

        $extinguisherTool = new Tool();
        $extinguisherTool
            ->addAction($extinguishAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::EXTINGUISHER, GameConfigEnum::DEFAULT);
        $manager->persist($extinguisherTool);

        $extinguisher = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::EXTINGUISHER));
        $extinguisher
            ->setActionConfigs([
                $takeAction, $dropAction, $hideAction, $examineAction,
                $dismantle325, $repair25, $sabotage25, $reportAction,
            ])
            ->setMechanics([$extinguisherTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($extinguisher);

        /** @var ActionConfig $gagAction */
        $gagAction = $this->getReference(ActionsFixtures::GAG_DEFAULT);
        $ductTapeMechanic = new Tool();
        $ductTapeMechanic
            ->addAction($gagAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::DUCT_TAPE, GameConfigEnum::DEFAULT);

        $ductTape = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::DUCT_TAPE));
        $ductTape
            ->setMechanics([$ductTapeMechanic])
            ->setActionConfigs([$takeAction, $dropAction, $hideAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($ductTape);
        $manager->persist($ductTapeMechanic);

        /** @var ActionConfig $tryKubeAction */
        $tryKubeAction = $this->getReference(ActionsFixtures::TRY_KUBE);

        $madKube = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::MAD_KUBE));
        $madKube
            ->setActionConfigs([$takeAction, $dropAction, $hideAction, $examineAction, $tryKubeAction])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($madKube);

        $microwaveActions = [
            $takeAction, $dropAction, $hideAction, $examineAction,
            $dismantle425, $repair50, $sabotage50, $reportAction,
        ];

        /** @var ActionConfig $expressCookAction */
        $expressCookAction = $this->getReference(ActionsFixtures::COOK_EXPRESS);

        $microwaveMechanic = new Tool();
        $microwaveMechanic
            ->addAction($expressCookAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::MICROWAVE, GameConfigEnum::DEFAULT);

        /** @var ChargeStatusConfig $microwaveCharge */
        $microwaveCharge = $this->getReference(ChargeStatusFixtures::MICROWAVE_CHARGE);

        $microwave = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::MICROWAVE));
        $microwave
            ->setMechanics([$microwaveMechanic])
            ->setActionConfigs($microwaveActions)
            ->setInitStatuses([$heavyStatus, $microwaveCharge])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($microwave);
        $manager->persist($microwaveMechanic);

        /** @var ActionConfig $hyperfreezeAction */
        $hyperfreezeAction = $this->getReference(ActionsFixtures::HYPERFREEZE_DEFAULT);

        $superFreezerMechanic = new Tool();
        $superFreezerMechanic
            ->addAction($hyperfreezeAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::SUPERFREEZER, GameConfigEnum::DEFAULT);

        $superfreezerActions = [
            $takeAction, $dropAction, $hideAction, $examineAction,
            $dismantle425, $repair25, $sabotage25, $reportAction,
        ];

        $superFreezer = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::SUPERFREEZER));
        $superFreezer
            ->setMechanics([$superFreezerMechanic])
            ->setActionConfigs($superfreezerActions)
            ->setInitStatuses([$heavyStatus])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($superFreezer);
        $manager->persist($superFreezerMechanic);

        /** @var ActionConfig $alienHolographicTVAction */
        $alienHolographicTVAction = $this->getReference(ActionsFixtures::PUBLIC_BROADCAST);

        $alienHolographicTV = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV));
        $alienHolographicTV
            ->setActionConfigs([
                $takeAction, $dropAction, $hideAction,
                $repair3, $sabotage3, $reportAction, $examineAction,
                $alienHolographicTVAction,
            ])
            ->setInitStatuses([$alienArtifactStatus])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($alienHolographicTV);

        $medikit = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::MEDIKIT));
        $medikit
            ->setActionConfigs([$takeAction, $dropAction, $hideAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($medikit);

        /** @var ActionConfig $removeSporeAction */
        $removeSporeAction = $this->getReference(ActionsFixtures::REMOVE_SPORE);

        $sporeSucker = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::SPORE_SUCKER));
        $sporeSucker
            ->setActionConfigs([
                $takeAction, $dropAction, $hideAction, $examineAction,
                $repair25, $sabotage25,
                $removeSporeAction,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($sporeSucker);

        /** @var ActionConfig $ultraHealAction */
        $ultraHealAction = $this->getReference(ActionsFixtures::HEAL_ULTRA);

        $alienModifierGear = new Gear();

        /** @var AbstractModifierConfig $alienOilModifier */
        $alienOilModifier = $this->getReference(GearModifierConfigFixtures::ALIEN_OIL_INCREASE_FUEL_INJECTED);
        $alienModifierGear
            ->setModifierConfigs([$alienOilModifier])
            ->setName('alien_oil_gear_default');
        $manager->persist($alienModifierGear);

        /** @var ActionConfig $insertFuelChamber */
        $insertFuelChamber = $this->getReference(ActionsFixtures::INSERT_FUEL_CHAMBER);
        $jarOfAlienOil = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::JAR_OF_ALIEN_OIL));
        $jarOfAlienOil
            ->setActionConfigs([$takeAction, $dropAction, $hideAction, $examineAction, $ultraHealAction, $insertFuelChamber])
            ->setInitStatuses([$alienArtifactStatus])
            ->setMechanics([$alienModifierGear])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($jarOfAlienOil);

        /** @var ActionConfig $bandageAction */
        $bandageAction = $this->getReference(ActionsFixtures::BANDAGE_DEFAULT);

        $bandage = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::BANDAGE));
        $bandage
            ->setActionConfigs([$takeAction, $dropAction, $hideAction, $examineAction, $bandageAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($bandage);

        /** @var ActionConfig $serumAction */
        $serumAction = $this->getReference(ActionsFixtures::INJECT_SERUM);

        $retroFungalSerumMechanic = new Tool();
        $retroFungalSerumMechanic
            ->addAction($serumAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . ToolItemEnum::RETRO_FUNGAL_SERUM, GameConfigEnum::DEFAULT);

        $retroFungalSerum = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::RETRO_FUNGAL_SERUM));
        $retroFungalSerum
            ->setMechanics([$retroFungalSerumMechanic])
            ->setActionConfigs([$takeAction, $dropAction, $hideAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($retroFungalSerum);
        $manager->persist($retroFungalSerumMechanic);

        /** @var ActionConfig $spaceCapsuleAction */
        $spaceCapsuleAction = $this->getReference(ActionsFixtures::OPEN_SPACE_CAPSULE);

        $spaceCapsule = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::SPACE_CAPSULE));
        $spaceCapsule
            ->setActionConfigs([$takeAction, $dropAction, $hideAction, $examineAction, $spaceCapsuleAction])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($spaceCapsule);

        /** @var ActionConfig $autoEjectAction */
        $autoEjectAction = $this->getReference(ActionsFixtures::AUTO_EJECT);

        $spaceSuitMechanic = new Tool();
        $spaceSuitMechanic
            ->addAction($autoEjectAction)
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . GearItemEnum::SPACESUIT, GameConfigEnum::DEFAULT);

        $spaceSuit = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::SPACESUIT));
        $spaceSuit
            ->setActionConfigs([$takeAction, $dropAction, $hideAction, $examineAction, $dismantle12, $repair6, $sabotage6, $reportAction])
            ->setMechanics([$spaceSuitMechanic])
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->addEquipmentConfig($spaceSuit);
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
