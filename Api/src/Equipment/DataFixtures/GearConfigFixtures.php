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

        $repair1 = $this->getReference(TechnicianFixtures::REPAIR_1);
        $repair6 = $this->getReference(TechnicianFixtures::REPAIR_6);
        $repair12 = $this->getReference(TechnicianFixtures::REPAIR_12);
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        $sabotage1 = $this->getReference(TechnicianFixtures::SABOTAGE_1);
        $sabotage6 = $this->getReference(TechnicianFixtures::SABOTAGE_6);
        $sabotage12 = $this->getReference(TechnicianFixtures::SABOTAGE_12);
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);

        $apronGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            -100,
            ModifierScopeEnum::EVENT_DIRTY,
            ReachEnum::INVENTORY,
            true
        );

        $actions25 = clone $actions;
        $actions25->add($repair25);
        $actions25->add($sabotage25);

        $apron = new ItemConfig();
        $apron
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::STAINPROOF_APRON)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setActions($actions25)
            ->setMechanics(new ArrayCollection([$apronGear]))
        ;
        $manager->persist($apron);

        $plasteniteActions = clone $actions;
        $plasteniteActions->add($dismantle12);
        $plasteniteActions->add($repair12);
        $plasteniteActions->add($sabotage12);

        $plasteniteGear = $this->createGear(
            ModifierTargetEnum::HEALTH_POINT,
            -1,
            ModifierScopeEnum::ACTION_ATTACK,
            ReachEnum::INVENTORY,
            true
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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$plasteniteGear]))
            ->setActions($plasteniteActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
        ;
        $manager->persist($plasteniteArmor);

        $wrenchGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            1.5,
            ModifierScopeEnum::ACTION_TECHNICIAN,
            ReachEnum::INVENTORY,
            false
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
            ReachEnum::INVENTORY,
            true
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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$glovesGear]))
            ->setActions($actions25)
        ;
        $manager->persist($gloves);

        $soapGear = $this->createGear(
            ModifierTargetEnum::ACTION_POINT,
            -1,
            ActionEnum::SHOWER,
            ReachEnum::INVENTORY,
            true
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
        $sniperHelmetActions->add($dismantle12);
        $sniperHelmetActions->add($repair1); //@FIXME with the right %
        $sniperHelmetActions->add($sabotage1);

        $sniperHelmetGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            1.1,
            ModifierScopeEnum::ACTION_SHOOT,
            ReachEnum::INVENTORY,
            true
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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$sniperHelmetGear]))
            ->setActions($sniperHelmetActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 1])
        ;
        $manager->persist($sniperHelmet);

        $alienBottleOpenerGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            1.5,
            ModifierScopeEnum::ACTION_TECHNICIAN,
            ReachEnum::INVENTORY,
            false
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
        $antiGravScooterActions->add($repair6);
        $antiGravScooterActions->add($sabotage6);

        $antiGravScooterGear = $this->createGear(
            ModifierTargetEnum::MOVEMENT_POINT,
            2,
            ModifierScopeEnum::EVENT_ACTION_POINT_CONVERSION,
            ReachEnum::INVENTORY,
            true
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
            ->setIsBreakable(true)
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

        $actions12 = clone $actions;
        $actions12->add($repair12);
        $actions12->add($sabotage12);

        $lensesGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            1.1,
            ModifierScopeEnum::ACTION_SHOOT,
            ReachEnum::INVENTORY,
            false
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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$lensesGear]))
            ->setActions($actions12)
        ;
        $manager->persist($lenses);

        $oscilloscopeGear = $this->createGear(
            ModifierTargetEnum::PERCENTAGE,
            1.5,
            ActionEnum::REINFORCE,
            ReachEnum::INVENTORY,
            false
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
            ->setMechanics(new ArrayCollection([$oscilloscopeGear]))
            ->setActions($actions) //@FIXME add repair and sabotage with right %
        ;
        $manager->persist($oscilloscope);

        $spacesuitActions = clone $actions;
        $spacesuitActions->add($dismantle12);
        $spacesuitActions->add($repair6);
        $spacesuitActions->add($sabotage6);

        $spacesuit = new ItemConfig();
        $spacesuit
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::SPACESUIT)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
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

        $actionsLiquidMap = clone $actions;
        $actionsLiquidMap->add($repair1);
        $actionsLiquidMap->add($sabotage1);

        $liquidMap = new ItemConfig();
        $liquidMap
            ->setGameConfig($gameConfig)
            ->setName(GearItemEnum::MAGELLAN_LIQUID_MAP)
            ->setIsHeavy(false)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsAlienArtifact(true)
            ->setIsBreakable(true)
            ->setActions($actionsLiquidMap)
        ;
        $manager->persist($liquidMap);

        $this->addReference(GearItemEnum::OSCILLOSCOPE, $oscilloscope);
        $this->addReference(GearItemEnum::SNIPER_HELMET, $sniperHelmet);

        $manager->flush();
    }

    private function createGear(string $target, float $delta, string $scope, string $reach, bool $isAdditive): Gear
    {
        $modifier = new Modifier();
        $modifier
            ->setTarget($target)
            ->setDelta($delta)
            ->setScope($scope)
            ->setReach($reach)
            ->setIsAdditive($isAdditive)
        ;

        $this->objectManager->persist($modifier);

        $gear = new Gear();
        $gear->setModifier(new ArrayCollection([$modifier]));

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
