<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Item\Entity\Items\Exploration;
use Mush\Item\Entity\Items\Fruit;
use Mush\Item\Entity\Items\Gear;
use Mush\Item\Entity\Items\Instrument;
use Mush\Item\Entity\Items\Misc;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Entity\Items\Tool;
use Mush\Item\Entity\Items\Weapon;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\ItemTypeEnum;

class ItemConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $apron = new Gear();
        $apron
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::STAINPROOF_APRON)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
        ;
        $manager->persist($apron);

        $plasteniteArmor = new Gear();
        $plasteniteArmor
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::PLASTENITE_ARMOR)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
        ;
        $manager->persist($plasteniteArmor);

        $hackerKit = new Tool();
        $hackerKit
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::HACKER_KIT)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
        ;
        $manager->persist($hackerKit);

        $blockOfPostIt = new Tool();
        $blockOfPostIt
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BLOCK_OF_POST_IT)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($blockOfPostIt);

        $blaster = new Weapon();
        $blaster
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BLASTER)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($blaster);

        $compass = new Exploration();
        $compass
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::QUADRIMETRIC_COMPASS)

            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($compass);

        $camera = new Instrument();
        $camera
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::CAMERA)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($camera);

        $wrench = new Gear();
        $wrench
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ADJUSTABLE_WRENCH)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($wrench);

        $rope = new Exploration();
        $rope
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROPE)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsTakeable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($rope);

        $knife = new Weapon();
        $knife
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::KNIFE)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($knife);

        $extinguisher = new Tool();
        $extinguisher
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::EXTINGUISHER)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($extinguisher);

        $drill = new Exploration();
        $drill
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::DRILL)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($drill);

        $gloves = new Gear();
        $gloves
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::PROTECTIVE_GLOVES)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($gloves);

        $grenade = new Weapon();
        $grenade
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::GRENADE)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($grenade);

        $hydropot = new Misc();
        $hydropot
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::HYDROPOT)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($hydropot);

        $ductTape = new Tool();
        $ductTape
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::DUCT_TAPE)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($ductTape);

        $soap = new Gear();
        $soap
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::SOAP)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($soap);

        $tabulatrix = new Tool();
        $tabulatrix
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::TABULATRIX)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($tabulatrix);

        $madKube = new Tool();
        $madKube
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MAD_KUBE)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($madKube);

        $microwave = new Tool();
        $microwave
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MICROWAVE)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($microwave);

        $superFreezer = new Tool();
        $superFreezer
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::SUPERFREEZER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($superFreezer);

        $standardRation = new Ration();
        $standardRation
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::STANDARD_RATION)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMaxActionPoint(4)
            ->setMinActionPoint(4)
            ->setMaxMovementPoint(0)
            ->setMinMovementPoint(0)
            ->setMaxHealthPoint(0)
            ->setMinHealthPoint(0)
            ->setMaxMoralPoint(-1)
            ->setMinMoralPoint(-1)
        ;
        $manager->persist($standardRation);

        $banana = new Fruit();
        $banana
            ->setGameConfig($gameConfig)
            ->setName(GameFruitEnum::BANANA)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMaxActionPoint(1)
            ->setMinActionPoint(1)
            ->setMaxMovementPoint(0)
            ->setMinMovementPoint(0)
            ->setMaxHealthPoint(1)
            ->setMinHealthPoint(1)
            ->setMaxMoralPoint(1)
            ->setMinMoralPoint(1)
        ;
        $manager->persist($banana);

        $bananaTree = new Plant();
        $bananaTree
            ->setGameConfig($gameConfig)
            ->setName(GamePlantEnum::BANANA_TREE)
            ->setFruit($banana)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMaxMaturationTime(36)
            ->setMinMaturationTime(36)
            ->setMaxOxygen(1)
            ->setMinOxygen(1)
        ;
        $manager->persist($bananaTree);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class
        ];
    }
}
