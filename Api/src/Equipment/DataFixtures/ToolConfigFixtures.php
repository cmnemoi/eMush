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

        $hackerKitType = new Tool();
        $hackerKitType->setActions([ActionEnum::HACK]);

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
            ->setMechanics(new ArrayCollection([$hackerKitType]))
        ;
        $manager->persist($hackerKit);
        $manager->persist($hackerKitType);

        $blockOfPostItType = new Tool();
        $blockOfPostItType->setActions([ActionEnum::WRITE]);

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
            ->setMechanics(new ArrayCollection([$blockOfPostItType]))

        ;
        $manager->persist($blockOfPostIt);
        $manager->persist($blockOfPostItType);

        $dismountableType = new Dismountable();
        $dismountableType
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(25)
        ;

        $extinguisherType = new Tool();
        $extinguisherType->setActions([ActionEnum::EXTINGUISH]);

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
            ->setMechanics(new ArrayCollection([$extinguisherType, $dismountableType]))
        ;
        $manager->persist($extinguisher);
        $manager->persist($extinguisherType);
        $manager->persist($dismountableType);

        $ductTapeType = new Tool();
        $ductTapeType->setActions([ActionEnum::GAG]);

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
            ->setMechanics(new ArrayCollection([$ductTapeType]))

        ;
        $manager->persist($ductTape);
        $manager->persist($ductTapeType);

        $madKubeType = new Tool();
        $madKubeType->setActions([ActionEnum::TRY_THE_KUBE]);

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
            ->setMechanics(new ArrayCollection([$madKubeType]))

        ;
        $manager->persist($madKube);
        $manager->persist($madKubeType);

        $kitchenToolsType = new Dismountable();
        $kitchenToolsType
            ->setProducts([ItemEnum::METAL_SCRAPS => 2])
            ->setActionCost(4)
            ->setChancesSuccess(25)
        ;

        $chargedType = new Charged();
        $chargedType
            ->setMaxCharge(4)
            ->setStartCharge(0)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        $microwaveType = new Tool();
        $microwaveType->setActions([ActionEnum::EXPRESS_COOK]);

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
            ->setMechanics(new ArrayCollection([$kitchenToolsType, $microwaveType, $chargedType]))

        ;
        $manager->persist($microwave);
        $manager->persist($microwaveType);
        $manager->persist($kitchenToolsType);
        $manager->persist($chargedType);

        $superFreezerType = new Tool();
        $superFreezerType->setActions([ActionEnum::HYPERFREEZE]);

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
            ->setMechanics(new ArrayCollection([$kitchenToolsType, $superFreezerType]))
        ;
        $manager->persist($superFreezer);
        $manager->persist($superFreezerType);

        $alienHolographicTVType = new Tool();
        $alienHolographicTVType->setActions([ActionEnum::PUBLIC_BROADCAST]);

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
            ->setMechanics(new ArrayCollection([$alienHolographicTVType]))
            ;
        $manager->persist($alienHolographicTV);
        $manager->persist($alienHolographicTVType);

        $medikitType = new Tool();
        $medikitType->setActions([ActionEnum::HEAL]);

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
            ->setMechanics(new ArrayCollection([$medikitType]))
            ;
        $manager->persist($medikit);
        $manager->persist($medikitType);

        $sporeSuckerType = new Tool();
        $sporeSuckerType->setActions([ActionEnum::EXTRACT_SPORE]);

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
            ->setMechanics(new ArrayCollection([$sporeSuckerType]))
            ;
        $manager->persist($sporeSucker);
        $manager->persist($sporeSuckerType);

        $jarOfAlienOilType = new Tool();
        $jarOfAlienOilType->setActions([ActionEnum::ULTRAHEAL]);

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
            ->setMechanics(new ArrayCollection([$jarOfAlienOilType]))
            ;

        $manager->persist($jarOfAlienOil);
        $manager->persist($jarOfAlienOilType);

        $bandageType = new Tool();
        $bandageType->setActions([ActionEnum::USE_BANDAGE]);

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
            ->setMechanics(new ArrayCollection([$bandageType]))
            ;

        $manager->persist($bandage);
        $manager->persist($bandageType);

        $retroFungalSerumType = new Tool();
        $retroFungalSerumType->setActions([ActionEnum::CURE]);

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
            ->setMechanics(new ArrayCollection([$retroFungalSerumType]))
            ;

        $manager->persist($retroFungalSerum);
        $manager->persist($retroFungalSerumType);

        $spaceCapsuleType = new Tool();
        $spaceCapsuleType->setActions([ActionEnum::OPEN]);

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
            ->setMechanics(new ArrayCollection([$spaceCapsuleType]))
            ;

        $manager->persist($spaceCapsule);
        $manager->persist($spaceCapsuleType);

        $metalScrapsType = new Tool();
        $metalScrapsType->setActions([ActionEnum::STRENGTHEN])
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
            ->setMechanics(new ArrayCollection([$metalScrapsType]))

        ;
        $manager->persist($metalScraps);
        $manager->persist($metalScrapsType);

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
