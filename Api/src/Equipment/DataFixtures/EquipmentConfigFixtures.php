<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Dismountable;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class EquipmentConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        //@TODO terminals
        $icarus = new EquipmentConfig();
        $icarus
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::ICARUS)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
        ;
        $manager->persist($icarus);


        $comsCenter = new EquipmentConfig();
        $comsCenter
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COMMUNICATION_CENTER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
        ;
        $manager->persist($comsCenter);


        $neronCore = new EquipmentConfig();
        $neronCore
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::NERON_CORE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
        ;
        $manager->persist($neronCore);

        $astroTerminal = new EquipmentConfig();
        $astroTerminal
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
        ;
        $manager->persist($astroTerminal);

        $researchLab = new EquipmentConfig();
        $researchLab
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::RESEARCH_LABORATORY)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
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
        ;
        $manager->persist($calculator);

        $biosTerminal = new EquipmentConfig();
        $biosTerminal
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::BIOS_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(3)
        ;
        $manager->persist($biosTerminal);

        $commandTerminal = new EquipmentConfig();
        $commandTerminal
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
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
        ;
        $manager->persist($planetScanner);

        $jukebox = new EquipmentConfig();
        $jukebox
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::JUKEBOX)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
        ;
        $manager->persist($jukebox);

        $emergencyReactor = new EquipmentConfig();
        $emergencyReactor
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::EMERGENCY_REACTOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
        ;
        $manager->persist($emergencyReactor);

        $reactorLateral = new EquipmentConfig();
        $reactorLateral
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::REACTOR_LATERAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
        ;
        $manager->persist($reactorLateral);

        $antenna = new EquipmentConfig();
        $antenna
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::ANTENNA)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
        ;
        $manager->persist($antenna);

        $gravitySimulator = new EquipmentConfig();
        $gravitySimulator
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(6)
        ;
        $manager->persist($gravitySimulator);

        $showerMechanic = new Tool();
        $showerMechanic->setActions([ActionEnum::SHOWER]);
        $showerDismountableMechanic = new Dismountable();
        $showerDismountableMechanic
            ->setProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->setActionCost(3)
            ->setChancesSuccess(25)
        ;
        $thalasso = new EquipmentConfig();
        $thalasso
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::THALASSO)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$showerMechanic, $showerDismountableMechanic]))
        ;
        $manager->persist($thalasso);
        $manager->persist($showerMechanic);
        $manager->persist($showerDismountableMechanic);



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


        //Tools
        $combustionChamberMechanic = new Tool();
        $combustionChamberMechanic->setGrantActions([ActionEnum::INJECT_FUEL_CHAMBER, ActionEnum::RETRIEVE_FUEL_CHAMBER]);
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
        ;
        $manager->persist($combustionChamber);
        $manager->persist($combustionChamberMechanic);
        $manager->persist($chargedMechanic);


        $kitchenMechanic = new Tool();
        $kitchenMechanic->setGrantActions([ActionEnum::COOK]);
        $kitchen = new EquipmentConfig();
        $kitchen
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::KITCHEN)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$kitchenMechanic]))
        ;
        $manager->persist($kitchen);
        $manager->persist($kitchenMechanic);

        $distillerDismountableMechanic = new Dismountable();
        $distillerDismountableMechanic
            ->setProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->setActionCost(3)
            ->setChancesSuccess(50)
        ;
        $dailyChargeMechanic = new Charged();
        $dailyChargeMechanic
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
            ->setIsVisible(false)
        ;
        $distillerMechanic = new Tool();
        $distillerMechanic->setActions([ActionEnum::DISPENSE]);
        $narcoticDistiller= new EquipmentConfig();
        $narcoticDistiller
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::NARCOTIC_DISTILLER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$distillerMechanic, $distillerDismountableMechanic,$dailyChargeMechanic]))
        ;
        $manager->persist($narcoticDistiller);
        $manager->persist($distillerMechanic);
        $manager->persist($distillerDismountableMechanic);
        $manager->persist($dailyChargeMechanic);


        $shower = new EquipmentConfig();
        $shower
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::SHOWER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$showerMechanic, $showerDismountableMechanic]))
        ;
        $manager->persist($shower);


        $dynarcadeMechanic = new Tool();
        $dynarcadeMechanic->setActions([ActionEnum::PLAY_ARCADE]);
        $dynarcade = new EquipmentConfig();
        $dynarcade
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::DYNARCADE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$dynarcadeMechanic]))
        ;
        $manager->persist($dynarcade);
        $manager->persist($dynarcadeMechanic);

        $bedMechanic = new Tool();
        $bedMechanic->setActions([ActionEnum::LIE_DOWN]);
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

        $coffeMachineMechanic = new Tool();
        $coffeMachineMechanic->setActions([ActionEnum::COFFEE]);
        $coffeMachine = new EquipmentConfig();
        $coffeMachine
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COFFEE_MACHINE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$coffeMachineMechanic, $dailyChargeMechanic]))
        ;
        $manager->persist($coffeMachine);
        $manager->persist($coffeMachineMechanic);

        $cryoModuleMechanic = new Tool();
        $cryoModuleMechanic->setActions([ActionEnum::CHECK_ROSTER]);
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
        $mycoscanMechanic->setActions([ActionEnum::CHECK_INFECTION]);
        $mycoscan = new EquipmentConfig();
        $mycoscan
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::MYCOSCAN)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$mycoscanMechanic]))
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
        $turretCommandMechanic->setActions([ActionEnum::SHOOT_HUNTER]);
        $turretCommand= new EquipmentConfig();
        $turretCommand
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::TURRET_COMMAND)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$turretCommandMechanic, $turretChargeMechanic]))
        ;
        $manager->persist($turretCommand);
        $manager->persist($turretCommandMechanic);
        $manager->persist($turretChargeMechanic);

        $surgicalPlotMechanic = new Tool();
        $surgicalPlotMechanic->setGrantActions([ActionEnum::SELF_SURGERY, ActionEnum::SURGERY]);
        $surgicalPlot= new EquipmentConfig();
        $surgicalPlot
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::SURGICAL_PLOT)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(12)
            ->setMechanics(new ArrayCollection([$surgicalPlotMechanic]))
        ;
        $manager->persist($surgicalPlot);
        $manager->persist($surgicalPlotMechanic);

        $fuelTankMechanic = new Tool();
        $fuelTankMechanic->setGrantActions([ActionEnum::INJECT_FUEL, ActionEnum::RETRIEVE_FUEL]);
        $fuelTank = new EquipmentConfig();
        $fuelTank
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::FUEL_TANK)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$fuelTankMechanic]))
        ;
        $manager->persist($fuelTank);
        $manager->persist($fuelTankMechanic);

        $oxygenTankMechanic = new Tool();
        $oxygenTankMechanic->setGrantActions([ActionEnum::INJECT_OXYGEN, ActionEnum::RETRIEVE_OXYGEN]);
        $oxygenTank = new EquipmentConfig();
        $oxygenTank
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setBreakableRate(25)
            ->setMechanics(new ArrayCollection([$oxygenTankMechanic]))
        ;
        $manager->persist($oxygenTank);
        $manager->persist($oxygenTankMechanic);

        $manager->flush();
     }

     public function getDependencies()
     {
        return [
            GameConfigFixtures::class,
        ];
    }
}