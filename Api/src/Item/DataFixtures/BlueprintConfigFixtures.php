<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Blueprint;
use Mush\Item\Enum\GearItemEnum;
use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\ToolItemEnum;

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
            ->setItem($echolocator)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintEcholocator = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintEcholocatorType]))
        ;
        $manager->persist($blueprintEcholocatorType);
        $manager->persist($blueprintEcholocator);

        $blueprintWhiteFlagType = new Blueprint();
        $blueprintWhiteFlagType
            ->setItem($whiteFlag)
            ->setIngredients([GearItemEnum::SOAP => 1, ItemEnum::OLD_T_SHIRT => 1])
        ;

        $blueprintWhiteFlag = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintWhiteFlagType]))
        ;
        $manager->persist($blueprintWhiteFlagType);
        $manager->persist($blueprintWhiteFlag);

        $blueprintThermosensorType = new Blueprint();
        $blueprintThermosensorType
            ->setItem($thermosensor)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintThermosensor = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintThermosensorType]))
        ;
        $manager->persist($blueprintThermosensorType);
        $manager->persist($blueprintThermosensor);

        $blueprintBabelModuleType = new Blueprint();
        $blueprintBabelModuleType
            ->setItem($babelModule)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintBabelModule = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintBabelModuleType]))
        ;
        $manager->persist($blueprintBabelModuleType);
        $manager->persist($blueprintBabelModule);

        $blueprintGrenadeType = new Blueprint();
        $blueprintGrenadeType
            ->setItem($grenade)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::FUEL_CAPSULE => 1])
        ;

        $blueprintGrenade = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintGrenadeType]))
        ;
        $manager->persist($blueprintGrenadeType);
        $manager->persist($blueprintGrenade);

        $blueprintOldFaithfulType = new Blueprint();
        $blueprintOldFaithfulType
            ->setItem($oldFaithful)
            ->setIngredients([ItemEnum::METAL_SCRAPS => 4])
        ;

        $blueprintOldFaithful = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintOldFaithfulType]))
        ;
        $manager->persist($blueprintOldFaithfulType);
        $manager->persist($blueprintOldFaithful);

        $blueprintLizaroJungleType = new Blueprint();
        $blueprintLizaroJungleType
            ->setItem($lizaroJungle)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
        ;

        $blueprintLizaroJungle = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintLizaroJungleType]))
        ;
        $manager->persist($blueprintLizaroJungleType);
        $manager->persist($blueprintLizaroJungle);

        $blueprintRocketLauncherType = new Blueprint();
        $blueprintRocketLauncherType
            ->setItem($rocketLauncher)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
        ;

        $blueprintRocketLauncher = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintRocketLauncherType]))
        ;
        $manager->persist($blueprintRocketLauncherType);
        $manager->persist($blueprintRocketLauncher);

        $blueprintExtinguisherType = new Blueprint();
        $blueprintExtinguisherType
            ->setItem($extinguisher)
            ->setIngredients([ItemEnum::OXYGEN_CAPSULE => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintExtinguisher = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintExtinguisherType]))
        ;
        $manager->persist($blueprintExtinguisherType);
        $manager->persist($blueprintExtinguisher);

        $blueprintOscilloscopeType = new Blueprint();
        $blueprintOscilloscopeType
            ->setItem($oscilloscope)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintOscilloscope = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintOscilloscopeType]))
        ;
        $manager->persist($blueprintOscilloscopeType);
        $manager->persist($blueprintOscilloscope);

        $blueprintSniperHelmetType = new Blueprint();
        $blueprintSniperHelmetType
            ->setItem($sniperHelmet)
            ->setIngredients([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;

        $blueprintSniperHelmet = new Item();
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
            ->setTypes(new ArrayCollection([$blueprintSniperHelmetType]))
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
