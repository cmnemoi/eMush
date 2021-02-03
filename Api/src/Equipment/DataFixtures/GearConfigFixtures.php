<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class GearConfigFixtures extends Fixture implements DependentFixtureInterface
{
    private ObjectManager $objectManager;

    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;

        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $takeAction */
        $takeAction = $this->getReference(ActionsFixtures::DEFAULT_TAKE);
        /** @var Action $takeAction */
        $dropAction = $this->getReference(ActionsFixtures::DEFAULT_DROP);

        $actions = new ArrayCollection([$takeAction, $dropAction]);

        $apronGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            -100,
            ModifierScopeEnum::EVENT_DIRTY,
            ReachEnum::INVENTORY
        );

        $apron = new ItemConfig();
        $apron
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::STAINPROOF_APRON)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setActions($actions)
            ->setMechanics(new ArrayCollection([$apronGear]))
        ;
        $manager->persist($apron);

        $plasteniteGearActions = clone $actions;
        $plasteniteGearActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_12));

        $plasteniteGear = $this->createGear(
            ModifierTargetEnum::HEALTH_POINT,
            -1,
            ModifierScopeEnum::ACTION_ATTACK,
            ReachEnum::INVENTORY
        );

        $plasteniteArmor = new ItemConfig();
        $plasteniteArmor
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::PLASTENITE_ARMOR)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$plasteniteGear]))
            ->setActions($plasteniteGearActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
        ;
        $manager->persist($plasteniteArmor);

        $wrenchGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            1.5,
            ModifierScopeEnum::ACTION_TECHNICIAN,
            ReachEnum::INVENTORY
        );
        $wrench = new ItemConfig();
        $wrench
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ADJUSTABLE_WRENCH)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$wrenchGear]))
            ->setActions($actions)
        ;
        $manager->persist($wrench);

        $glovesGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            -100,
            ModifierScopeEnum::EVENT_CLUMSINESS,
            ReachEnum::INVENTORY
        );

        $gloves = new ItemConfig();
        $gloves
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::PROTECTIVE_GLOVES)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$glovesGear]))
            ->setActions($actions)
        ;
        $manager->persist($gloves);

        $soapGear = $this->createGear(
            ModifierTargetEnum::ACTION_POINT,
            -1,
            ActionEnum::SHOWER,
            ReachEnum::INVENTORY
        );

        $soap = new ItemConfig();
        $soap
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SOAP)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$soapGear]))
            ->setActions($actions)
        ;
        $manager->persist($soap);

        $sniperHelmetActions = clone $actions;
        $sniperHelmetActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_12));

        $sniperHelmetGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            10,
            ModifierScopeEnum::ACTION_SHOOT,
            ReachEnum::INVENTORY
        );

        $sniperHelmet = new ItemConfig();
        $sniperHelmet
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SNIPER_HELMET)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(99)
            ->setMechanics(new ArrayCollection([$sniperHelmetGear]))
            ->setActions($sniperHelmetActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;
        $manager->persist($sniperHelmet);

        $alienBottleOpenerGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            1.5,
            ModifierScopeEnum::ACTION_TECHNICIAN,
            ReachEnum::INVENTORY
        );

        $alienBottleOpener = new ItemConfig();
        $alienBottleOpener
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ALIEN_BOTTLE_OPENER)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
            ->setMechanics(new ArrayCollection([$alienBottleOpenerGear]))
            ->setActions($actions)
        ;
        $manager->persist($alienBottleOpener);

        $antiGravScooterActions = clone $actions;
        $antiGravScooterActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_25));

        $antiGravScooterGear = $this->createGear(
            ModifierTargetEnum::MOVEMENT_POINT,
            2,
            ModifierScopeEnum::EVENT_ACTION_POINT_CONVERSION,
            ReachEnum::INVENTORY
        );

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
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(6)
            ->setMechanics(new ArrayCollection([$chargedMechanic, $antiGravScooterGear]))
            ->setActions($antiGravScooterActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
        ;
        $manager->persist($antiGravScooter);
        $manager->persist($chargedMechanic);

        $rollingBoulder = new ItemConfig();
        $rollingBoulder
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::ROLLING_BOULDER)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
            ->setActions($actions)
        ;
        $manager->persist($rollingBoulder);

        $lensesGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            10,
            ModifierScopeEnum::ACTION_SHOOT,
            ReachEnum::INVENTORY
        );
        $lenses = new ItemConfig();
        $lenses
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::NCC_LENS)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$lensesGear]))
            ->setActions($actions)
        ;
        $manager->persist($lenses);

        $oscilloscopeGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            1.5,
            ActionEnum::REINFORCE,
            ReachEnum::INVENTORY
        );

        $oscilloscope = new ItemConfig();
        $oscilloscope
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::OSCILLOSCOPE)
            ->setIsHeavy(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(99)
            ->setMechanics(new ArrayCollection([$oscilloscopeGear]))
            ->setActions($actions)
        ;
        $manager->persist($oscilloscope);

        $spacesuitActions = clone $actions;
        $spacesuitActions->add($this->getReference(TechnicianFixtures::DISMANTLE_3_12));

        $spacesuit = new ItemConfig();
        $spacesuit
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SPACESUIT)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(6)
            ->setActions($spacesuitActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;
        $manager->persist($spacesuit);

        $superSoaper = new ItemConfig();
        $superSoaper
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SUPER_SOAPER)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions($actions)
        ;
        $manager->persist($superSoaper);

        $printedCircuitJelly = new ItemConfig();
        $printedCircuitJelly
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::PRINTED_CIRCUIT_JELLY)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
            ->setActions($actions)
        ;
        $manager->persist($printedCircuitJelly);

        $invertebrateShell = new ItemConfig();
        $invertebrateShell
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::INVERTEBRATE_SHELL)
            ->setIsHeavy(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setIsAlienArtifact(true)
            ->setActions($actions)
        ;
        $manager->persist($invertebrateShell);

        $liquidMap = new ItemConfig();
        $liquidMap
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::MAGELLAN_LIQUID_MAP)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setBreakableRate(1)
            ->setIsAlienArtifact(true)
            ->setActions($actions)
        ;
        $manager->persist($liquidMap);

        $this->addReference(GearItemEnum::OSCILLOSCOPE, $oscilloscope);
        $this->addReference(GearItemEnum::SNIPER_HELMET, $sniperHelmet);

        $manager->flush();
    }

    private function createGear(string $target, float $delta, string $scope, string $reach): Gear
    {
        $modifier = new Modifier();
        $modifier
            ->setTarget($target)
            ->setDelta($delta)
            ->setScope($scope)
            ->setReach($reach)
        ;

        $this->objectManager->persist($modifier);

        $gear = new Gear();
        $gear->setModifier($modifier);

        $this->objectManager->persist($gear);

        return $gear;
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
            TechnicianFixtures::class,
            GameConfigFixtures::class,
        ];
    }
}
