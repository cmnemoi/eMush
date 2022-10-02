<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\Entity\Config\ModifierConfig;
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

        // @TODO terminals
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

        // @TODO terminals
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

        // @TODO gears
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

        $gravitySimulatorGear = $this->createGear([GearModifierConfigFixtures::GRAVITY_CONVERSION_MODIFIER, GearModifierConfigFixtures::GRAVITY_CYCLE_MODIFIER]);
        $gravitySimulator = new EquipmentConfig();
        $gravitySimulator
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setMechanics(new ArrayCollection([$gravitySimulatorGear]))
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
        ;
        $manager->persist($gravitySimulator);
        $manager->persist($gravitySimulatorGear);

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
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$showerMechanic]))
            ->setActions(new ArrayCollection([$this->getReference(TechnicianFixtures::DISMANTLE_3_25), $examineAction]))
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
        ;
        $manager->persist($thalasso);
        $manager->persist($showerMechanic);

        // @TODO ships
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

        // Tools
        $combustionChamberMechanic = new Tool();
        $combustionChamberMechanic->addAction($fuelInjectAction);
        $combustionChamberMechanic->addAction($fuelRetrieveAction);

        /** @var ChargeStatusConfig $combustionChargeStatus */
        $combustionChargeStatus = $this->getReference(ChargeStatusFixtures::COMBUSTION_CHAMBER);

        $combustionChamber = new EquipmentConfig();
        $combustionChamber
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COMBUSTION_CHAMBER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$combustionChamberMechanic]))
            ->setInitStatus(new ArrayCollection([$combustionChargeStatus]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
        ;
        $manager->persist($combustionChamber);
        $manager->persist($combustionChamberMechanic);

        /** @var Action $cookAction */
        $cookAction = $this->getReference(ActionsFixtures::COOK_DEFAULT);
        /** @var Action $washAction */
        $washAction = $this->getReference(ActionsFixtures::WASH_IN_SINK);

        $kitchenMechanic = new Tool();
        $kitchenMechanic->addAction($cookAction);
        $kitchenMechanic->addAction($washAction);

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

        /** @var ChargeStatusConfig $dispenserCharge */
        $dispenserCharge = $this->getReference(ChargeStatusFixtures::DISPENSER_CHARGE);

        $narcoticDistiller = new EquipmentConfig();
        $narcoticDistiller
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::NARCOTIC_DISTILLER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$distillerMechanic]))
            ->setInitStatus(new ArrayCollection([$dispenserCharge]))
            ->setActions(new ArrayCollection([$this->getReference(TechnicianFixtures::DISMANTLE_3_25), $examineAction]))
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
        ;

        $manager->persist($narcoticDistiller);
        $manager->persist($distillerMechanic);

        $shower = new EquipmentConfig();
        $shower
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::SHOWER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
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

        $medlabBed = new EquipmentConfig();
        $medlabBed
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::MEDLAB_BED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$bedMechanic]))
            ->setActions(new ArrayCollection([$examineAction]))
        ;
        $manager->persist($medlabBed);

        /** @var Action $coffeeAction */
        $coffeeAction = $this->getReference(ActionsFixtures::COFFEE_DEFAULT);

        $coffeMachineMechanic = new Tool();
        $coffeMachineMechanic->addAction($coffeeAction);

        /** @var ChargeStatusConfig $coffeeCharge */
        $coffeeCharge = $this->getReference(ChargeStatusFixtures::COFFEE_CHARGE);

        $coffeMachine = new EquipmentConfig();
        $coffeMachine
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::COFFEE_MACHINE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$coffeMachineMechanic]))
            ->setInitStatus(new ArrayCollection([$coffeeCharge]))
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

        /** @var Action $checkSporeLevelAction */
        $checkSporeLevelAction = $this->getReference(ActionsFixtures::CHECK_SPORE_LEVEL);

        $mycoscanMechanic = new Tool();
        $mycoscan = new EquipmentConfig();
        $mycoscan
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::MYCOSCAN)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$mycoscanMechanic]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction, $checkSporeLevelAction]))
        ;
        $manager->persist($mycoscan);
        $manager->persist($mycoscanMechanic);

        /** @var ChargeStatusConfig $turretCharge */
        $turretCharge = $this->getReference(ChargeStatusFixtures::TURRET_CHARGE);

        $turretCommandMechanic = new Tool();
//        $turretCommandMechanic->setActions([ActionEnum::SHOOT_HUNTER]);
        $turretCommand = new EquipmentConfig();
        $turretCommand
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::TURRET_COMMAND)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$turretCommandMechanic]))
            ->setInitStatus(new ArrayCollection([$turretCharge]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
        ;
        $manager->persist($turretCommand);
        $manager->persist($turretCommandMechanic);

        /** @var Action $selfSurgeryAction */
        $selfSurgeryAction = $this->getReference(ActionsFixtures::SELF_SURGERY);

        $surgicalPlotMechanic = new Tool();
        $surgicalPlotMechanic->addAction($selfSurgeryAction);

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

        /** @var ModifierConfig $oxygenTankModifier */
        $oxygenTankModifier = $this->getReference(GearModifierConfigFixtures::OXYGEN_TANK_MODIFIER);

        $oxygenTankGear = new Gear();
        $oxygenTankGear->setModifierConfigs(new ArrayCollection([$oxygenTankModifier]));

        $oxygenTank = new EquipmentConfig();
        $oxygenTank
            ->setGameConfig($gameConfig)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$oxygenTankMechanic, $oxygenTankGear]))
            ->setActions(new ArrayCollection([$repair25, $sabotage25, $reportAction, $examineAction]))
        ;
        $manager->persist($oxygenTank);
        $manager->persist($oxygenTankMechanic);
        $manager->persist($oxygenTankGear);

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
            GearModifierConfigFixtures::class,
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
