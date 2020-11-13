<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Enum\GameRationEnum;
use Mush\Status\Enum\DiseaseEnum;
use Mush\Status\Enum\DisorderEnum;
use Mush\Action\Enum\SpecialEffectEnum;

class ItemConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);
        

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
        
        $coockedRationType = new Ration();
        $coockedRationType
            ->setActionPoints([4])
            ->setMovementPoints([0])
            ->setHealthPoints([0])
            ->setMoralPoints([0])
            ->setSatiety(4)
        ;

        $cookedRation = new Item();
        $coockedRation
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::COOCKED_RATION)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$cookedRationType]))
        ;
        $manager->persist($coockedRationType);
        $manager->persist($coockedRation);
        
        $alienSteackType = new Ration();
        $alienSteackType
            ->setActionPoints([4])
            ->setMovementPoints([0])
            ->setHealthPoints([0])
            ->setMoralPoints([-1])
            ->setSatiety(4)
            ->setDiseases([
                DiseaseEnum::ACID_REFLUX => 50,
                DiseaseEnum::TAPEWORM => 25])
            ->setDiseasesNumber([2])
        ;

        $alienSteack = new Item();
        $alienSteack
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ALIEN_STEAK)
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
        
        
        
        $coffeType = new Ration();
        $coffeType
            ->setActionPoints([2])
            ->setMovementPoints([0])
            ->setHealthPoints([0])
            ->setMoralPoints([0])
            ->setSatiety(0)
        ;

        $coffe = new Item();
        $coffe
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::COFFE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$coffeType]))
        ;
        $manager->persist($coffeType);
        $manager->persist($coffeRation);


        $anabolicType = new Ration();
        $anabolicType
            ->setActionPoints([0])
            ->setMovementPoints([8])
            ->setHealthPoints([0])
            ->setMoralPoints([0])
            ->setSatiety(0)
            ->setIsPerishable(false)
        ;

        $anabolic = new Item();
        $anabolic
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ANABOLIC)
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
            ->setActionPoints([8])
            ->setMovementPoints([0])
            ->setHealthPoints([0])
            ->setMoralPoints([2])
            ->setSatiety(8)
            ->setIsPerishable(false)
        ;

        $lombrickBar = new Item();
        $lombrickBar
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::LOMBRICK_BAR)
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
            ->setActionPoints([6])
            ->setMovementPoints([0])
            ->setHealthPoints([0])
            ->setMoralPoints([-4])
            ->setSatiety(16)
        ;

        $organicWaste = new Item();
        $organicWaste
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ORGANIC_WASTE)
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
            ->setActionPoints([10])
            ->setMovementPoints([0])
            ->setHealthPoints([0])
            ->setMoralPoints([0])
            ->setSatiety(5)
            ->setIsPerishable(false)
            ->setExtraEffects([ExtraEffectEnum::BREAK_DOOR => 55])
            ->setExtraEffectsNumber([1])
        ;

        $proactivePuffedRice = new Item();
        $proactivePuffedRice
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::PROACTIVE_PUFFED_RICE)
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
            ->setActionPoints([8])
            ->setMovementPoints([0])
            ->setHealthPoints([0])
            ->setMoralPoints([0])
            ->setSatiety(8)
            ->setIsPerishable(false)
        ;

        $spacePotato = new Item();
        $spacePotato
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::SPACE_POTATO)
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
            ->setActionPoints([8])
            ->setMovementPoints([4])
            ->setHealthPoints([0])
            ->setMoralPoints([0])
            ->setSatiety(6)
            ->setDiseases([DiseaseEnum::SLIGHT_NAUSEA => 55])
            ->setDiseasesNumber([1])
        ;

        $supervitaminBar = new Item();
        $supervitaminBar
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::SUPERVITAMIN_BAR)
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
