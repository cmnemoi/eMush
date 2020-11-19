<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Blueprint;
use Mush\Item\Entity\Items\Book;
use Mush\Item\Entity\Items\Dismountable;
use Mush\Item\Entity\Items\Drug;
use Mush\Item\Entity\Items\Fruit;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Entity\Items\Weapon;
use Mush\Item\Enum\GameDrugEnum;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Enum\ItemEnum;
use Mush\Status\Enum\DiseaseEnum;
use Mush\Status\Enum\DisorderEnum;

class ItemConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $dismountableType1 = new Dismountable();
        $dismountableType1
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(25)
        ;

        $camera = new Item();
        $camera
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::CAMERA)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setTypes(new ArrayCollection([$dismountableType1]))
        ;
        $manager->persist($camera);
        $manager->persist($dismountableType1);


        $mycoAlarm = new Item();
        $mycoAlarm
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MYCO_ALARM)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setTypes(new ArrayCollection([$dismountableType1]))
        ;
        $manager->persist($mycoAlarm);


        $dismountableType2 = new Dismountable();
        $dismountableType2
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(12)
        ;
        $tabulatrix = new Item();
        $tabulatrix
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::TABULATRIX)
            ->setIsHeavy(false)
            ->setIsTakeable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setTypes(new ArrayCollection([$dismountableType2]))
        ;
        $manager->persist($tabulatrix);
        $manager->persist($dismountableType2);

        $plasticScraps = new Item();
        $plasticScraps
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::PLASTIC_SCRAPS)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($plasticScraps);

        $oldTShirt = new Item();
        $oldTShirt
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::OLD_T_SHIRT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($oldTShirt);


        $dismountableType3 = new Dismountable();
        $dismountableType3
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(50)
        ;

        $thickTube = new Item();
        $thickTube
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::THICK_TUBE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$dismountableType3]))
        ;
        $manager->persist($thickTube);
        $manager->persist($dismountableType3);

        $dismountableType4 = new Dismountable();
        $dismountableType4
            ->setProducts([ItemEnum::PLASTIC_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(50)
        ;

        $mushDisk = new Item();
        $mushDisk
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MUSH_GENOME_DISK)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setTypes(new ArrayCollection([$dismountableType4]))
        ;
        $manager->persist($mushDisk);
        $manager->persist($dismountableType4);


        $mushSample = new Item();
        $mushSample
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MUSH_SAMPLE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($mushSample);


        $starmapFragment = new Item();
        $starmapFragment
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::STARMAP_FRAGMENT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($starmapFragment);

        $waterStick = new Item();
        $waterStick
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::WATER_STICK)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($waterStick);

        //@TODO add drones, cat, coffee thermos, lunchbox, survival kit, oxygen capsule, fuel capsule
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
