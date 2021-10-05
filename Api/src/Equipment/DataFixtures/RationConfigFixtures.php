<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ExtraEffectEnum;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class RationConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $takeAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $buildAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var Action $consumeRationAction */
        $consumeRationAction = $this->getReference(ActionsFixtures::RATION_CONSUME);

        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        $standardRationMechanic = new Ration();
        $standardRationMechanic
            ->setActionPoints([4 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([-1 => 1])
            ->setSatiety(4)
            ->setIsPerishable(false)
            ->addAction($consumeRationAction)
        ;

        $standardRation = new ItemConfig();
        $standardRation
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::STANDARD_RATION)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$standardRationMechanic]))
            ->setActions($actions)
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
            ->addAction($consumeRationAction)
        ;

        $cookedRation = new ItemConfig();
        $cookedRation
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::COOKED_RATION)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$cookedRationMechanic]))
            ->setActions($actions)
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
            ->addAction($consumeRationAction)
        ;

        $alienSteack = new ItemConfig();
        $alienSteack
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::ALIEN_STEAK)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$alienSteackMechanic]))
            ->setActions($actions)
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
            ->addAction($consumeRationAction)
        ;

        $coffee = new ItemConfig();
        $coffee
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::COFFEE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$coffeeMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($coffeeMechanic);
        $manager->persist($coffee);

        $anabolicMechanic = new Ration();
        $anabolicMechanic
            ->setActionPoints([0 => 1])
            ->setMovementPoints([8 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setIsPerishable(false)
            ->addAction($consumeRationAction)
        ;

        $anabolic = new ItemConfig();
        $anabolic
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::ANABOLIC)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$anabolicMechanic]))
            ->setActions($actions)
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
            ->addAction($consumeRationAction)
        ;

        $lombrickBar = new ItemConfig();
        $lombrickBar
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::LOMBRICK_BAR)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$lombrickBarMechanic]))
            ->setActions($actions)
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
            ->addAction($consumeRationAction)
            ->setActions($actions)
        ;

        $organicWaste = new ItemConfig();
        $organicWaste
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::ORGANIC_WASTE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$organicWasteMechanic]))
            ->setActions($actions)
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
            ->addAction($consumeRationAction)
        ;

        $proactivePuffedRice = new ItemConfig();
        $proactivePuffedRice
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::PROACTIVE_PUFFED_RICE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$proactivePuffedRiceMechanic]))
            ->setActions($actions)
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
            ->addAction($consumeRationAction)
        ;

        $spacePotato = new ItemConfig();
        $spacePotato
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::SPACE_POTATO)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$spacePotatoMechanic]))
            ->setActions($actions)
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
            ->setIsPerishable(false)
            ->addAction($consumeRationAction)
        ;

        $supervitaminBar = new ItemConfig();
        $supervitaminBar
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::SUPERVITAMIN_BAR)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$supervitaminBarMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($supervitaminBarMechanic);
        $manager->persist($supervitaminBar);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            ActionsFixtures::class,
        ];
    }
}
