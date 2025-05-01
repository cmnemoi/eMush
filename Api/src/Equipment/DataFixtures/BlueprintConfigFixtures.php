<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\ConfigData\EquipmentConfigData;
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

        $blueprintEcholocator = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::ECHOLOCATOR . '_' . ItemEnum::BLUEPRINT));
        $blueprintEcholocator
            ->setMechanics([$blueprintEcholocatorMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintEcholocatorMechanic);
        $manager->persist($blueprintEcholocator);

        $blueprintWhiteFlagMechanic = new Blueprint();
        $blueprintWhiteFlagMechanic
            ->setCraftedEquipmentName(ItemEnum::WHITE_FLAG)
            ->setIngredients([GearItemEnum::SOAP => 1, ItemEnum::OLD_T_SHIRT => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::WHITE_FLAG . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintWhiteFlag = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::WHITE_FLAG . '_' . ItemEnum::BLUEPRINT));
        $blueprintWhiteFlag
            ->setMechanics([$blueprintWhiteFlagMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintWhiteFlagMechanic);
        $manager->persist($blueprintWhiteFlag);

        $blueprintThermosensorMechanic = new Blueprint();
        $blueprintThermosensorMechanic
            ->setCraftedEquipmentName(ItemEnum::THERMOSENSOR)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::THERMOSENSOR . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintThermosensor = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::THERMOSENSOR . '_' . ItemEnum::BLUEPRINT));
        $blueprintThermosensor
            ->setMechanics([$blueprintThermosensorMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintThermosensorMechanic);
        $manager->persist($blueprintThermosensor);

        $blueprintBabelModuleMechanic = new Blueprint();
        $blueprintBabelModuleMechanic
            ->setCraftedEquipmentName(ItemEnum::BABEL_MODULE)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::BABEL_MODULE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintBabelModule = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::BABEL_MODULE . '_' . ItemEnum::BLUEPRINT));
        $blueprintBabelModule
            ->setMechanics([$blueprintBabelModuleMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintBabelModuleMechanic);
        $manager->persist($blueprintBabelModule);

        $blueprintGrenadeMechanic = new Blueprint();
        $blueprintGrenadeMechanic
            ->setCraftedEquipmentName(ItemEnum::GRENADE)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::FUEL_CAPSULE => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::GRENADE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintGrenade = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::GRENADE . '_' . ItemEnum::BLUEPRINT));
        $blueprintGrenade
            ->setMechanics([$blueprintGrenadeMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintGrenadeMechanic);
        $manager->persist($blueprintGrenade);

        $blueprintOldFaithfulMechanic = new Blueprint();
        $blueprintOldFaithfulMechanic
            ->setCraftedEquipmentName(ItemEnum::OLD_FAITHFUL)
            ->setIngredients([ItemEnum::METAL_SCRAPS => 4])
            ->addAction($buildAction)
            ->buildName(ItemEnum::OLD_FAITHFUL . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintOldFaithful = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::OLD_FAITHFUL . '_' . ItemEnum::BLUEPRINT));
        $blueprintOldFaithful
            ->setMechanics([$blueprintOldFaithfulMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintOldFaithfulMechanic);
        $manager->persist($blueprintOldFaithful);

        $blueprintLizaroJungleMechanic = new Blueprint();
        $blueprintLizaroJungleMechanic
            ->setCraftedEquipmentName(ItemEnum::LIZARO_JUNGLE)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->addAction($buildAction)
            ->buildName(ItemEnum::LIZARO_JUNGLE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintLizaroJungle = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::LIZARO_JUNGLE . '_' . ItemEnum::BLUEPRINT));
        $blueprintLizaroJungle
            ->setMechanics([$blueprintLizaroJungleMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintLizaroJungleMechanic);
        $manager->persist($blueprintLizaroJungle);

        $blueprintRocketLauncherMechanic = new Blueprint();
        $blueprintRocketLauncherMechanic
            ->setCraftedEquipmentName(ItemEnum::ROCKET_LAUNCHER)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->addAction($buildAction)
            ->buildName(ItemEnum::ROCKET_LAUNCHER . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintRocketLauncher = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::ROCKET_LAUNCHER . '_' . ItemEnum::BLUEPRINT));
        $blueprintRocketLauncher
            ->setMechanics([$blueprintRocketLauncherMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintRocketLauncherMechanic);
        $manager->persist($blueprintRocketLauncher);

        $blueprintExtinguisherMechanic = new Blueprint();
        $blueprintExtinguisherMechanic
            ->setCraftedEquipmentName(ToolItemEnum::EXTINGUISHER)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(ToolItemEnum::EXTINGUISHER . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintExtinguisher = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ToolItemEnum::EXTINGUISHER . '_' . ItemEnum::BLUEPRINT));
        $blueprintExtinguisher
            ->setMechanics([$blueprintExtinguisherMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintExtinguisherMechanic);
        $manager->persist($blueprintExtinguisher);

        $blueprintOscilloscopeMechanic = new Blueprint();
        $blueprintOscilloscopeMechanic
            ->setCraftedEquipmentName(GearItemEnum::OSCILLOSCOPE)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(GearItemEnum::OSCILLOSCOPE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintOscilloscope = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::OSCILLOSCOPE . '_' . ItemEnum::BLUEPRINT));
        $blueprintOscilloscope
            ->setMechanics([$blueprintOscilloscopeMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintOscilloscopeMechanic);
        $manager->persist($blueprintOscilloscope);

        $blueprintSniperHelmetMechanic = new Blueprint();
        $blueprintSniperHelmetMechanic
            ->setCraftedEquipmentName(GearItemEnum::SNIPER_HELMET)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(GearItemEnum::SNIPER_HELMET . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintSniperHelmet = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(GearItemEnum::SNIPER_HELMET . '_' . ItemEnum::BLUEPRINT));
        $blueprintSniperHelmet
            ->setMechanics([$blueprintSniperHelmetMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintSniperHelmetMechanic);
        $manager->persist($blueprintSniperHelmet);

        $blueprintSwedishSofaMechanic = new Blueprint();
        $blueprintSwedishSofaMechanic
            ->setCraftedEquipmentName(EquipmentEnum::SWEDISH_SOFA)
            ->setIngredients([ItemEnum::THICK_TUBE => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName(EquipmentEnum::SWEDISH_SOFA . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintSwedishSofa = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::SWEDISH_SOFA . '_' . ItemEnum::BLUEPRINT));
        $blueprintSwedishSofa
            ->setMechanics([$blueprintSwedishSofaMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintSwedishSofaMechanic);
        $manager->persist($blueprintSwedishSofa);

        $blueprintJukeboxMechanic = new Blueprint();
        $blueprintJukeboxMechanic
            ->setCraftedEquipmentName(EquipmentEnum::JUKEBOX)
            ->setIngredients([ItemEnum::THICK_TUBE => 1, ItemEnum::METAL_SCRAPS => 1])
            ->addAction($buildAction)
            ->buildName('jukebox_blueprint', GameConfigEnum::DEFAULT);

        $blueprintJukebox = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName('jukebox_blueprint'));
        $blueprintJukebox
            ->setMechanics([$blueprintJukeboxMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintJukeboxMechanic);
        $manager->persist($blueprintJukebox);

        $blueprintSupportDroneMechanic = new Blueprint();
        $blueprintSupportDroneMechanic
            ->setCraftedEquipmentName(ItemEnum::SUPPORT_DRONE)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 4])
            ->addAction($buildAction)
            ->buildName(ItemEnum::SUPPORT_DRONE . '_' . ItemEnum::BLUEPRINT, GameConfigEnum::DEFAULT);

        $blueprintSupportDrone = ItemConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(ItemEnum::SUPPORT_DRONE . '_' . ItemEnum::BLUEPRINT));
        $blueprintSupportDrone
            ->setMechanics([$blueprintSupportDroneMechanic])
            ->setActionConfigs($actions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($blueprintSupportDroneMechanic);
        $manager->persist($blueprintSupportDrone);

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
            ->addEquipmentConfig($blueprintSwedishSofa)
            ->addEquipmentConfig($blueprintJukebox)
            ->addEquipmentConfig($blueprintSupportDrone);
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
