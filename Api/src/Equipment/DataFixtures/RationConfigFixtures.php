<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ExtraEffectEnum;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Enum\DiseaseEnum;

class RationConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $standardRationMechanic = new Ration();
        $standardRationMechanic
            ->setActionPoints([4 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([-1 => 1])
            ->setSatiety(4)
            ->setIsPerishable(false)
        ;

        $standardRation = new ItemConfig();
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
            ->setMechanics(new ArrayCollection([$standardRationMechanic]))
        ;
        $manager->persist($standardRationMechanic);
        $manager->persist($standardRation);

        $cookedRationMechanic = new Ration();
        $cookedRationMechanic
            ->setActionPoints([4 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(4)
        ;

        $cookedRation = new ItemConfig();
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
            ->setMechanics(new ArrayCollection([$cookedRationMechanic]))
        ;
        $manager->persist($cookedRationMechanic);
        $manager->persist($cookedRation);

        $alienSteackMechanic = new Ration();
        $alienSteackMechanic
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
             ->setDiseasesDelayLength([
                DiseaseEnum::ACID_REFLUX => 4,
                DiseaseEnum::TAPEWORM => 4, ])

        ;

        $alienSteack = new ItemConfig();
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
            ->setMechanics(new ArrayCollection([$alienSteackMechanic]))
        ;
        $manager->persist($alienSteackMechanic);
        $manager->persist($alienSteack);

        $coffeeMechanic = new Ration();
        $coffeeMechanic
            ->setActionPoints([2 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(0)
        ;

        $coffee = new ItemConfig();
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
            ->setMechanics(new ArrayCollection([$coffeeMechanic]))
        ;
        $manager->persist($coffeeMechanic);
        $manager->persist($coffee);

        $anabolicMechanic = new Ration();
        $anabolicMechanic
            ->setActionPoints([0 => 1])
            ->setMovementPoints([8 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(0)
            ->setIsPerishable(false)
        ;

        $anabolic = new ItemConfig();
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
            ->setMechanics(new ArrayCollection([$anabolicMechanic]))
        ;
        $manager->persist($anabolicMechanic);
        $manager->persist($anabolic);

        $lombrickBarMechanic = new Ration();
        $lombrickBarMechanic
            ->setActionPoints([8 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([2 => 1])
            ->setSatiety(8)
            ->setIsPerishable(false)
        ;

        $lombrickBar = new ItemConfig();
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
            ->setMechanics(new ArrayCollection([$lombrickBarMechanic]))
        ;
        $manager->persist($lombrickBarMechanic);
        $manager->persist($lombrickBar);

        $organicWasteMechanic = new Ration();
        $organicWasteMechanic
            ->setActionPoints([6 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([-4 => 1])
            ->setSatiety(16)
            ->setIsPerishable(false)
        ;

        $organicWaste = new ItemConfig();
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
            ->setMechanics(new ArrayCollection([$organicWasteMechanic]))
        ;
        $manager->persist($organicWasteMechanic);
        $manager->persist($organicWaste);

        $proactivePuffedRiceMechanic = new Ration();
        $proactivePuffedRiceMechanic
            ->setActionPoints([10 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(5)
            ->setIsPerishable(false)
            ->setExtraEffects([ExtraEffectEnum::BREAK_DOOR => 55])
        ;

        $proactivePuffedRice = new ItemConfig();
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
            ->setMechanics(new ArrayCollection([$proactivePuffedRiceMechanic]))
        ;
        $manager->persist($proactivePuffedRiceMechanic);
        $manager->persist($proactivePuffedRice);

        $spacePotatoMechanic = new Ration();
        $spacePotatoMechanic
            ->setActionPoints([8 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(8)
            ->setIsPerishable(false)
        ;

        $spacePotato = new ItemConfig();
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
            ->setMechanics(new ArrayCollection([$spacePotatoMechanic]))
        ;
        $manager->persist($spacePotatoMechanic);
        $manager->persist($spacePotato);

        $supervitaminBarMechanic = new Ration();
        $supervitaminBarMechanic
            ->setActionPoints([8 => 1])
            ->setMovementPoints([4 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(6)
            ->setDiseasesChances([DiseaseEnum::SLIGHT_NAUSEA => 55])
            ->setDiseasesDelayMin([DiseaseEnum::SLIGHT_NAUSEA => 0])
            ->setDiseasesDelayLength([DiseaseEnum::SLIGHT_NAUSEA => 0])
            ->setIsPerishable(false)
        ;

        $supervitaminBar = new ItemConfig();
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
            ->setMechanics(new ArrayCollection([$supervitaminBarMechanic]))
        ;
        $manager->persist($supervitaminBarMechanic);
        $manager->persist($supervitaminBar);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
