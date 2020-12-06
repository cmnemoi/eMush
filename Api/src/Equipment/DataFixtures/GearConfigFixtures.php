<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Dismountable;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class GearConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $apron = new ItemConfig();
        $apron
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::STAINPROOF_APRON)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
        ;
        $manager->persist($apron);

        $dismountableMechanic = new Dismountable();
        $dismountableMechanic
            ->setProducts([ItemEnum::PLASTIC_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(12)
        ;

        $plasteniteArmor = new ItemConfig();
        $plasteniteArmor
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::PLASTENITE_ARMOR)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$dismountableMechanic]))
        ;
        $manager->persist($plasteniteArmor);
        $manager->persist($dismountableMechanic);

        $wrench = new ItemConfig();
        $wrench
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ADJUSTABLE_WRENCH)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($wrench);

        $gloves = new ItemConfig();
        $gloves
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::PROTECTIVE_GLOVES)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)

        ;
        $manager->persist($gloves);

        $soap = new ItemConfig();
        $soap
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SOAP)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)

        ;
        $manager->persist($soap);

        $dismountableMechanic = new Dismountable();
        $dismountableMechanic
            ->setProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(99)
        ;

        $sniperHelmet = new ItemConfig();
        $sniperHelmet
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SNIPER_HELMET)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(99)
            ->setMechanics(new ArrayCollection([$dismountableMechanic]))
        ;
        $manager->persist($sniperHelmet);
        $manager->persist($dismountableMechanic);

        $alienBottleOpener = new ItemConfig();
        $alienBottleOpener
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ALIEN_BOTTLE_OPENER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
        ;
        $manager->persist($alienBottleOpener);

        $dismountableMechanic = new Dismountable();
        $dismountableMechanic
            ->setProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->setActionCost(3)
            ->setChancesSuccess(25)
        ;

        $chargedMechanic = new Charged();
        $chargedMechanic
            ->setMaxCharge(8)
            ->setStartCharge(2)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;

        $antiGravScooter = new ItemConfig();
        $antiGravScooter
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ANTI_GRAV_SCOOTER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(6)
            ->setMechanics(new ArrayCollection([$dismountableMechanic, $chargedMechanic]))
        ;
        $manager->persist($antiGravScooter);
        $manager->persist($dismountableMechanic);
        $manager->persist($chargedMechanic);

        $rollingBoulder = new ItemConfig();
        $rollingBoulder
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ROLLING_BOULDER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
        ;
        $manager->persist($rollingBoulder);

        $lenses = new ItemConfig();
        $lenses
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::NCC_LENS)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
        ;
        $manager->persist($lenses);

        $oscilloscope = new ItemConfig();
        $oscilloscope
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::OSCILLOSCOPE)
            ->setIsHeavy(true)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(99)
        ;
        $manager->persist($oscilloscope);

        $dismountableMechanic
            ->setProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
            ->setActionCost(3)
            ->setChancesSuccess(6)
        ;

        $spacesuit = new ItemConfig();
        $spacesuit
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SPACESUIT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(6)
            ->setMechanics(new ArrayCollection([$dismountableMechanic]))
        ;
        $manager->persist($spacesuit);
        $manager->persist($dismountableMechanic);

        $superSoaper = new ItemConfig();
        $superSoaper
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SUPER_SOAPER)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($superSoaper);

        $printedCircuitJelly = new ItemConfig();
        $printedCircuitJelly
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::PRINTED_CIRCUIT_JELLY)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
        ;
        $manager->persist($printedCircuitJelly);

        $invertebrateShell = new ItemConfig();
        $invertebrateShell
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::INVERTEBRATE_SHELL)
            ->setIsHeavy(true)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
        ;
        $manager->persist($invertebrateShell);

        $liquidMap = new ItemConfig();
        $liquidMap
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::MAGELLAN_LIQUID_MAP)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(1)
            ->setIsAlienArtifact(true)
        ;
        $manager->persist($liquidMap);

        $this->addReference(GearItemEnum::OSCILLOSCOPE, $oscilloscope);
        $this->addReference(GearItemEnum::SNIPER_HELMET, $sniperHelmet);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
