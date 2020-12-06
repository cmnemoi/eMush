<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;

class BlueprintConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $echolocator = $this->getReference(ItemEnum::ECHOLOCATOR);
        $whiteFlag = $this->getReference(ItemEnum::WHITE_FLAG);
        $thermosensor = $this->getReference(ItemEnum::THERMOSENSOR);
        $babelModule = $this->getReference(ItemEnum::BABEL_MODULE);
        $grenade = $this->getReference(ItemEnum::GRENADE);
        $oldFaithful = $this->getReference(ItemEnum::OLD_FAITHFUL);
        $lizaroJungle = $this->getReference(ItemEnum::LIZARO_JUNGLE);
        $rocketLauncher = $this->getReference(ItemEnum::ROCKET_LAUNCHER);
        $extinguisher = $this->getReference(ToolItemEnum::EXTINGUISHER);
        $oscilloscope = $this->getReference(GearItemEnum::OSCILLOSCOPE);
        $sniperHelmet = $this->getReference(GearItemEnum::SNIPER_HELMET);

        //@TODO add support_drone and swedish_sofa

        $blueprintEcholocatorType = new Blueprint();
        $blueprintEcholocatorType
            ->setEquipment($echolocator)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintEcholocator = new ItemConfig();
        $blueprintEcholocator
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ECHOLOCATOR . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintEcholocatorType]))
        ;
        $manager->persist($blueprintEcholocatorType);
        $manager->persist($blueprintEcholocator);

        $blueprintWhiteFlagType = new Blueprint();
        $blueprintWhiteFlagType
            ->setEquipment($whiteFlag)
            ->setIngredients([GearItemEnum::SOAP => 1, ItemEnum::OLD_T_SHIRT => 1])
        ;

        $blueprintWhiteFlag = new ItemConfig();
        $blueprintWhiteFlag
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::WHITE_FLAG . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintWhiteFlagType]))
        ;
        $manager->persist($blueprintWhiteFlagType);
        $manager->persist($blueprintWhiteFlag);

        $blueprintThermosensorType = new Blueprint();
        $blueprintThermosensorType
            ->setEquipment($thermosensor)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintThermosensor = new ItemConfig();
        $blueprintThermosensor
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::THERMOSENSOR . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintThermosensorType]))
        ;
        $manager->persist($blueprintThermosensorType);
        $manager->persist($blueprintThermosensor);

        $blueprintBabelModuleType = new Blueprint();
        $blueprintBabelModuleType
            ->setEquipment($babelModule)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintBabelModule = new ItemConfig();
        $blueprintBabelModule
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::BABEL_MODULE . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintBabelModuleType]))
        ;
        $manager->persist($blueprintBabelModuleType);
        $manager->persist($blueprintBabelModule);

        $blueprintGrenadeType = new Blueprint();
        $blueprintGrenadeType
            ->setEquipment($grenade)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::FUEL_CAPSULE => 1])
        ;

        $blueprintGrenade = new ItemConfig();
        $blueprintGrenade
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::GRENADE . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintGrenadeType]))
        ;
        $manager->persist($blueprintGrenadeType);
        $manager->persist($blueprintGrenade);

        $blueprintOldFaithfulType = new Blueprint();
        $blueprintOldFaithfulType
            ->setEquipment($oldFaithful)
            ->setIngredients([ItemEnum::METAL_SCRAPS => 4])
        ;

        $blueprintOldFaithful = new ItemConfig();
        $blueprintOldFaithful
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::OLD_FAITHFUL . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintOldFaithfulType]))
        ;
        $manager->persist($blueprintOldFaithfulType);
        $manager->persist($blueprintOldFaithful);

        $blueprintLizaroJungleType = new Blueprint();
        $blueprintLizaroJungleType
            ->setEquipment($lizaroJungle)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
        ;

        $blueprintLizaroJungle = new ItemConfig();
        $blueprintLizaroJungle
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::LIZARO_JUNGLE . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintLizaroJungleType]))
        ;
        $manager->persist($blueprintLizaroJungleType);
        $manager->persist($blueprintLizaroJungle);

        $blueprintRocketLauncherType = new Blueprint();
        $blueprintRocketLauncherType
            ->setEquipment($rocketLauncher)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
        ;

        $blueprintRocketLauncher = new ItemConfig();
        $blueprintRocketLauncher
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::ROCKET_LAUNCHER . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintRocketLauncherType]))
        ;
        $manager->persist($blueprintRocketLauncherType);
        $manager->persist($blueprintRocketLauncher);

        $blueprintExtinguisherType = new Blueprint();
        $blueprintExtinguisherType
            ->setEquipment($extinguisher)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintExtinguisher = new ItemConfig();
        $blueprintExtinguisher
            ->setGameConfig($gameConfig)
            ->setName(ToolItemEnum::EXTINGUISHER . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintExtinguisherType]))
        ;
        $manager->persist($blueprintExtinguisherType);
        $manager->persist($blueprintExtinguisher);

        $blueprintOscilloscopeType = new Blueprint();
        $blueprintOscilloscopeType
            ->setEquipment($oscilloscope)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintOscilloscope = new ItemConfig();
        $blueprintOscilloscope
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::OSCILLOSCOPE . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintOscilloscopeType]))
        ;
        $manager->persist($blueprintOscilloscopeType);
        $manager->persist($blueprintOscilloscope);

        $blueprintSniperHelmetType = new Blueprint();
        $blueprintSniperHelmetType
            ->setEquipment($sniperHelmet)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintSniperHelmet = new ItemConfig();
        $blueprintSniperHelmet
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SNIPER_HELMET . '_' . ItemEnum::BLUEPRINT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$blueprintSniperHelmetType]))
        ;
        $manager->persist($blueprintSniperHelmetType);
        $manager->persist($blueprintSniperHelmet);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ItemConfigFixtures::class,
            ExplorationConfigFixtures::class,
            ToolConfigFixtures::class,
            GearConfigFixtures::class,
            WeaponConfigFixtures::class,
        ];
    }
}
