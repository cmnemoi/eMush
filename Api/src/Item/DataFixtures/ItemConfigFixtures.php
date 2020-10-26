<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Item\Entity\Exploration;
use Mush\Item\Entity\Gear;
use Mush\Item\Entity\Instrument;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Misc;
use Mush\Item\Entity\Ration;
use Mush\Item\Entity\Tool;
use Mush\Item\Entity\Weapon;
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
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsMovable(true)
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
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsMovable(true)
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
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)

        ;
        $manager->persist($hackerKit);

        $blockOfPostIt = new Tool();
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

        $blaster = new Weapon();
        $blaster
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BLASTER)
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

        $compass = new Exploration();
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

        $camera = new Instrument();
        $camera
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::CAMERA)
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

        $wrench = new Gear();
        $wrench
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ADJUSTABLE_WRENCH)
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

        $rope = new Exploration();
        $rope
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROPE)
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

        $knife = new Weapon();
        $knife
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::KNIFE)
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

        $extinguisher = new Tool();
        $extinguisher
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::EXTINGUISHER)
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

        $drill = new Exploration();
        $drill
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::DRILL)
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

        $gloves = new Gear();
        $gloves
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::PROTECTIVE_GLOVES)
            ->setIsHeavy(false)
            ->setIsDismantable(false)->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
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
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
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
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
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
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
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
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
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
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
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
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
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
            ->setIsDropable(false)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsMovable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($microwave);

        $superFreezer = new Tool();
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

        $standardRation = new Ration();
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
            ->setHealthPoint(0)
            ->setActionPoint(4)
            ->setMoralPoint(-1)

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
