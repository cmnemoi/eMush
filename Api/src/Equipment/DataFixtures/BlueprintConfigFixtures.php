<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Enum\EquipmentEnum;
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

        /** @var ActionConfig $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);

        /** @var ActionConfig $dropAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        /** @var ActionConfig $buildAction */
        $buildAction = $this->getReference(ActionsFixtures::BUILD_DEFAULT);

        /** @var ActionConfig $hideAction */
        $hideAction = $this->getReference(ActionsFixtures::HIDE_DEFAULT);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        $actions = [$takeAction, $dropAction, $hideAction, $examineAction];

        /** @TODO add support_drone and swedish_sofa */
        $blueprintEcholocatorMechanic = new Blueprint();
        $blueprintEcholocatorMechanic
            ->setCraftedEquipmentName(ItemEnum::ECHOLOCATOR)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::ECHOLOCATOR . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintEcholocator = new ItemConfig();
        $blueprintEcholocator
            ->setEquipmentName(ItemEnum::ECHOLOCATOR . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintEcholocatorMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintEcholocatorMechanic);
        $manager->persist($blueprintEcholocator);

        $blueprintWhiteFlagMechanic = new Blueprint();
        $blueprintWhiteFlagMechanic
            ->setCraftedEquipmentName(ItemEnum::WHITE_FLAG)
            ->setIngredients([GearItemEnum::SOAP => 1, ItemEnum::OLD_T_SHIRT => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::WHITE_FLAG . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintWhiteFlag = new ItemConfig();
        $blueprintWhiteFlag
            ->setEquipmentName(ItemEnum::WHITE_FLAG . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintWhiteFlagMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintWhiteFlagMechanic);
        $manager->persist($blueprintWhiteFlag);

        $blueprintThermosensorMechanic = new Blueprint();
        $blueprintThermosensorMechanic
            ->setCraftedEquipmentName(ItemEnum::THERMOSENSOR)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::THERMOSENSOR . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintThermosensor = new ItemConfig();
        $blueprintThermosensor
            ->setEquipmentName(ItemEnum::THERMOSENSOR . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintThermosensorMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintThermosensorMechanic);
        $manager->persist($blueprintThermosensor);

        $blueprintBabelModuleMechanic = new Blueprint();
        $blueprintBabelModuleMechanic
            ->setCraftedEquipmentName(ItemEnum::BABEL_MODULE)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::BABEL_MODULE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintBabelModule = new ItemConfig();
        $blueprintBabelModule
            ->setEquipmentName(ItemEnum::BABEL_MODULE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintBabelModuleMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintBabelModuleMechanic);
        $manager->persist($blueprintBabelModule);

        $blueprintGrenadeMechanic = new Blueprint();
        $blueprintGrenadeMechanic
            ->setCraftedEquipmentName(ItemEnum::GRENADE)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::FUEL_CAPSULE => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::GRENADE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintGrenade = new ItemConfig();
        $blueprintGrenade
            ->setEquipmentName(ItemEnum::GRENADE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintGrenadeMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintGrenadeMechanic);
        $manager->persist($blueprintGrenade);

        $blueprintOldFaithfulMechanic = new Blueprint();
        $blueprintOldFaithfulMechanic
            ->setCraftedEquipmentName(ItemEnum::OLD_FAITHFUL)
            ->setIngredients([ItemEnum::METAL_SCRAPS => 4])
            ->addAction($buildAction)
            ->buildName(ItemEnum::OLD_FAITHFUL . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintOldFaithful = new ItemConfig();
        $blueprintOldFaithful
            ->setEquipmentName(ItemEnum::OLD_FAITHFUL . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintOldFaithfulMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintOldFaithfulMechanic);
        $manager->persist($blueprintOldFaithful);

        $blueprintLizaroJungleMechanic = new Blueprint();
        $blueprintLizaroJungleMechanic
            ->setCraftedEquipmentName(ItemEnum::LIZARO_JUNGLE)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->addAction($buildAction)
            ->buildName(ItemEnum::LIZARO_JUNGLE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintLizaroJungle = new ItemConfig();
        $blueprintLizaroJungle
            ->setEquipmentName(ItemEnum::LIZARO_JUNGLE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintLizaroJungleMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintLizaroJungleMechanic);
        $manager->persist($blueprintLizaroJungle);

        $blueprintRocketLauncherMechanic = new Blueprint();
        $blueprintRocketLauncherMechanic
            ->setCraftedEquipmentName(ItemEnum::ROCKET_LAUNCHER)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::ROCKET_LAUNCHER . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintRocketLauncher = new ItemConfig();
        $blueprintRocketLauncher
            ->setEquipmentName(ItemEnum::ROCKET_LAUNCHER . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintRocketLauncherMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintRocketLauncherMechanic);
        $manager->persist($blueprintRocketLauncher);

        $blueprintExtinguisherMechanic = new Blueprint();
        $blueprintExtinguisherMechanic
            ->setCraftedEquipmentName(ToolItemEnum::EXTINGUISHER)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ToolItemEnum::EXTINGUISHER . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintExtinguisher = new ItemConfig();
        $blueprintExtinguisher
            ->setEquipmentName(ToolItemEnum::EXTINGUISHER . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintExtinguisherMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintExtinguisherMechanic);
        $manager->persist($blueprintExtinguisher);

        $blueprintOscilloscopeMechanic = new Blueprint();
        $blueprintOscilloscopeMechanic
            ->setCraftedEquipmentName(GearItemEnum::OSCILLOSCOPE)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(GearItemEnum::OSCILLOSCOPE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintOscilloscope = new ItemConfig();
        $blueprintOscilloscope
            ->setEquipmentName(GearItemEnum::OSCILLOSCOPE . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintOscilloscopeMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintOscilloscopeMechanic);
        $manager->persist($blueprintOscilloscope);

        $blueprintSniperHelmetMechanic = new Blueprint();
        $blueprintSniperHelmetMechanic
            ->setCraftedEquipmentName(GearItemEnum::SNIPER_HELMET)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(GearItemEnum::SNIPER_HELMET . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintSniperHelmet = new ItemConfig();
        $blueprintSniperHelmet
            ->setEquipmentName(GearItemEnum::SNIPER_HELMET . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintSniperHelmetMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintSniperHelmetMechanic);
        $manager->persist($blueprintSniperHelmet);

        $blueprintSwedishSofaMechanic = new Blueprint();
        $blueprintSwedishSofaMechanic
            ->setCraftedEquipmentName(EquipmentEnum::SWEDISH_SOFA)
            ->setIngredients([ItemEnum::THICK_TUBE => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(EquipmentEnum::SWEDISH_SOFA . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintSwedishSofa = new ItemConfig();
        $blueprintSwedishSofa
            ->setEquipmentName(EquipmentEnum::SWEDISH_SOFA . '_' . ItemEnum::BLUEPRINT)
            ->setIsStackable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics([$blueprintSwedishSofaMechanic])
            ->setActions($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintSwedishSofaMechanic);
        $manager->persist($blueprintSwedishSofa);

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
            ->addEquipmentConfig($blueprintSwedishSofa);
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
