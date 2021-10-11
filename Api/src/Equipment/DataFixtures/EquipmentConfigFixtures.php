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
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\ChargeStatusConfig;

class EquipmentConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var Action $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);
        /** @var Action $repair3 */
        $repair3 = $this->getReference(TechnicianFixtures::REPAIR_3);
        /** @var Action $repair6 */
        $repair6 = $this->getReference(TechnicianFixtures::REPAIR_6);
        /** @var Action $repair12 */
        $repair12 = $this->getReference(TechnicianFixtures::REPAIR_12);
        /** @var Action $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        /** @var Action $sabotage3 */
        $sabotage3 = $this->getReference(TechnicianFixtures::SABOTAGE_3);
        /** @var Action $sabotage6 */
        $sabotage6 = $this->getReference(TechnicianFixtures::SABOTAGE_6);
        /** @var Action $sabotage12 */
        $sabotage12 = $this->getReference(TechnicianFixtures::SABOTAGE_12);
        /** @var Action $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        /** @var ChargeStatusConfig $electricCharge */
        $electricCharge = $this->getReference(ChargeStatusFixtures::CYCLE_ELECTRIC_CHARGE);

        /** @var ChargeStatusConfig $dailyElectricCharge */
        $dailyElectricCharge = $this->getReference(ChargeStatusFixtures::DAILY_ELECTRIC_CHARGE);

        //@TODO terminals
        $icarus = new EquipmentConfig();
        $icarus
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::ICARUS)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$examineAction]))
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
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$moveAction, $repair25, $sabotage25, $reportAction, $examineAction]))
        ;
        $manager->persist($door);

        $comsCenter = new EquipmentConfig();
        $comsCenter
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COMMUNICATION_CENTER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
        ;
        $manager->persist($comsCenter);

        $neronCore = new EquipmentConfig();
        $neronCore
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::NERON_CORE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
        ;
        $manager->persist($neronCore);

        $astroTerminal = new EquipmentConfig();
        $astroTerminal
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
        ;
        $manager->persist($astroTerminal);

        $researchLab = new EquipmentConfig();
        $researchLab
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::RESEARCH_LABORATORY)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
        ;
        $manager->persist($researchLab);

        $pilgred = new EquipmentConfig();
        $pilgred
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::PILGRED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$examineAction]))
        ;
        $manager->persist($pilgred);

        $calculator = new EquipmentConfig();
        $calculator
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::CALCULATOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
        ;
        $manager->persist($calculator);

        $biosTerminal = new EquipmentConfig();
        $biosTerminal
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::BIOS_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair3, $sabotage3, $reportAction, $examineAction]))
        ;
        $manager->persist($biosTerminal);

        $commandTerminal = new EquipmentConfig();
        $commandTerminal
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
        ;
        $manager->persist($commandTerminal);

        //@TODO gears
        $planetScanner = new EquipmentConfig();
        $planetScanner
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::PLANET_SCANNER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
        ;
        $manager->persist($planetScanner);

        $jukebox = new EquipmentConfig();
        $jukebox
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::JUKEBOX)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
        ;
        $manager->persist($jukebox);

        $emergencyReactor = new EquipmentConfig();
        $emergencyReactor
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::EMERGENCY_REACTOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
        ;
        $manager->persist($emergencyReactor);

        $reactorLateral = new EquipmentConfig();
        $reactorLateral
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::REACTOR_LATERAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
        ;
        $manager->persist($reactorLateral);

        $antennaGear = $this->createGear([GearModifierConfigFixtures::ANTENNA_MODIFIER]);

        $antenna = new EquipmentConfig();
        $antenna
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::ANTENNA)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
            ->setMechanics(new ArrayCollection([$antennaGear]))
        ;
        $manager->persist($antenna);
        $manager->persist($antennaGear);

        $gravitySimulator = new EquipmentConfig();
        $gravitySimulator
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
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
            ->setActions(new ArrayCollection([$this->getReference(TechnicianFixtures::DISMANTLE_3_25), $examineAction]))
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
        ;
        $manager->persist($thalasso);
        $manager->persist($showerMechanic);

        //@TODO ships
        $patrolShip = new EquipmentConfig();
        $patrolShip
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::PATROL_SHIP)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$examineAction]))
        ;
        $manager->persist($patrolShip);

        $pasiphae = new EquipmentConfig();
        $pasiphae
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::PASIPHAE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$examineAction]))
        ;
        $manager->persist($pasiphae);

        /** @var Action $removeCamera */
        $removeCamera = $this->getReference(ActionsFixtures::REMOVE_CAMERA);

        $cameraMechanic = new Tool();
        $cameraMechanic->addAction($removeCamera);
        $camera = new EquipmentConfig();
        $camera
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::CAMERA_EQUIPMENT)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$cameraMechanic]))
            ->setActions(new ArrayCollection([$this->getReference(TechnicianFixtures::DISMANTLE_3_25), $repair25, $sabotage25, $reportAction, $examineAction]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $manager->persist($camera);
        $manager->persist($cameraMechanic);

        /** @var Action $fuelInjectAction */
        $fuelInjectAction = $this->getReference(ActionsFixtures::FUEL_INJECT);
        /** @var Action $fuelRetrieveAction */
        $fuelRetrieveAction = $this->getReference(ActionsFixtures::FUEL_RETRIEVE);

        //Tools
        $combustionChamberMechanic = new Tool();
        $combustionChamberMechanic->addAction($fuelInjectAction);
        $combustionChamberMechanic->addAction($fuelRetrieveAction);

        /** @var ChargeStatusConfig $combustionChargeStatus */
        $combustionChargeStatus = $this->getReference(ChargeStatusFixtures::COMBUSTION_CHAMBER);

        $combustionChargedMechanic = new Charged();
        $combustionChargedMechanic
            ->setMaxCharge(9)
            ->setStartCharge(0)
            ->setChargeStatusConfig($combustionChargeStatus)
        ;

        $combustionChamber = new EquipmentConfig();
        $combustionChamber
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COMBUSTION_CHAMBER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$combustionChamberMechanic, $combustionChargedMechanic]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
        ;
        $manager->persist($combustionChamber);
        $manager->persist($combustionChamberMechanic);
        $manager->persist($combustionChargedMechanic);

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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$kitchenMechanic]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
        ;
        $manager->persist($kitchen);
        $manager->persist($kitchenMechanic);

        $distillerMechanic = new Tool();
        /** @var Action $dispenseAction */
        $dispenseAction = $this->getReference(ActionsFixtures::DISPENSE_DRUG);
        $distillerMechanic->addAction($dispenseAction);

        $dailyChargeMechanic = new Charged();
        $dailyChargeMechanic
            ->setMaxCharge(1)
            ->setStartCharge(1)
            ->setChargeStatusConfig($dailyElectricCharge)
        ;

        $narcoticDistiller = new EquipmentConfig();
        $narcoticDistiller
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::NARCOTIC_DISTILLER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$distillerMechanic, $dailyChargeMechanic]))
            ->setActions(new ArrayCollection([$this->getReference(TechnicianFixtures::DISMANTLE_3_25), $examineAction]))
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
            ->setActions(new ArrayCollection([$this->getReference(TechnicianFixtures::DISMANTLE_3_25), $examineAction]))
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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$dynarcadeMechanic]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
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
            ->setActions(new ArrayCollection([$examineAction]))
        ;
        $manager->persist($bed);
        $manager->persist($bedMechanic);

        /** @var Action $healAction */
        $healAction = $this->getReference(ActionsFixtures::HEAL_DEFAULT);
        /** @var Action $selfHealAction */
        $selfHealAction = $this->getReference(ActionsFixtures::HEAL_SELF);

        $medlabBedMechanic = new Tool();
        $medlabBedMechanic->addAction($healAction);
        $medlabBedMechanic->addAction($selfHealAction);

        $medlabBed = new EquipmentConfig();
        $medlabBed
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::MEDLAB_BED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$bedMechanic, $medlabBedMechanic]))
            ->setActions(new ArrayCollection([$examineAction]))
        ;
        $manager->persist($medlabBed);
        $manager->persist($medlabBedMechanic);

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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$coffeMachineMechanic, $dailyChargeMechanic]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
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
            ->setActions(new ArrayCollection([$examineAction]))
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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$mycoscanMechanic]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
        ;
        $manager->persist($mycoscan);
        $manager->persist($mycoscanMechanic);

        $turretChargeMechanic = new Charged();
        $turretChargeMechanic
            ->setMaxCharge(4)
            ->setStartCharge(4)
            ->setChargeStatusConfig($electricCharge)
        ;

        $turretCommandMechanic = new Tool();
