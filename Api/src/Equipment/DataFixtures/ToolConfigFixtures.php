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
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
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
        /** @var Action $takeAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $buildAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

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

        // @TODO
        $hackerKitMechanic = new Tool();

        $hackerKit = new ItemConfig();
        $hackerKit
            ->setName(ToolItemEnum::HACKER_KIT)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$hackerKitMechanic]))
            ->setActions(new ArrayCollection([
                $takeAction,
                $dropAction,
                $hideAction,
                $repair6,
                $sabotage6,
                $reportAction,
                $examineAction,
            ]))
        ;
        $manager->persist($hackerKit);
        $manager->persist($hackerKitMechanic);

        /** @var Action $writeAction */
        $writeAction = $this->getReference(ActionsFixtures::WRITE_DEFAULT);

        $blockOfPostItMechanic = new Tool();
        $blockOfPostItMechanic->addAction($writeAction);

        $blockOfPostIt = new ItemConfig();
        $blockOfPostIt
            ->setName(ToolItemEnum::BLOCK_OF_POST_IT)
            ->setIsStackable(false)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blockOfPostItMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($blockOfPostIt);
        $manager->persist($blockOfPostItMechanic);

        /** @var Action $installCamera */
        $installCamera = $this->getReference(ActionsFixtures::INSTALL_CAMERA);
        $cameraMechanics = new Tool();
        $cameraMechanics->addAction($installCamera);

        $cameraActions = new ArrayCollection([
            $takeAction,
            $this->getReference(TechnicianFixtures::DISMANTLE_3_25),
            $repair25,
            $reportAction,
            $examineAction,
        ]);

        $camera = new ItemConfig();
        $camera
            ->setName(ItemEnum::CAMERA_ITEM)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$cameraMechanics]))
            ->setActions($cameraActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($camera);
        $manager->persist($cameraMechanics);

        $extinguisherActions = clone $actions;
        $extinguisherActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_25));
        $extinguisherActions->add($repair25);
        $extinguisherActions->add($sabotage25);
        $extinguisherActions->add($reportAction);

        /** @var Action $extinguishAction */
        $extinguishAction = $this->getReference(ActionsFixtures::EXTINGUISH_DEFAULT);

        $extinguisherMechanic = new Tool();
        $extinguisherMechanic->addAction($extinguishAction);

        $extinguisher = new ItemConfig();
        $extinguisher
            ->setName(ToolItemEnum::EXTINGUISHER)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$extinguisherMechanic]))
            ->setActions($extinguisherActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($extinguisher);
        $manager->persist($extinguisherMechanic);

        /** @var Action $gagAction */
        $gagAction = $this->getReference(ActionsFixtures::GAG_DEFAULT);

        $ductTapeMechanic = new Tool();
        $ductTapeMechanic->addAction($gagAction);

        $ductTape = new ItemConfig();
        $ductTape
            ->setName(ToolItemEnum::DUCT_TAPE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$ductTapeMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($ductTape);
        $manager->persist($ductTapeMechanic);

        /** @var Action $tryKubeAction */
        $tryKubeAction = $this->getReference(ActionsFixtures::TRY_KUBE);

        $madKubeMechanic = new Tool();
        $madKubeMechanic->addAction($tryKubeAction);

        $madKube = new ItemConfig();
        $madKube
            ->setName(ToolItemEnum::MAD_KUBE)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$madKubeMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($madKube);
        $manager->persist($madKubeMechanic);

        $microwaveActions = clone $actions;
        $microwaveActions->add($this->getReference(TechnicianFixtures::DISMANTLE_4_25));
        $microwaveActions->add($repair50);
        $microwaveActions->add($sabotage50);
        $microwaveActions->add($reportAction);

        /** @var Action $expressCookAction */
        $expressCookAction = $this->getReference(ActionsFixtures::COOK_EXPRESS);

        $microwaveMechanic = new Tool();
        $microwaveMechanic->addAction($expressCookAction);

        /** @var ChargeStatusConfig $microwaveCharge */
        $microwaveCharge = $this->getReference(ChargeStatusFixtures::MICROWAVE_CHARGE);

        $microwave = new ItemConfig();
        $microwave
            ->setName(ToolItemEnum::MICROWAVE)
            ->setIsStackable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$microwaveMechanic]))
            ->setActions($microwaveActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 2])
            ->setInitStatus(new ArrayCollection([$heavyStatus, $microwaveCharge]))
        ;

        $manager->persist($microwave);
        $manager->persist($microwaveMechanic);

        /** @var Action $hyperfreezeAction */
        $hyperfreezeAction = $this->getReference(ActionsFixtures::HYPERFREEZE_DEFAULT);

        $superFreezerMechanic = new Tool();
        $superFreezerMechanic->addAction($hyperfreezeAction);

        $superfreezerActions = clone $actions;
        $superfreezerActions->add($this->getReference(TechnicianFixtures::DISMANTLE_4_25));
        $superfreezerActions->add($repair25);
        $superfreezerActions->add($sabotage25);
        $superfreezerActions->add($reportAction);

        $superFreezer = new ItemConfig();
        $superFreezer
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$superFreezerMechanic]))
            ->setActions($superfreezerActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 2])
            ->setInitStatus(new ArrayCollection([$heavyStatus]))
        ;

        $manager->persist($superFreezer);
        $manager->persist($superFreezerMechanic);

        /** @var Action $alienHolographicTVAction */
        $alienHolographicTVAction = $this->getReference(ActionsFixtures::PUBLIC_BROADCAST);

        $alienHolographicTVMechanic = new Tool();
        $alienHolographicTVMechanic->addAction($alienHolographicTVAction);

        $alienHolographicTV = new ItemConfig();
        $alienHolographicTV
            ->setName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$alienHolographicTVMechanic]))
            ->setActions(new ArrayCollection([$takeAction, $dropAction, $hideAction, $repair3, $sabotage3, $reportAction, $examineAction]))
            ->setInitStatus(new ArrayCollection([$alienArtifactStatus]))
        ;

        $manager->persist($alienHolographicTV);
        $manager->persist($alienHolographicTVMechanic);

        $medikit = new ItemConfig();
        $medikit
            ->setName(ToolItemEnum::MEDIKIT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($medikit);

        /** @var Action $removeSporeAction */
        $removeSporeAction = $this->getReference(ActionsFixtures::REMOVE_SPORE);

        $sporeSuckerMechanic = new Tool();
        $sporeSuckerMechanic->addAction($removeSporeAction);

        $sporeSucker = new ItemConfig();
        $sporeSucker
            ->setName(ToolItemEnum::SPORE_SUCKER)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$sporeSuckerMechanic]))
            ->setActions($actions) // @FIXME add repair and sabotage
        ;

        $manager->persist($sporeSucker);
        $manager->persist($sporeSuckerMechanic);

        /** @var Action $ultraHealAction */
        $ultraHealAction = $this->getReference(ActionsFixtures::HEAL_ULTRA);

        $jarOfAlienOilMechanic = new Tool();
        $jarOfAlienOilMechanic->addAction($ultraHealAction);

        $jarOfAlienOil = new ItemConfig();
        $jarOfAlienOil
            ->setName(ToolItemEnum::JAR_OF_ALIEN_OIL)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$jarOfAlienOilMechanic]))
            ->setActions($actions)
            ->setInitStatus(new ArrayCollection([$alienArtifactStatus]))
        ;

        $manager->persist($jarOfAlienOil);
        $manager->persist($jarOfAlienOilMechanic);

        /** @var Action $bandageAction */
        $bandageAction = $this->getReference(ActionsFixtures::BANDAGE_DEFAULT);

        $bandageMechanic = new Tool();
        $bandageMechanic->addAction($bandageAction);

        $bandage = new ItemConfig();
        $bandage
            ->setName(ToolItemEnum::BANDAGE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$bandageMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($bandage);
        $manager->persist($bandageMechanic);

        /** @var Action $serumAction */
        $serumAction = $this->getReference(ActionsFixtures::INJECT_SERUM);

        $retroFungalSerumMechanic = new Tool();
        $retroFungalSerumMechanic->addAction($serumAction);

        $retroFungalSerum = new ItemConfig();
        $retroFungalSerum
            ->setName(ToolItemEnum::RETRO_FUNGAL_SERUM)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$retroFungalSerumMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($retroFungalSerum);
        $manager->persist($retroFungalSerumMechanic);

        /** @var Action $spaceCapsuleAction */
        $spaceCapsuleAction = $this->getReference(ActionsFixtures::OPEN_SPACE_CAPSULE);

        $spaceCapsuleMechanic = new Tool();
        $spaceCapsuleMechanic->addAction($spaceCapsuleAction);

        $spaceCapsule = new ItemConfig();
        $spaceCapsule
            ->setName(ToolItemEnum::SPACE_CAPSULE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$spaceCapsuleMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($spaceCapsule);
        $manager->persist($spaceCapsuleMechanic);

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
