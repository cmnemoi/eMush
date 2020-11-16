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

class ToolConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $apron = new Item();
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

        $plasteniteArmor = new Item();
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

        $hackerKit = new Item();
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

        $blockOfPostIt = new Item();
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

        $blasterConfig = new Weapon();
        $blasterConfig
            ->setMaxCharges(3)
        ;

        $blaster = new Item();
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
            ->setTypes(new ArrayCollection([$blasterConfig]))
        ;
        $manager->persist($blasterConfig);
        $manager->persist($blaster);

        $compass = new Item();
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

        $camera = new Item();
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

        $wrench = new Item();
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

        $rope = new Item();
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

        $knife = new Item();
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

        $extinguisher = new Item();
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

        $drill = new Item();
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

        $gloves = new Item();
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

        $grenade = new Item();
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

        $hydropot = new Item();
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

        $ductTape = new Item();
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

        $soap = new Item();
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

        $tabulatrix = new Item();
        $tabulatrix
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::TABULATRIX)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($tabulatrix);

        $madKube = new Item();
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

        $kitchenToolsType = new Dismountable();
        $kitchenToolsType
            ->setProducts([ItemEnum::METAL_SCRAPS => 2])
            ->setActionCost(4)
            ->setChancesSuccess(25)
        ;

        $microwave = new Item();
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
            ->setTypes(new ArrayCollection([$kitchenToolsType]))

        ;
        $manager->persist($microwave);
        $manager->persist($kitchenToolsType);

        $superFreezer = new Item();
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
            ->setTypes(new ArrayCollection([$kitchenToolsType]))

        ;
        $manager->persist($superFreezer);

        $plasticScraps = new Item();
        $plasticScraps
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::PLASTIC_SCRAPS)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDismantable(false)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($plasticScraps);

        $metalScraps = new Item();
        $metalScraps
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDismantable(false)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($metalScraps);

        $standardRationType = new Ration();
        $standardRationType
            ->setActionPoints([4])
            ->setMovementPoints([0])
            ->setHealthPoints([0])
            ->setMoralPoints([-1])
            ->setSatiety(4)
        ;

        $standardRation = new Item();
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
            ->setTypes(new ArrayCollection([$standardRationType]))
        ;
        $manager->persist($standardRationType);
        $manager->persist($standardRation);

        $bananaType = new Fruit();
        $bananaType
            ->setActionPoints([1])
            ->setMovementPoints([0])
            ->setHealthPoints([1])
            ->setMoralPoints([1])

        ;

        $banana = new Item();
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
            ->setTypes(new ArrayCollection([$bananaType]))
        ;
        $manager->persist($bananaType);
        $manager->persist($banana);

        $bananaTreeType = new Plant();
        $bananaTreeType
            ->setFruit($banana)
            ->setMaxMaturationTime(36)
            ->setMinMaturationTime(36)
            ->setMaxOxygen(1)
            ->setMinOxygen(1)
        ;

        $bananaTree = new Item();
        $bananaTree
            ->setGameConfig($gameConfig)
            ->setName(GamePlantEnum::BANANA_TREE)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(false)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$bananaTreeType]))
        ;
        $manager->persist($bananaTreeType);
        $manager->persist($bananaTree);

        $apprentonPilotType = new Book();
        $apprentonPilotType
            ->setSkill(SkillEnum::PILOT)
        ;

        $dismantle = new Dismountable();
        $dismantle
            ->setActionCost(4)
            ->setChancesSuccess(6)
            ->setProducts([ItemEnum::METAL_SCRAPS])
        ;

        $apprentonPilot = new Item();
        $apprentonPilot
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::APPRENTON_PILOT)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$apprentonPilotType, $dismantle]))
        ;
        $manager->persist($dismantle);
        $manager->persist($apprentonPilotType);
        $manager->persist($apprentonPilot);

        $SniperHelmet = new Item();
        $SniperHelmet
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::SNIPER_HELMET)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
        ;
        $manager->persist($SniperHelmet);

        $blueprintSniperHelmetType = new Blueprint();
        $blueprintSniperHelmetType
            ->setItem($SniperHelmet)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintSniperHelmet = new Item();
        $blueprintSniperHelmet
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::SNIPER_HELMET_BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$blueprintSniperHelmetType]))
        ;
        $manager->persist($blueprintSniperHelmetType);
        $manager->persist($blueprintSniperHelmet);

        $drugType = new Drug();
        $drugType
            ->setSatiety(0)
            ->setMoralPoints([-2, 0, 1, 2, 3])
            ->setActionPoints([0, 1, 2, 3])
            ->setMovementPoints([0, 2, 4])
            ->setCures([
                DiseaseEnum::VITAMIN_DEFICIENCY,
                DiseaseEnum::SYPHILIS,
                DiseaseEnum::SKIN_INFLAMMATION,
                DiseaseEnum::GASTROENTERIS,
                DiseaseEnum::FLU,
                DiseaseEnum::SEPTIS,
                DiseaseEnum::COLD,
                DiseaseEnum::RUBELLA,
                DiseaseEnum::SINUS_STORM,
                DiseaseEnum::TAPEWORM,
                DisorderEnum::PARANOIA,
                DisorderEnum::DEPRESSION,
                DisorderEnum::CHRONIC_MIGRAINE])
            ->setCuresNumber([1,2,3,4])
        ;

        $bacta = new Item();
        $bacta
            ->setGameConfig($gameConfig)
            ->setName(GameDrugEnum::BACTA)
            ->setIsHeavy(false)
            ->setIsDismantable(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(false)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$drugType]))
        ;
        $manager->persist($drugType);
        $manager->persist($bacta);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
