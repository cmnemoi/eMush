<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class BlueprintConfigFixtures extends Fixture implements DependentFixtureInterface
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
        $buildAction = $this->getReference(ActionsFixtures::BUILD_DEFAULT);
        /** @var Action $buildAction */
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

        //@TODO add support_drone and swedish_sofa

        $blueprintEcholocatorMechanic = new Blueprint();
        $blueprintEcholocatorMechanic
            ->setEquipment($echolocator)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
        ;

        $blueprintEcholocator = new ItemConfig();
        $blueprintEcholocator
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ECHOLOCATOR . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintEcholocatorMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintEcholocatorMechanic);
        $manager->persist($blueprintEcholocator);

        $blueprintWhiteFlagMechanic = new Blueprint();
        $blueprintWhiteFlagMechanic
            ->setEquipment($whiteFlag)
            ->setIngredients([GearItemEnum::SOAP => 1, ItemEnum::OLD_T_SHIRT => 1])
            ->addAction($buildAction)
        ;

        $blueprintWhiteFlag = new ItemConfig();
        $blueprintWhiteFlag
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::WHITE_FLAG . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintWhiteFlagMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintWhiteFlagMechanic);
        $manager->persist($blueprintWhiteFlag);

        $blueprintThermosensorMechanic = new Blueprint();
        $blueprintThermosensorMechanic
            ->setEquipment($thermosensor)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
        ;

        $blueprintThermosensor = new ItemConfig();
        $blueprintThermosensor
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::THERMOSENSOR . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintThermosensorMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintThermosensorMechanic);
        $manager->persist($blueprintThermosensor);

        $blueprintBabelModuleMechanic = new Blueprint();
        $blueprintBabelModuleMechanic
            ->setEquipment($babelModule)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
        ;

        $blueprintBabelModule = new ItemConfig();
        $blueprintBabelModule
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BABEL_MODULE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintBabelModuleMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintBabelModuleMechanic);
        $manager->persist($blueprintBabelModule);

        $blueprintGrenadeMechanic = new Blueprint();
        $blueprintGrenadeMechanic
            ->setEquipment($grenade)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::FUEL_CAPSULE => 1])
            ->addAction($buildAction)
        ;

        $blueprintGrenade = new ItemConfig();
        $blueprintGrenade
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::GRENADE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintGrenadeMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintGrenadeMechanic);
        $manager->persist($blueprintGrenade);

        $blueprintOldFaithfulMechanic = new Blueprint();
        $blueprintOldFaithfulMechanic
            ->setEquipment($oldFaithful)
            ->setIngredients([ItemEnum::METAL_SCRAPS => 4])
            ->addAction($buildAction)
        ;

        $blueprintOldFaithful = new ItemConfig();
        $blueprintOldFaithful
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::OLD_FAITHFUL . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintOldFaithfulMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintOldFaithfulMechanic);
        $manager->persist($blueprintOldFaithful);

        $blueprintLizaroJungleMechanic = new Blueprint();
        $blueprintLizaroJungleMechanic
            ->setEquipment($lizaroJungle)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->addAction($buildAction)
        ;

        $blueprintLizaroJungle = new ItemConfig();
        $blueprintLizaroJungle
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::LIZARO_JUNGLE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintLizaroJungleMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintLizaroJungleMechanic);
        $manager->persist($blueprintLizaroJungle);

        $blueprintRocketLauncherMechanic = new Blueprint();
        $blueprintRocketLauncherMechanic
            ->setEquipment($rocketLauncher)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->addAction($buildAction)
        ;

        $blueprintRocketLauncher = new ItemConfig();
        $blueprintRocketLauncher
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROCKET_LAUNCHER . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintRocketLauncherMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintRocketLauncherMechanic);
        $manager->persist($blueprintRocketLauncher);

        $blueprintExtinguisherMechanic = new Blueprint();
        $blueprintExtinguisherMechanic
            ->setEquipment($extinguisher)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
        ;

        $blueprintExtinguisher = new ItemConfig();
        $blueprintExtinguisher
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::EXTINGUISHER . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintExtinguisherMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintExtinguisherMechanic);
        $manager->persist($blueprintExtinguisher);

        $blueprintOscilloscopeMechanic = new Blueprint();
        $blueprintOscilloscopeMechanic
            ->setEquipment($oscilloscope)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
        ;

        $blueprintOscilloscope = new ItemConfig();
        $blueprintOscilloscope
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::OSCILLOSCOPE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintOscilloscopeMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintOscilloscopeMechanic);
        $manager->persist($blueprintOscilloscope);

        $blueprintSniperHelmetMechanic = new Blueprint();
        $blueprintSniperHelmetMechanic
            ->setEquipment($sniperHelmet)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
        ;

        $blueprintSniperHelmet = new ItemConfig();
        $blueprintSniperHelmet
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SNIPER_HELMET . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintSniperHelmetMechanic]))
            ->setActions($actions)
        ;
        $manager->persist($blueprintSniperHelmetMechanic);
        $manager->persist($blueprintSniperHelmet);

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
