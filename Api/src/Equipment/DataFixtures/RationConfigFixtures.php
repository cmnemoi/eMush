<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ExtraEffectEnum;
use Mush\Equipment\ConfigData\EquipmentConfigData;
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

        /** @var ActionConfig $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);

        /** @var ActionConfig $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        /** @var ActionConfig $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var ActionConfig $consumeRationAction */
        $consumeRationAction = $this->getReference(ActionsFixtures::RATION_CONSUME);

        /** @var ActionConfig $mixRationSporeAction */
        $mixRationSporeAction = $this->getReference(ActionEnum::MIX_RATION_SPORE->value);

        $actions = [$takeAction, $dropAction, $hideAction, $examineAction, $consumeRationAction, $mixRationSporeAction];

        $standardRationMechanic = new Ration();
        $standardRationMechanic
            ->setActionPoints([4 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([-1 => 1])
            ->setSatiety(4)
            ->setIsPerishable(false)
            ->addAction($consumeRationAction)
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::STANDARD_RATION, GameConfigEnum::DEFAULT);

        $standardRation = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameRationEnum::STANDARD_RATION));
        $standardRation
            ->setMechanics([$standardRationMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::COOKED_RATION, GameConfigEnum::DEFAULT);

        $cookedRation = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameRationEnum::COOKED_RATION));
        $cookedRation
            ->setMechanics([$cookedRationMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::ALIEN_STEAK, GameConfigEnum::DEFAULT);

        $alienSteak = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameRationEnum::ALIEN_STEAK));
        $alienSteak
            ->setMechanics([$alienSteackMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($alienSteackMechanic);
        $manager->persist($alienSteak);

        $coffeeMechanic = new Ration();
        $coffeeMechanic
            ->setActionPoints([2 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([0 => 1])
            ->setMoralPoints([0 => 1])
            ->setSatiety(0)
            ->addAction($consumeRationAction)
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::COFFEE, GameConfigEnum::DEFAULT);

        $coffee = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameRationEnum::COFFEE));
        $coffee
            ->setMechanics([$coffeeMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::ANABOLIC, GameConfigEnum::DEFAULT);

        $anabolic = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameRationEnum::ANABOLIC));
        $anabolic
            ->setMechanics([$anabolicMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::LOMBRICK_BAR, GameConfigEnum::DEFAULT);

        $lombrickBar = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameRationEnum::LOMBRICK_BAR));
        $lombrickBar
            ->setMechanics([$lombrickBarMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::ORGANIC_WASTE, GameConfigEnum::DEFAULT);

        $organicWaste = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameRationEnum::ORGANIC_WASTE));
        $organicWaste
            ->setMechanics([$organicWasteMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::PROACTIVE_PUFFED_RICE, GameConfigEnum::DEFAULT);

        $proactivePuffedRice = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameRationEnum::PROACTIVE_PUFFED_RICE));
        $proactivePuffedRice
            ->setMechanics([$proactivePuffedRiceMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::SPACE_POTATO, GameConfigEnum::DEFAULT);

        $spacePotato = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameRationEnum::SPACE_POTATO));
        $spacePotato
            ->setMechanics([$spacePotatoMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->buildName(EquipmentMechanicEnum::RATION . '_' . GameRationEnum::SUPERVITAMIN_BAR, GameConfigEnum::DEFAULT);

        $supervitaminBar = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GameRationEnum::SUPERVITAMIN_BAR));
        $supervitaminBar
            ->setMechanics([$supervitaminBarMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($supervitaminBarMechanic);
        $manager->persist($supervitaminBar);

        $gameConfig
            ->addEquipmentConfig($standardRation)
            ->addEquipmentConfig($cookedRation)
            ->addEquipmentConfig($coffee)
            ->addEquipmentConfig($anabolic)
            ->addEquipmentConfig($alienSteak)
            ->addEquipmentConfig($spacePotato)
            ->addEquipmentConfig($proactivePuffedRice)
            ->addEquipmentConfig($lombrickBar)
            ->addEquipmentConfig($supervitaminBar)
            ->addEquipmentConfig($organicWaste);
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
