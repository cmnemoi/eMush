<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

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

        $actions = new ArrayCollection([$takeAction, $dropAction]);

        //@TODO
        $hackerKitMechanic = new Tool();

        $hackerKit = new ItemConfig();
        $hackerKit
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::HACKER_KIT)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(6)
            ->setMechanics(new ArrayCollection([$hackerKitMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($hackerKit);
        $manager->persist($hackerKitMechanic);

        /** @var Action $writeAction */
        $writeAction = $this->getReference(ActionsFixtures::WRITE_DEFAULT);

        $blockOfPostItMechanic = new Tool();
        $blockOfPostItMechanic->addAction($writeAction);

        $blockOfPostIt = new ItemConfig();
        $blockOfPostIt
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::BLOCK_OF_POST_IT)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blockOfPostItMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($blockOfPostIt);
        $manager->persist($blockOfPostItMechanic);

        $extinguisherActions = clone $actions;
        $extinguisherActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_25));

        /** @var Action $extinguishAction */
        $extinguishAction = $this->getReference(ActionsFixtures::EXTINGUISH_DEFAULT);

        $extinguisherMechanic = new Tool();
        $extinguisherMechanic->addAction($extinguishAction);

        $extinguisher = new ItemConfig();
        $extinguisher
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::EXTINGUISHER)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
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
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::DUCT_TAPE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$ductTapeMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($ductTape);
        $manager->persist($ductTapeMechanic);

        /** @var Action $tryTheKubeAction */
        $tryTheKubeAction = $this->getReference(ActionsFixtures::TRY_KUBE);

        $madKubeMechanic = new Tool();
        $madKubeMechanic->addAction($tryTheKubeAction);

        $madKube = new ItemConfig();
        $madKube
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::MAD_KUBE)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$madKubeMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($madKube);
        $manager->persist($madKubeMechanic);

        $microwaveActions = clone $actions;
        $microwaveActions->add($this->getReference(TechnicianFixtures::DISMANTLE_4_25));

        $chargedMechanic = new Charged();
        $chargedMechanic
            ->setMaxCharge(4)
            ->setStartCharge(1)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        /** @var Action $expressCookAction */
        $expressCookAction = $this->getReference(ActionsFixtures::COOK_EXPRESS);

        $microwaveMechanic = new Tool();
        $microwaveMechanic->addAction($expressCookAction);

        $microwave = new ItemConfig();
        $microwave
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setIsHeavy(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(50)
            ->setMechanics(new ArrayCollection([$microwaveMechanic, $chargedMechanic]))
            ->setActions($microwaveActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 2])
        ;

        $manager->persist($microwave);
        $manager->persist($microwaveMechanic);
        $manager->persist($chargedMechanic);

        /** @var Action $hyperfreezAction */
        $hyperfreezAction = $this->getReference(ActionsFixtures::HYPERFREEZE_DEFAULT);

        $superFreezerMechanic = new Tool();
        $superFreezerMechanic->addAction($hyperfreezAction);

        $superFreezer = new ItemConfig();
        $superFreezer
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setIsHeavy(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$superFreezerMechanic]))
            ->setActions($microwaveActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 2])
        ;

        $manager->persist($superFreezer);
        $manager->persist($superFreezerMechanic);

        //@TODO
        $alienHolographicTVMechanic = new Tool();
//        $alienHolographicTVMechanic->setActions([ActionEnum::PUBLIC_BROADCAST]);

        $alienHolographicTV = new ItemConfig();
        $alienHolographicTV
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(3)
            ->setIsAlienArtifact(true)
            ->setMechanics(new ArrayCollection([$alienHolographicTVMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($alienHolographicTV);
        $manager->persist($alienHolographicTVMechanic);

        /** @var Action $healAction */
        $healAction = $this->getReference(ActionsFixtures::HEAL_DEFAULT);
        /** @var Action $selfHealAction */
        $selfHealAction = $this->getReference(ActionsFixtures::HEAL_SELF);

        $medikitMechanic = new Tool();
        $medikitMechanic
            ->addAction($healAction)
            ->addAction($selfHealAction)
        ;

        $medikit = new ItemConfig();
        $medikit
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::MEDIKIT)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$medikitMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($medikit);
        $manager->persist($medikitMechanic);

        //@TODO
        $sporeSuckerMechanic = new Tool();
//        $sporeSuckerMechanic->setActions([ActionEnum::EXTRACT_SPORE]);

        $sporeSucker = new ItemConfig();
        $sporeSucker->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::SPORE_SUCKER)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(18)
            ->setMechanics(new ArrayCollection([$sporeSuckerMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($sporeSucker);
        $manager->persist($sporeSuckerMechanic);

        /** @var Action $ultraHealAction */
        $ultraHealAction = $this->getReference(ActionsFixtures::HEAL_ULTRA);

        $jarOfAlienOilMechanic = new Tool();
        $jarOfAlienOilMechanic->addAction($ultraHealAction);

        $jarOfAlienOil = new ItemConfig();
        $jarOfAlienOil->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::JAR_OF_ALIEN_OIL)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsAlienArtifact(true)
            ->setMechanics(new ArrayCollection([$jarOfAlienOilMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($jarOfAlienOil);
        $manager->persist($jarOfAlienOilMechanic);

        /** @var Action $bandageAction */
        $bandageAction = $this->getReference(ActionsFixtures::BANDAGE_DEFAULT);

        $bandageMechanic = new Tool();
        $bandageMechanic->addAction($bandageAction);

        $bandage = new ItemConfig();
        $bandage->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::BANDAGE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
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
        $retroFungalSerum->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::RETRO_FUNGAL_SERUM)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
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
        $spaceCapsule->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::SPACE_CAPSULE)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$spaceCapsuleMechanic]))
            ->setActions($actions)
        ;

        $manager->persist($spaceCapsule);
        $manager->persist($spaceCapsuleMechanic);

        $this->addReference(ToolItemEnum::EXTINGUISHER, $extinguisher);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            GameConfigFixtures::class,
        ];
    }
}
