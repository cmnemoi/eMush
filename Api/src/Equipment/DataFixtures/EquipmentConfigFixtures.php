<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class EquipmentConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $repair = $this->getReference(TechnicianFixtures::REPAIR);

        //@TODO terminals
        $icarus = new EquipmentConfig();
        $icarus
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::ICARUS)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($icarus);

        $moveAction = $this->getReference(ActionsFixtures::MOVE_DEFAULT);

        //@TODO terminals
        $door = new EquipmentConfig();
        $door
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::DOOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$moveAction, $repair]))
        ;
        $manager->persist($door);

        $comsCenter = new EquipmentConfig();
        $comsCenter
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COMMUNICATION_CENTER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($comsCenter);

        $neronCore = new EquipmentConfig();
        $neronCore
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::NERON_CORE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($neronCore);

        $astroTerminal = new EquipmentConfig();
        $astroTerminal
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($astroTerminal);

        $researchLab = new EquipmentConfig();
        $researchLab
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::RESEARCH_LABORATORY)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($researchLab);

        $pilgred = new EquipmentConfig();
        $pilgred
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::PILGRED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($pilgred);

        $calculator = new EquipmentConfig();
        $calculator
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::CALCULATOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($calculator);

        $biosTerminal = new EquipmentConfig();
        $biosTerminal
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::BIOS_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(3)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($biosTerminal);

        $commandTerminal = new EquipmentConfig();
        $commandTerminal
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($commandTerminal);

        //@TODO gears
        $planetScanner = new EquipmentConfig();
        $planetScanner
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::PLANET_SCANNER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($planetScanner);

        $jukebox = new EquipmentConfig();
        $jukebox
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::JUKEBOX)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($jukebox);

        $emergencyReactor = new EquipmentConfig();
        $emergencyReactor
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::EMERGENCY_REACTOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($emergencyReactor);

        $reactorLateral = new EquipmentConfig();
        $reactorLateral
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::REACTOR_LATERAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($reactorLateral);

        $antenna = new EquipmentConfig();
        $antenna
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::ANTENNA)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($antenna);

        $gravitySimulator = new EquipmentConfig();
        $gravitySimulator
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($gravitySimulator);

        /** @var Action $showerAction */
        $showerAction = $this->getReference(ActionsFixtures::SHOWER_DEFAULT);

        $showerMechanic = new Tool();
        $showerMechanic->addAction($showerAction);

        $thalasso = new EquipmentConfig();
        $thalasso
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::THALASSO)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$showerMechanic]))
            ->setActions(new ArrayCollection([$this->getReference(TechnicianFixtures::DISMANTLE_3_25)]))
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
        ;
        $manager->persist($thalasso);
        $manager->persist($showerMechanic);

        //@TODO ships
        $patrolShipChargeMechanic = new Charged();
        $patrolShipChargeMechanic
            ->setMaxCharge(10)
            ->setStartCharge(10)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;
        $patrolShip = new EquipmentConfig();
        $patrolShip
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::PATROL_SHIP)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$patrolShipChargeMechanic]))
        ;
        $manager->persist($patrolShip);
        $manager->persist($patrolShipChargeMechanic);

        $pasiphae = new EquipmentConfig();
        $pasiphae
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::PASIPHAE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($pasiphae);

        /** @var Action $fuelInjectAction */
        $fuelInjectAction = $this->getReference(ActionsFixtures::FUEL_INJECT);
        /** @var Action $fuelRetrieveAction */
        $fuelRetrieveAction = $this->getReference(ActionsFixtures::FUEL_RETRIEVE);

        //Tools
        $combustionChamberMechanic = new Tool();
        $combustionChamberMechanic->addAction($fuelInjectAction);
        $combustionChamberMechanic->addAction($fuelRetrieveAction);
        $chargedMechanic = new Charged();
        $chargedMechanic
            ->setMaxCharge(9)
            ->setStartCharge(0)
            ->setChargeStrategy(ChargeStrategyTypeEnum::NONE)
            ->setIsVisible(false)
        ;
        $combustionChamber = new EquipmentConfig();
        $combustionChamber
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COMBUSTION_CHAMBER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$combustionChamberMechanic, $chargedMechanic]))
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($combustionChamber);
        $manager->persist($combustionChamberMechanic);
        $manager->persist($chargedMechanic);

        /** @var Action $cookAction */
        $cookAction = $this->getReference(ActionsFixtures::COOK_DEFAULT);

        $kitchenMechanic = new Tool();
        $kitchenMechanic->addAction($cookAction);

        $kitchen = new EquipmentConfig();
        $kitchen
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::KITCHEN)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$kitchenMechanic]))
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($kitchen);
        $manager->persist($kitchenMechanic);

        $dailyChargeMechanic = new Charged();
        $dailyChargeMechanic
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
            ->setIsVisible(false)
        ;
        $distillerMechanic = new Tool();

        $narcoticDistiller = new EquipmentConfig();
        $narcoticDistiller
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::NARCOTIC_DISTILLER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$distillerMechanic, $dailyChargeMechanic]))
            ->setActions(new ArrayCollection([$this->getReference(TechnicianFixtures::DISMANTLE_3_25)]))
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
        ;

        $manager->persist($narcoticDistiller);
        $manager->persist($distillerMechanic);
        $manager->persist($dailyChargeMechanic);

        $shower = new EquipmentConfig();
        $shower
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::SHOWER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$showerMechanic]))
            ->setActions(new ArrayCollection([$this->getReference(TechnicianFixtures::DISMANTLE_3_25)]))
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
        ;
        $manager->persist($shower);

        $dynarcadeMechanic = new Tool();
        $dynarcade = new EquipmentConfig();
        $dynarcade
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::DYNARCADE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$dynarcadeMechanic]))
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($dynarcade);
        $manager->persist($dynarcadeMechanic);

        /** @var Action $lieDownAction */
        $lieDownAction = $this->getReference(ActionsFixtures::LIE_DOWN);

        $bedMechanic = new Tool();
        $bedMechanic->addAction($lieDownAction);
        $bed = new EquipmentConfig();
        $bed
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::BED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$bedMechanic]))
        ;
        $manager->persist($bed);
        $manager->persist($bedMechanic);

        /** @var Action $coffeeAction */
        $coffeeAction = $this->getReference(ActionsFixtures::COFFEE_DEFAULT);

        $coffeMachineMechanic = new Tool();
        $coffeMachineMechanic->addAction($coffeeAction);

        $coffeMachine = new EquipmentConfig();
        $coffeMachine
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COFFEE_MACHINE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$coffeMachineMechanic, $dailyChargeMechanic]))
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($coffeMachine);
        $manager->persist($coffeMachineMechanic);

        $cryoModuleMechanic = new Tool();