//        $turretCommandMechanic->setActions([ActionEnum::SHOOT_HUNTER]);
        $turretCommand = new EquipmentConfig();
        $turretCommand
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::TURRET_COMMAND)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$turretCommandMechanic, $turretChargeMechanic]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
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
            ->setName(EquipmentEnum::SURGERY_PLOT)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$surgicalPlotMechanic]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$fuelTankMechanic]))
            ->setActions(new ArrayCollection([$repair25, $sabotage25, $reportAction, $examineAction]))
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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$oxygenTankMechanic]))
            ->setActions(new ArrayCollection([$repair25, $sabotage25, $reportAction, $examineAction]))
        ;
        $manager->persist($oxygenTank);
        $manager->persist($oxygenTankMechanic);

        $gravityGear = $this->createGear([GearModifierConfigFixtures::GRAVITY_CYCLE_MODIFIER, GearModifierConfigFixtures::GRAVITY_CONVERSION_MODIFIER]);

        $gravitySimulator = new EquipmentConfig();
        $gravitySimulator
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
            ->setMechanics(new ArrayCollection([$gravityGear]))
        ;

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            ActionsFixtures::class,
            TechnicianFixtures::class,
            ChargeStatusFixtures::class,
            StatusFixtures::class,
        ];
    }

    private function createGear(array $modifierConfigNames): Gear
    {
        $gear = new Gear();

        $modifierConfigs = [];
        foreach ($modifierConfigNames as $modifierConfigName) {
            /* @var ModifierConfig $modifierConfig */
            $modifierConfigs[] = $this->getReference($modifierConfigName);
        }

        $gear->setModifierConfigs(new ArrayCollection($modifierConfigs));

        return $gear;
    }
}
