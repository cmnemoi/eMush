<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ExtraEffectEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Enum\GameRationEnum;
use Mush\Status\Enum\DiseaseEnum;

class RationConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $standardRationType = new Ration();
        $standardRationType
            ->setActionPoints([4 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([-1 => 1])
            ->setSatiety(4)
        ;

        $standardRation = new Item();
        $standardRation
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::STANDARD_RATION)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$standardRationType]))
        ;
        $manager->persist($standardRationType);
        $manager->persist($standardRation);

        $cookedRationType = new Ration();
        $cookedRationType
            ->setActionPoints([4 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(4)
        ;

        $cookedRation = new Item();
        $cookedRation
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::COOKED_RATION)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$cookedRationType]))
        ;
        $manager->persist($cookedRationType);
        $manager->persist($cookedRation);

        $alienSteackType = new Ration();
        $alienSteackType
            ->setActionPoints([4 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([-1 => 1])
            ->setSatiety(4)
            ->setDiseasesChances([
                DiseaseEnum::ACID_REFLUX => 50,
                DiseaseEnum::TAPEWORM => 25, ])
            ->setDiseasesDelayMin([
                DiseaseEnum::ACID_REFLUX => 4,
                DiseaseEnum::TAPEWORM => 4, ])
             ->setDiseasesDelayLengh([
                DiseaseEnum::ACID_REFLUX => 4,
                DiseaseEnum::TAPEWORM => 4, ])

        ;

        $alienSteack = new Item();
        $alienSteack
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::ALIEN_STEAK)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$alienSteackType]))
        ;
        $manager->persist($alienSteackType);
        $manager->persist($alienSteack);

        $coffeeType = new Ration();
        $coffeeType
            ->setActionPoints([2 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(0)
        ;

        $coffee = new Item();
        $coffee
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::COFFEE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$coffeeType]))
        ;
        $manager->persist($coffeeType);
        $manager->persist($coffee);

        $anabolicType = new Ration();
        $anabolicType
            ->setActionPoints([0 => 1])
            ->setMovementPoints([8 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(0)
            ->setIsPerishable(false)
        ;

        $anabolic = new Item();
        $anabolic
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::ANABOLIC)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$anabolicType]))
        ;
        $manager->persist($anabolicType);
        $manager->persist($anabolic);

        $lombrickBarType = new Ration();
        $lombrickBarType
            ->setActionPoints([8 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([2 => 1])
            ->setSatiety(8)
            ->setIsPerishable(false)
        ;

        $lombrickBar = new Item();
        $lombrickBar
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::LOMBRICK_BAR)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$lombrickBarType]))
        ;
        $manager->persist($lombrickBarType);
        $manager->persist($lombrickBar);

        $organicWasteType = new Ration();
        $organicWasteType
            ->setActionPoints([6 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([-4 => 1])
            ->setSatiety(16)
        ;

        $organicWaste = new Item();
        $organicWaste
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::ORGANIC_WASTE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$organicWasteType]))
        ;
        $manager->persist($organicWasteType);
        $manager->persist($organicWaste);

        $proactivePuffedRiceType = new Ration();
        $proactivePuffedRiceType
            ->setActionPoints([10 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(5)
            ->setIsPerishable(false)
            ->setExtraEffects([ExtraEffectEnum::BREAK_DOOR => 55])
        ;

        $proactivePuffedRice = new Item();
        $proactivePuffedRice
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::PROACTIVE_PUFFED_RICE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$proactivePuffedRiceType]))
        ;
        $manager->persist($proactivePuffedRiceType);
        $manager->persist($proactivePuffedRice);

        $spacePotatoType = new Ration();
        $spacePotatoType
            ->setActionPoints([8 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(8)
            ->setIsPerishable(false)
        ;

        $spacePotato = new Item();
        $spacePotato
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::SPACE_POTATO)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$spacePotatoType]))
        ;
        $manager->persist($spacePotatoType);
        $manager->persist($spacePotato);

        $supervitaminBarType = new Ration();
        $supervitaminBarType
            ->setActionPoints([8 => 1])
            ->setMovementPoints([4 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(6)
            ->setDiseasesChances([DiseaseEnum::SLIGHT_NAUSEA => 55])
            ->setDiseasesDelayMin([DiseaseEnum::SLIGHT_NAUSEA => 0])
            ->setDiseasesDelayLengh([DiseaseEnum::SLIGHT_NAUSEA => 0])
        ;

        $supervitaminBar = new Item();
        $supervitaminBar
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::SUPERVITAMIN_BAR)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$supervitaminBarType]))
        ;
        $manager->persist($supervitaminBarType);
        $manager->persist($supervitaminBar);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
