<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Item\Entity\Item;
use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\ItemTypeEnum;

class ItemConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $apron = new Item();
        $apron
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::STAINPROOF_APRON)
            ->setType(ItemTypeEnum::GEAR)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
        ;
        $manager->persist($apron);

        $plasteniteArmor = new Item();
        $plasteniteArmor
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::PLASTENITE_ARMOR)
            ->setType(ItemTypeEnum::GEAR)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
        ;
        $manager->persist($plasteniteArmor);

        $hackerKit = new Item();
        $hackerKit
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::HACKER_KIT)
            ->setType(ItemTypeEnum::TOOL)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)

        ;
        $manager->persist($hackerKit);

        $blockOfPostIt = new Item();
        $blockOfPostIt
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BLOCK_OF_POST_IT)
            ->setType(ItemTypeEnum::TOOL)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsMovable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($blockOfPostIt);

        $blaster = new Item();
        $blaster
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BLASTER)
            ->setType(ItemTypeEnum::WEAPON)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($blaster);

        $compass = new Item();
        $compass
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::QUADRIMETRIC_COMPASS)
            ->setType(ItemTypeEnum::EXPLORATION)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($compass);

        $camera = new Item();
        $camera
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::CAMERA)
            ->setType(ItemTypeEnum::INSTRUMENT)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($camera);

        $camera = new Item();
        $camera
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::CAMERA)
            ->setType(ItemTypeEnum::INSTRUMENT)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($camera);

        $wrench = new Item();
        $wrench
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ADJUSTABLE_WRENCH)
            ->setType(ItemTypeEnum::GEAR)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($wrench);

        $rope = new Item();
        $rope
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROPE)
            ->setType(ItemTypeEnum::EXPLORATION)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($rope);

        $knife = new Item();
        $knife
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::KNIFE)
            ->setType(ItemTypeEnum::WEAPON)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($knife);

        $extinguisher = new Item();
        $extinguisher
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::EXTINGUISHER)
            ->setType(ItemTypeEnum::TOOL)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($extinguisher);

        $drill = new Item();
        $drill
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::DRILL)
            ->setType(ItemTypeEnum::EXPLORATION)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($drill);

        $gloves = new Item();
        $gloves
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::PROTECTIVE_GLOVES)
            ->setType(ItemTypeEnum::GEAR)
            ->setIsHeavy(false)
            ->setIsDismantable(false)->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($gloves);

        $grenade = new Item();
        $grenade
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::GRENADE)
            ->setType(ItemTypeEnum::WEAPON)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($grenade);

        $hydropot = new Item();
        $hydropot
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::HYDROPOT)
            ->setType(ItemTypeEnum::MISC)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($hydropot);

        $ductTape = new Item();
        $ductTape
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::DUCT_TAPE)
            ->setType(ItemTypeEnum::TOOL)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($ductTape);

        $soap = new Item();
        $soap
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::SOAP)
            ->setType(ItemTypeEnum::GEAR)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($soap);

        $tabulatrix = new Item();
        $tabulatrix
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::TABULATRIX)
            ->setType(ItemTypeEnum::TOOL)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($tabulatrix);

        $madKube = new Item();
        $madKube
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MAD_KUBE)
            ->setType(ItemTypeEnum::TOOL)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($madKube);

        $microwave = new Item();
        $microwave
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::MICROWAVE)
            ->setType(ItemTypeEnum::TOOL)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($microwave);

        $superFreezer = new Item();
        $superFreezer
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::SUPERFREEZER)
            ->setType(ItemTypeEnum::TOOL)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($superFreezer);

        $standardRation = new Item();
        $standardRation
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::STANDARD_RATION)
            ->setType(ItemTypeEnum::RATION)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($standardRation);

        $manager->flush();

        $this->addReference(ItemEnum::STAINPROOF_APRON, $apron);
        $this->addReference(ItemEnum::PLASTENITE_ARMOR, $plasteniteArmor);
        $this->addReference(ItemEnum::HACKER_KIT, $hackerKit);
        $this->addReference(ItemEnum::BLOCK_OF_POST_IT, $blockOfPostIt);
        $this->addReference(ItemEnum::BLASTER, $blaster);
        $this->addReference(ItemEnum::QUADRIMETRIC_COMPASS, $compass);
        $this->addReference(ItemEnum::CAMERA, $camera);
        $this->addReference(ItemEnum::ADJUSTABLE_WRENCH, $wrench);
        $this->addReference(ItemEnum::ROPE, $rope);
        $this->addReference(ItemEnum::KNIFE, $knife);
        $this->addReference(ItemEnum::DRILL, $drill);
        $this->addReference(ItemEnum::EXTINGUISHER, $extinguisher);
        $this->addReference(ItemEnum::PROTECTIVE_GLOVES, $gloves);
        $this->addReference(ItemEnum::GRENADE, $grenade);
        $this->addReference(ItemEnum::HYDROPOT, $hydropot);
        $this->addReference(ItemEnum::DUCT_TAPE, $ductTape);
        $this->addReference(ItemEnum::SOAP, $soap);
        $this->addReference(ItemEnum::TABULATRIX, $tabulatrix);
        $this->addReference(ItemEnum::MAD_KUBE, $madKube);
        $this->addReference(ItemEnum::MICROWAVE, $microwave);
        $this->addReference(ItemEnum::SUPERFREEZER, $superFreezer);
        $this->addReference(ItemEnum::STANDARD_RATION, $standardRation);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class
        ];
    }
}
