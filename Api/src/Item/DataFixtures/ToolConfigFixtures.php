<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Blueprint;
use Mush\Item\Entity\Items\Book;
use Mush\Item\Entity\Items\Dismountable;
use Mush\Item\Entity\Items\Fruit;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Entity\Items\Tool;
use Mush\Item\Entity\Items\Drug;
use Mush\Item\Entity\Items\Weapon;
use Mush\Item\Entity\Items\Charged;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Enum\GameDrugEnum;
use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\ToolItemEnum;
use Mush\Status\Enum\DiseaseEnum;
use Mush\Status\Enum\DisorderEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class ToolConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);



        $hackerKitType = new Tool();
        $hackerKitType->setActions([ActionEnum::HACK]);

        $hackerKit = new Item();
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
            ->setTypes(new ArrayCollection([$hackerKitType]))
        ;
        $manager->persist($hackerKit);
        $manager->persist($hackerKitType);


        $blockOfPostItType = new Tool();
        $blockOfPostItType->setActions([ActionEnum::WRITE]);

        $blockOfPostIt = new Item();
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
            ->setTypes(new ArrayCollection([$blockOfPostItType]))

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

        $extinguisher = new Item();
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
            ->setTypes(new ArrayCollection([$extinguisherType, $dismountableType]))
        ;
        $manager->persist($extinguisher);
        $manager->persist($extinguisherType);
        $manager->persist($dismountableType);


        $ductTapeType = new Tool();
        $ductTapeType->setActions([ActionEnum::GAG]);

        $ductTape = new Item();
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
            ->setTypes(new ArrayCollection([$ductTapeType]))

        ;
        $manager->persist($ductTape);
        $manager->persist($ductTapeType);


        $madKubeType = new Tool();
        $madKubeType->setActions([ActionEnum::TRY_THE_KUBE]);

        $madKube = new Item();
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
            ->setTypes(new ArrayCollection([$madKubeType]))

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

        $microwave = new Item();
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
            ->setTypes(new ArrayCollection([$kitchenToolsType, $microwaveType, $chargedType]))

        ;
        $manager->persist($microwave);
        $manager->persist($microwaveType);
        $manager->persist($kitchenToolsType);
        $manager->persist($chargedType);


        $superFreezerType  = new Tool();
        $superFreezerType ->setActions([ActionEnum::HYPERFREEZE]);

        $superFreezer = new Item();
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
            ->setTypes(new ArrayCollection([$kitchenToolsType, $superFreezerType]))
        ;
        $manager->persist($superFreezer);
        $manager->persist($superFreezerType);


        $alienHolographicTVType = new Tool();
        $alienHolographicTVType ->setActions([ActionEnum::PUBLIC_BROADCAST]);

        $alienHolographicTV = new Item();
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
            ->setTypes(new ArrayCollection([$alienHolographicTVType]))
            ;
        $manager->persist($alienHolographicTV);
        $manager->persist($alienHolographicTVType);


        $medikitType = new Tool();
        $medikitType->setActions([ActionEnum::HEAL]);

        $medikit = new Item();
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
            ->setTypes(new ArrayCollection([$medikitType]))
            ;
        $manager->persist($medikit);
        $manager->persist($medikitType);


        $sporeSuckerType = new Tool();
        $sporeSuckerType ->setActions([ActionEnum::EXTRACT_SPORE]);

        $sporeSucker  = new Item();
        $sporeSucker ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::SPORE_SUCKER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(18)
            ->setTypes(new ArrayCollection([$sporeSuckerType]))
            ;
        $manager->persist($sporeSucker);
        $manager->persist($sporeSuckerType);


        $jarOfAlienOilType = new Tool();
        $jarOfAlienOilType ->setActions([ActionEnum::ULTRAHEAL]);

        $jarOfAlienOil  = new Item();
        $jarOfAlienOil ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::JAR_OF_ALIEN_OIL)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsAlienArtifact(true)
            ->setTypes(new ArrayCollection([$jarOfAlienOilType]))
            ;

        $manager->persist($jarOfAlienOil);
        $manager->persist($jarOfAlienOilType);


        $bandageType = new Tool();
        $bandageType->setActions([ActionEnum::USE_BANDAGE]);

        $bandage = new Item();
        $bandage->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::BANDAGE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setTypes(new ArrayCollection([$bandageType]))
            ;

        $manager->persist($bandage);
        $manager->persist($bandageType);


        $retroFungalSerumType = new Tool();
        $retroFungalSerumType ->setActions([ActionEnum::CURE]);

        $retroFungalSerum = new Item();
        $retroFungalSerum->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::RETRO_FUNGAL_SERUM)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$retroFungalSerumType]))
            ;

        $manager->persist($retroFungalSerum);
        $manager->persist($retroFungalSerumType);


        $spaceCapsuleType = new Tool();
        $spaceCapsuleType->setActions([ActionEnum::OPEN]);

        $spaceCapsule = new Item();
        $spaceCapsule->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::SPACE_CAPSULE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$spaceCapsuleType]))
            ;

        $manager->persist($spaceCapsule);
        $manager->persist($spaceCapsuleType);



        $metalScrapsType  = new Tool();
        $metalScrapsType ->setActions([ActionEnum::STRENGTHEN])
        ;

        $metalScraps = new Item();
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
            ->setTypes(new ArrayCollection([$metalScrapsType]))

        ;
        $manager->persist($metalScraps);
        $manager->persist($metalScrapsType);


        $hydropotType  = new Tool();
        $hydropotType ->setActions([ActionEnum::PLANT_IT])
        ;

        $hydropot = new Item();
        $hydropot
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::HYDROPOT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$hydropotType]))
        ;
        $manager->persist($hydropot);
        $manager->persist($hydropotType);


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