//        $cryoModuleMechanic->setActions([ActionEnum::CHECK_ROSTER]);
        $cryoModule = new EquipmentConfig();
        $cryoModule
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::CRYO_MODULE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$cryoModuleMechanic]))
        ;
        $manager->persist($cryoModule);
        $manager->persist($cryoModuleMechanic);

        $mycoscanMechanic = new Tool();
//        $mycoscanMechanic->setActions([ActionEnum::CHECK_INFECTION]);
        $mycoscan = new EquipmentConfig();
        $mycoscan
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::MYCOSCAN)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$mycoscanMechanic]))
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($mycoscan);
        $manager->persist($mycoscanMechanic);

        $turretChargeMechanic = new Charged();
        $turretChargeMechanic
            ->setMaxCharge(4)
            ->setStartCharge(4)
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setIsVisible(true)
        ;
        $turretCommandMechanic = new Tool();
//        $turretCommandMechanic->setActions([ActionEnum::SHOOT_HUNTER]);
        $turretCommand = new EquipmentConfig();
        $turretCommand
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::TURRET_COMMAND)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$turretCommandMechanic, $turretChargeMechanic]))
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($turretCommand);
        $manager->persist($turretCommandMechanic);
        $manager->persist($turretChargeMechanic);

        $surgicalPlotMechanic = new Tool();
//        $surgicalPlotMechanic->setGrantActions([ActionEnum::SELF_SURGERY, ActionEnum::SURGERY]);
//        $surgicalPlotMechanic->setActionsTarget([
//            ActionEnum::SELF_SURGERY => ActionTargetEnum::SELF_PLAYER,
//            ActionEnum::SURGERY => ActionTargetEnum::TARGET_PLAYER,
//        ]);
        $surgicalPlot = new EquipmentConfig();
        $surgicalPlot
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::SURGICAL_PLOT)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$surgicalPlotMechanic]))
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($surgicalPlot);
        $manager->persist($surgicalPlotMechanic);

        $fuelTankMechanic = new Tool();

        $fuelTankMechanic->addAction($fuelInjectAction);
        $fuelTankMechanic->addAction($fuelRetrieveAction);

        $fuelTank = new EquipmentConfig();
        $fuelTank
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::FUEL_TANK)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$fuelTankMechanic]))
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($fuelTank);
        $manager->persist($fuelTankMechanic);

        $oxygenTankMechanic = new Tool();

        /** @var Action $oxygenInjectAction */
        $oxygenInjectAction = $this->getReference(ActionsFixtures::OXYGEN_INJECT);
        /** @var Action $oxygenRetrieveAction */
        $oxygenRetrieveAction = $this->getReference(ActionsFixtures::OXYGEN_RETRIEVE);

        $oxygenTankMechanic->addAction($oxygenInjectAction);
        $oxygenTankMechanic->addAction($oxygenRetrieveAction);

        $oxygenTank = new EquipmentConfig();
        $oxygenTank
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$oxygenTankMechanic]))
            ->setActions(new ArrayCollection([$repair]))
        ;
        $manager->persist($oxygenTank);
        $manager->persist($oxygenTankMechanic);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            ActionsFixtures::class,
            TechnicianFixtures::class,
        ];
    }
}
