<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Dismountable;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class ToolConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $hackerKitMechanic = new Tool();
        $hackerKitMechanic->setActions([ActionEnum::HACK]);

        $hackerKit = new ItemConfig();
        $hackerKit
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::HACKER_KIT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(6)
            ->setMechanics(new ArrayCollection([$hackerKitMechanic]))
        ;
        $manager->persist($hackerKit);
        $manager->persist($hackerKitMechanic);

        $blockOfPostItMechanic = new Tool();
        $blockOfPostItMechanic->setActions([ActionEnum::WRITE]);

        $blockOfPostIt = new ItemConfig();
        $blockOfPostIt
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::BLOCK_OF_POST_IT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blockOfPostItMechanic]))

        ;
        $manager->persist($blockOfPostIt);
        $manager->persist($blockOfPostItMechanic);

        $dismountableMechanic = new Dismountable();
        $dismountableMechanic
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(25)
        ;

        $extinguisherMechanic = new Tool();
        $extinguisherMechanic->setActions([ActionEnum::EXTINGUISH]);

        $extinguisher = new ItemConfig();
        $extinguisher
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::EXTINGUISHER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$extinguisherMechanic, $dismountableMechanic]))
        ;
        $manager->persist($extinguisher);
        $manager->persist($extinguisherMechanic);
        $manager->persist($dismountableMechanic);

        $ductTapeMechanic = new Tool();
        $ductTapeMechanic->setActions([ActionEnum::GAG]);

        $ductTape = new ItemConfig();
        $ductTape
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::DUCT_TAPE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$ductTapeMechanic]))

        ;
        $manager->persist($ductTape);
        $manager->persist($ductTapeMechanic);

        $madKubeMechanic = new Tool();
        $madKubeMechanic->setActions([ActionEnum::TRY_THE_KUBE]);

        $madKube = new ItemConfig();
        $madKube
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::MAD_KUBE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$madKubeMechanic]))

        ;
        $manager->persist($madKube);
        $manager->persist($madKubeMechanic);

        $kitchenToolsMechanic = new Dismountable();
        $kitchenToolsMechanic
            ->setProducts([ItemEnum::METAL_SCRAPS => 2])
            ->setActionCost(4)
            ->setChancesSuccess(25)
        ;

        $chargedMechanic = new Charged();
        $chargedMechanic
            ->setMaxCharge(4)
            ->setStartCharge(0)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        $microwaveMechanic = new Tool();
        $microwaveMechanic->setActions([ActionEnum::EXPRESS_COOK]);

        $microwave = new ItemConfig();
        $microwave
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::MICROWAVE)
            ->setIsHeavy(true)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(50)
            ->setMechanics(new ArrayCollection([$kitchenToolsMechanic, $microwaveMechanic, $chargedMechanic]))

        ;
        $manager->persist($microwave);
        $manager->persist($microwaveMechanic);
        $manager->persist($kitchenToolsMechanic);
        $manager->persist($chargedMechanic);

        $superFreezerMechanic = new Tool();
        $superFreezerMechanic->setActions([ActionEnum::HYPERFREEZE]);

        $superFreezer = new ItemConfig();
        $superFreezer
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$kitchenToolsMechanic, $superFreezerMechanic]))
        ;
        $manager->persist($superFreezer);
        $manager->persist($superFreezerMechanic);

        $alienHolographicTVMechanic = new Tool();
        $alienHolographicTVMechanic->setActions([ActionEnum::PUBLIC_BROADCAST]);

        $alienHolographicTV = new ItemConfig();
        $alienHolographicTV
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::ALIEN_HOLOGRAPHIC_TV)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(3)
            ->setIsAlienArtifact(true)
            ->setMechanics(new ArrayCollection([$alienHolographicTVMechanic]))
            ;
        $manager->persist($alienHolographicTV);
        $manager->persist($alienHolographicTVMechanic);

        $medikitMechanic = new Tool();
        $medikitMechanic->setActions([ActionEnum::HEAL]);

        $medikit = new ItemConfig();
        $medikit
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::MEDIKIT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$medikitMechanic]))
            ;
        $manager->persist($medikit);
        $manager->persist($medikitMechanic);

        $sporeSuckerMechanic = new Tool();
        $sporeSuckerMechanic->setActions([ActionEnum::EXTRACT_SPORE]);

        $sporeSucker = new ItemConfig();
        $sporeSucker->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::SPORE_SUCKER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(18)
            ->setMechanics(new ArrayCollection([$sporeSuckerMechanic]))
            ;
        $manager->persist($sporeSucker);
        $manager->persist($sporeSuckerMechanic);

        $jarOfAlienOilMechanic = new Tool();
        $jarOfAlienOilMechanic->setActions([ActionEnum::ULTRAHEAL]);

        $jarOfAlienOil = new ItemConfig();
        $jarOfAlienOil->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::JAR_OF_ALIEN_OIL)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsAlienArtifact(true)
            ->setMechanics(new ArrayCollection([$jarOfAlienOilMechanic]))
            ;

        $manager->persist($jarOfAlienOil);
        $manager->persist($jarOfAlienOilMechanic);

        $bandageMechanic = new Tool();
        $bandageMechanic->setActions([ActionEnum::USE_BANDAGE]);

        $bandage = new ItemConfig();
        $bandage->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::BANDAGE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setMechanics(new ArrayCollection([$bandageMechanic]))
            ;

        $manager->persist($bandage);
        $manager->persist($bandageMechanic);

        $retroFungalSerumMechanic = new Tool();
        $retroFungalSerumMechanic->setActions([ActionEnum::CURE]);

        $retroFungalSerum = new ItemConfig();
        $retroFungalSerum->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::RETRO_FUNGAL_SERUM)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$retroFungalSerumMechanic]))
            ;

        $manager->persist($retroFungalSerum);
        $manager->persist($retroFungalSerumMechanic);

        $spaceCapsuleMechanic = new Tool();
        $spaceCapsuleMechanic->setActions([ActionEnum::OPEN]);

        $spaceCapsule = new ItemConfig();
        $spaceCapsule->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::SPACE_CAPSULE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$spaceCapsuleMechanic]))
            ;

        $manager->persist($spaceCapsule);
        $manager->persist($spaceCapsuleMechanic);

        $metalScrapsMechanic = new Tool();
        $metalScrapsMechanic->setActions([ActionEnum::STRENGTHEN])
        ;

        $metalScraps = new ItemConfig();
        $metalScraps
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$metalScrapsMechanic]))

        ;
        $manager->persist($metalScraps);
        $manager->persist($metalScrapsMechanic);

        $this->addReference(ToolItemEnum::EXTINGUISHER, $extinguisher);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
