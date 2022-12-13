<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;

class BlueprintConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);
        /** @var Action $buildAction */
        $buildAction = $this->getReference(ActionsFixtures::BUILD_DEFAULT);
        /** @var Action $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);
        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = new ArrayCollection([$takeAction, $dropAction, $hideAction, $examineAction]);

        /** @var ItemConfig $echolocator */
        $echolocator = $this->getReference(ItemEnum::ECHOLOCATOR);
        /** @var ItemConfig $whiteFlag */
        $whiteFlag = $this->getReference(ItemEnum::WHITE_FLAG);
        /** @var ItemConfig $thermosensor */
        $thermosensor = $this->getReference(ItemEnum::THERMOSENSOR);
        /** @var ItemConfig $babelModule */
        $babelModule = $this->getReference(ItemEnum::BABEL_MODULE);
        /** @var ItemConfig $grenade */
        $grenade = $this->getReference(ItemEnum::GRENADE);
        /** @var ItemConfig $oldFaithful */
        $oldFaithful = $this->getReference(ItemEnum::OLD_FAITHFUL);
        /** @var ItemConfig $lizaroJungle */
        $lizaroJungle = $this->getReference(ItemEnum::LIZARO_JUNGLE);
        /** @var ItemConfig $rocketLauncher */
        $rocketLauncher = $this->getReference(ItemEnum::ROCKET_LAUNCHER);
        /** @var ItemConfig $extinguisher */
        $extinguisher = $this->getReference(ToolItemEnum::EXTINGUISHER);
        /** @var ItemConfig $oscilloscope */
        $oscilloscope = $this->getReference(GearItemEnum::OSCILLOSCOPE);
        /** @var ItemConfig $sniperHelmet */
        $sniperHelmet = $this->getReference(GearItemEnum::SNIPER_HELMET);

        // @TODO add support_drone and swedish_sofa

        $blueprintEcholocatorMechanic = new Blueprint();
        $blueprintEcholocatorMechanic
            ->setEquipment($echolocator)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::ECHOLOCATOR . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintEcholocator = new ItemConfig();
        $blueprintEcholocator
            ->setEquipmentName(ItemEnum::ECHOLOCATOR . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintEcholocatorMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintEcholocatorMechanic);
        $manager->persist($blueprintEcholocator);

        $blueprintWhiteFlagMechanic = new Blueprint();
        $blueprintWhiteFlagMechanic
            ->setEquipment($whiteFlag)
            ->setIngredients([GearItemEnum::SOAP => 1, ItemEnum::OLD_T_SHIRT => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::WHITE_FLAG . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintWhiteFlag = new ItemConfig();
        $blueprintWhiteFlag
            ->setEquipmentName(ItemEnum::WHITE_FLAG . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintWhiteFlagMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintWhiteFlagMechanic);
        $manager->persist($blueprintWhiteFlag);

        $blueprintThermosensorMechanic = new Blueprint();
        $blueprintThermosensorMechanic
            ->setEquipment($thermosensor)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::THERMOSENSOR . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintThermosensor = new ItemConfig();
        $blueprintThermosensor
            ->setEquipmentName(ItemEnum::THERMOSENSOR . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintThermosensorMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintThermosensorMechanic);
        $manager->persist($blueprintThermosensor);

        $blueprintBabelModuleMechanic = new Blueprint();
        $blueprintBabelModuleMechanic
            ->setEquipment($babelModule)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::BABEL_MODULE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintBabelModule = new ItemConfig();
        $blueprintBabelModule
            ->setEquipmentName(ItemEnum::BABEL_MODULE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintBabelModuleMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintBabelModuleMechanic);
        $manager->persist($blueprintBabelModule);

        $blueprintGrenadeMechanic = new Blueprint();
        $blueprintGrenadeMechanic
            ->setEquipment($grenade)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::FUEL_CAPSULE => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::GRENADE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintGrenade = new ItemConfig();
        $blueprintGrenade
            ->setEquipmentName(ItemEnum::GRENADE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintGrenadeMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintGrenadeMechanic);
        $manager->persist($blueprintGrenade);

        $blueprintOldFaithfulMechanic = new Blueprint();
        $blueprintOldFaithfulMechanic
            ->setEquipment($oldFaithful)
            ->setIngredients([ItemEnum::METAL_SCRAPS => 4])
            ->addAction($buildAction)
            ->buildName(ItemEnum::OLD_FAITHFUL . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintOldFaithful = new ItemConfig();
        $blueprintOldFaithful
            ->setEquipmentName(ItemEnum::OLD_FAITHFUL . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintOldFaithfulMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintOldFaithfulMechanic);
        $manager->persist($blueprintOldFaithful);

        $blueprintLizaroJungleMechanic = new Blueprint();
        $blueprintLizaroJungleMechanic
            ->setEquipment($lizaroJungle)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->addAction($buildAction)
            ->buildName(ItemEnum::LIZARO_JUNGLE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintLizaroJungle = new ItemConfig();
        $blueprintLizaroJungle
            ->setEquipmentName(ItemEnum::LIZARO_JUNGLE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintLizaroJungleMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintLizaroJungleMechanic);
        $manager->persist($blueprintLizaroJungle);

        $blueprintRocketLauncherMechanic = new Blueprint();
        $blueprintRocketLauncherMechanic
            ->setEquipment($rocketLauncher)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::ROCKET_LAUNCHER . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintRocketLauncher = new ItemConfig();
        $blueprintRocketLauncher
            ->setEquipmentName(ItemEnum::ROCKET_LAUNCHER . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintRocketLauncherMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintRocketLauncherMechanic);
        $manager->persist($blueprintRocketLauncher);

        $blueprintExtinguisherMechanic = new Blueprint();
        $blueprintExtinguisherMechanic
            ->setEquipment($extinguisher)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ToolItemEnum::EXTINGUISHER . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintExtinguisher = new ItemConfig();
        $blueprintExtinguisher
            ->setEquipmentName(ToolItemEnum::EXTINGUISHER . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintExtinguisherMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintExtinguisherMechanic);
        $manager->persist($blueprintExtinguisher);

        $blueprintOscilloscopeMechanic = new Blueprint();
        $blueprintOscilloscopeMechanic
            ->setEquipment($oscilloscope)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(GearItemEnum::OSCILLOSCOPE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintOscilloscope = new ItemConfig();
        $blueprintOscilloscope
            ->setEquipmentName(GearItemEnum::OSCILLOSCOPE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintOscilloscopeMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintOscilloscopeMechanic);
        $manager->persist($blueprintOscilloscope);

        $blueprintSniperHelmetMechanic = new Blueprint();
        $blueprintSniperHelmetMechanic
            ->setEquipment($sniperHelmet)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(GearItemEnum::SNIPER_HELMET . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT)
        ;

        $blueprintSniperHelmet = new ItemConfig();
        $blueprintSniperHelmet
            ->setEquipmentName(GearItemEnum::SNIPER_HELMET . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintSniperHelmetMechanic]))
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($blueprintSniperHelmetMechanic);
        $manager->persist($blueprintSniperHelmet);

        $gameConfig
            ->addEquipmentConfig($blueprintEcholocator)
            ->addEquipmentConfig($blueprintWhiteFlag)
            ->addEquipmentConfig($blueprintBabelModule)
            ->addEquipmentConfig($blueprintThermosensor)
            ->addEquipmentConfig($blueprintGrenade)
            ->addEquipmentConfig($blueprintOldFaithful)
            ->addEquipmentConfig($blueprintLizaroJungle)
            ->addEquipmentConfig($blueprintRocketLauncher)
            ->addEquipmentConfig($blueprintExtinguisher)
            ->addEquipmentConfig($blueprintOscilloscope)
            ->addEquipmentConfig($blueprintSniperHelmet)
        ;
        $manager->persist($gameConfig);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            ItemConfigFixtures::class,
            ExplorationConfigFixtures::class,
            ToolConfigFixtures::class,
            GearConfigFixtures::class,
            WeaponConfigFixtures::class,
        ];
    }
}
