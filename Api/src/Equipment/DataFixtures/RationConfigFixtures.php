<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ExtraEffectEnum;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;

class RationConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var Action $consumeRationAction */
        $consumeRationAction = $this->getReference(ActionsFixtures::RATION_CONSUME);

        $actions = [$takeAction, $dropAction, $hideAction, $examineAction];

        $standardRationMechanic = new Ration();
        $standardRationMechanic
            ->setActionPoints([4 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([-1 => 1])
            ->setSatiety(4)
            ->setIsPerishable(false)
            ->addAction($consumeRationAction)
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::STANDARD_RATION, GameConfigEnum::DEFAULT)
        ;

        $standardRation = new ItemConfig();
        $standardRation
            ->setEquipmentName(GameRationEnum::STANDARD_RATION)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$standardRationMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::COOKED_RATION, GameConfigEnum::DEFAULT)
        ;

        $cookedRation = new ItemConfig();
        $cookedRation
            ->setEquipmentName(GameRationEnum::COOKED_RATION)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$cookedRationMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::ALIEN_STEAK, GameConfigEnum::DEFAULT)
        ;

        $alienSteack = new ItemConfig();
        $alienSteack
            ->setEquipmentName(GameRationEnum::ALIEN_STEAK)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$alienSteackMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::COFFEE, GameConfigEnum::DEFAULT)
        ;

        $coffee = new ItemConfig();
        $coffee
            ->setEquipmentName(GameRationEnum::COFFEE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$coffeeMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::ANABOLIC, GameConfigEnum::DEFAULT)
        ;

        $anabolic = new ItemConfig();
        $anabolic
            ->setEquipmentName(GameRationEnum::ANABOLIC)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$anabolicMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::LOMBRICK_BAR, GameConfigEnum::DEFAULT)
        ;

        $lombrickBar = new ItemConfig();
        $lombrickBar
            ->setEquipmentName(GameRationEnum::LOMBRICK_BAR)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$lombrickBarMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::ORGANIC_WASTE, GameConfigEnum::DEFAULT)
        ;

        $organicWaste = new ItemConfig();
        $organicWaste
            ->setEquipmentName(GameRationEnum::ORGANIC_WASTE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$organicWasteMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::PROACTIVE_PUFFED_RICE, GameConfigEnum::DEFAULT)
        ;

        $proactivePuffedRice = new ItemConfig();
        $proactivePuffedRice
            ->setEquipmentName(GameRationEnum::PROACTIVE_PUFFED_RICE)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$proactivePuffedRiceMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::SPACE_POTATO, GameConfigEnum::DEFAULT)
        ;

        $spacePotato = new ItemConfig();
        $spacePotato
            ->setEquipmentName(GameRationEnum::SPACE_POTATO)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$spacePotatoMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::SUPERVITAMIN_BAR, GameConfigEnum::DEFAULT)
        ;

        $supervitaminBar = new ItemConfig();
        $supervitaminBar
            ->setEquipmentName(GameRationEnum::SUPERVITAMIN_BAR)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$supervitaminBarMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($supervitaminBarMechanic);
        $manager->persist($supervitaminBar);

        $gameConfig
            ->addEquipmentConfig($standardRation)
            ->addEquipmentConfig($cookedRation)
            ->addEquipmentConfig($coffee)
            ->addEquipmentConfig($anabolic)
            ->addEquipmentConfig($alienSteack)
            ->addEquipmentConfig($spacePotato)
            ->addEquipmentConfig($proactivePuffedRice)
            ->addEquipmentConfig($lombrickBar)
            ->addEquipmentConfig($supervitaminBar)
            ->addEquipmentConfig($organicWaste)
        ;
        $manager->persist($gameConfig);

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
