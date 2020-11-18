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
use Mush\Item\Entity\Items\Fruit;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Entity\Items\Tool;
use Mush\Item\Entity\Items\Drug;
use Mush\Item\Entity\Items\Weapon;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Enum\GameDrugEnum;
use Mush\Item\Enum\ItemEnum;
use Mush\Status\Enum\DiseaseEnum;
use Mush\Status\Enum\DisorderEnum;

class WeaponConfigFixtures extends Fixture implements DependentFixtureInterface
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

         // @TODO more details are needed on the output of each weapon
        $blasterType = new Weapon();
        $blasterType
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 5])
            ->setBaseInjuryNumber([0 => 1])
            ->setExpeditionBonus(1)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;


        $blaster = new Item();
        $blaster
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BLASTER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setTypes(new ArrayCollection([$dismountableType1, $blasterType]))
        ;
        $manager->persist($dismountableType1);
        $manager->persist($blasterType);
        $manager->persist($blaster);



        $knifeType = new Weapon();
        $knifeType
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([1 => 5])
            ->setBaseInjuryNumber([0 => 1])
            ->setExpeditionBonus(1)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $knife = new Item();
        $knife
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::KNIFE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setTypes(new ArrayCollection([$dismountableType1, $knifeType]))

        ;
        $manager->persist($knife);
        $manager->persist($knifeType);



        $grenadeType = new Weapon();
        $grenadeType
            ->setBaseAccuracy(100)
            ->setBaseDamageRange([0 => 10])
            ->setBaseInjuryNumber([0 => 1])
            ->setExpeditionBonus(3)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
            ;

        $grenade = new Item();
        $grenade
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::GRENADE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$grenadeType]))

        ;
        $manager->persist($grenade);
        $manager->persist($grenadeType);


        $dismountableType2 = new Dismountable();
        $dismountableType2
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(12)
        ;

        $natamyType = new Weapon();
        $natamyType
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 12])
            ->setBaseInjuryNumber([1 => 3])
            ->setExpeditionBonus(1)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $natamy = new Item();
        $natamy
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::NATAMY)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setTypes(new ArrayCollection([$dismountableType2, $natamyType]))
        ;
        $manager->persist($natamy);
        $manager->persist($natamyType);


        $dismountableType3 = new Dismountable();
        $dismountableType3
            ->setProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(4)
            ->setChancesSuccess(12)
        ;

        $oldFaithfulType = new Weapon();
        $oldFaithfulType
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([2 => 3])
            ->setBaseInjuryNumber([0 => 3])
            ->setExpeditionBonus(2)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $oldFaithful = new Item();
        $oldFaithful
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::OLD_FAITHFUL)
            ->setIsHeavy(true)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setTypes(new ArrayCollection([$dismountableType3, $oldFaithfulType]))

        ;
        $manager->persist($oldFaithful);
        $manager->persist($oldFaithfulType);


        $dismountableType4 = new Dismountable();
        $dismountableType4
            ->setProducts([ItemEnum::METAL_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->setActionCost(3)
            ->setChancesSuccess(12)
        ;

        $lizaroJungleType = new Weapon();
        $lizaroJungleType
            ->setBaseAccuracy(99)
            ->setBaseDamageRange([3 => 5])
            ->setBaseInjuryNumber([1 => 2])
            ->setExpeditionBonus(1)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $lizaroJungle = new Item();
        $lizaroJungle
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::LIZARO_JUNGLE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setTypes(new ArrayCollection([$dismountableType4, $lizaroJungleType]))

        ;
        $manager->persist($lizaroJungle);
        $manager->persist($lizaroJungleType);


        $rocketLauncherType = new Weapon();
        $rocketLauncherType
            ->setBaseAccuracy(50)
            ->setBaseDamageRange([0 => 8])
            ->setBaseInjuryNumber([0 => 2])
            ->setExpeditionBonus(3)
            ->setCriticalSucessEvents([])
            ->setCriticalSucessEvents([])
        ;

        $rocketLauncher = new Item();
        $rocketLauncher
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROCKET_LAUNCHER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setTypes(new ArrayCollection([$dismountableType2, $rocketLauncherType]))

        ;
        $manager->persist($rocketLauncher);
        $manager->persist($rocketLauncherType);


        $this->addReference(self::GRENADE, $grenade);
        $this->addReference(self::OLD_FAITHFUL, $oldFaithful);
        $this->addReference(self::LIZARO_JUNGLE, $lizaroJungle);
        $this->addReference(self::ROCKET_LAUNCHER, $rocketLauncher);


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
