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
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
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

        /** @var Action $dismantle25 */
        $dismantle25 = $this->getReference(TechnicianFixtures::DISMANTLE_3_25);

        // @TODO terminals
        $icarus = new EquipmentConfig();
        $icarus
            ->setEquipmentName(EquipmentEnum::ICARUS)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($icarus);

        /** @var Action $moveAction */
        $moveAction = $this->getReference(ActionsFixtures::MOVE_DEFAULT);

        // @TODO terminals
        $door = new EquipmentConfig();
        $door
            ->setEquipmentName(EquipmentEnum::DOOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$moveAction, $repair25, $sabotage25, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($door);

        $comsCenter = new EquipmentConfig();
        $comsCenter
            ->setEquipmentName(EquipmentEnum::COMMUNICATION_CENTER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($comsCenter);

        $neronCore = new EquipmentConfig();
        $neronCore
            ->setEquipmentName(EquipmentEnum::NERON_CORE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($neronCore);

        $astroTerminal = new EquipmentConfig();
        $astroTerminal
            ->setEquipmentName(EquipmentEnum::ASTRO_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($astroTerminal);

        $researchLab = new EquipmentConfig();
        $researchLab
            ->setEquipmentName(EquipmentEnum::RESEARCH_LABORATORY)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($researchLab);

        $pilgred = new EquipmentConfig();
        $pilgred
            ->setEquipmentName(EquipmentEnum::PILGRED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($pilgred);

        $calculator = new EquipmentConfig();
        $calculator
            ->setEquipmentName(EquipmentEnum::CALCULATOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($calculator);

        $biosTerminal = new EquipmentConfig();
        $biosTerminal
            ->setEquipmentName(EquipmentEnum::BIOS_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair3, $sabotage3, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($biosTerminal);

        $commandTerminal = new EquipmentConfig();
        $commandTerminal
            ->setEquipmentName(EquipmentEnum::COMMAND_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($commandTerminal);

        // @TODO gears
        $planetScanner = new EquipmentConfig();
        $planetScanner
            ->setEquipmentName(EquipmentEnum::PLANET_SCANNER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($planetScanner);

        $jukebox = new EquipmentConfig();
        $jukebox
            ->setEquipmentName(EquipmentEnum::JUKEBOX)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($jukebox);

        $emergencyReactor = new EquipmentConfig();
        $emergencyReactor
            ->setEquipmentName(EquipmentEnum::EMERGENCY_REACTOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($emergencyReactor);

        $reactorLateral = new EquipmentConfig();
        $reactorLateral
            ->setEquipmentName(EquipmentEnum::REACTOR_LATERAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($reactorLateral);

        $antennaGear = $this->createGear([GearModifierConfigFixtures::ANTENNA_MODIFIER], EquipmentEnum::ANTENNA);

        $antenna = new EquipmentConfig();
        $antenna
            ->setEquipmentName(EquipmentEnum::ANTENNA)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
            ->setMechanics(new ArrayCollection([$antennaGear]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($antenna);
        $manager->persist($antennaGear);

        $gravitySimulatorGear = $this->createGear(
            [GearModifierConfigFixtures::GRAVITY_CONVERSION_MODIFIER, GearModifierConfigFixtures::GRAVITY_CYCLE_MODIFIER],
            EquipmentEnum::GRAVITY_SIMULATOR
        );
        $gravitySimulator = new EquipmentConfig();
        $gravitySimulator
            ->setEquipmentName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setMechanics(new ArrayCollection([$gravitySimulatorGear]))
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair6, $sabotage6, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($gravitySimulator);
        $manager->persist($gravitySimulatorGear);

        /** @var Action $showerAction */
        $showerAction = $this->getReference(ActionsFixtures::SHOWER_DEFAULT);

        $thalasso = new EquipmentConfig();
        $thalasso
            ->setEquipmentName(EquipmentEnum::THALASSO)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair25, $dismantle25, $examineAction, $showerAction]))
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($thalasso);

        // @TODO ships
        $patrolShip = new EquipmentConfig();
        $patrolShip
            ->setEquipmentName(EquipmentEnum::PATROL_SHIP)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($patrolShip);

        $pasiphae = new EquipmentConfig();
        $pasiphae
            ->setEquipmentName(EquipmentEnum::PASIPHAE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($pasiphae);

        /** @var Action $removeCamera */
        $removeCamera = $this->getReference(ActionsFixtures::REMOVE_CAMERA);

        $camera = new EquipmentConfig();
        $camera
            ->setEquipmentName(EquipmentEnum::CAMERA_EQUIPMENT)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$dismantle25, $repair25, $sabotage25, $reportAction, $examineAction, $removeCamera]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($camera);

        /** @var Action $fuelInjectAction */
        $fuelInjectAction = $this->getReference(ActionsFixtures::FUEL_INJECT);
        /** @var Action $fuelRetrieveAction */
        $fuelRetrieveAction = $this->getReference(ActionsFixtures::FUEL_RETRIEVE);

        // Tools
        /** @var ChargeStatusConfig $combustionChargeStatus */
        $combustionChargeStatus = $this->getReference(ChargeStatusFixtures::COMBUSTION_CHAMBER);

        $combustionChamber = new EquipmentConfig();
        $combustionChamber
            ->setEquipmentName(EquipmentEnum::COMBUSTION_CHAMBER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setInitStatuses(new ArrayCollection([$combustionChargeStatus]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($combustionChamber);

        /** @var Action $cookAction */
        $cookAction = $this->getReference(ActionsFixtures::COOK_DEFAULT);
        /** @var Action $washAction */
        $washAction = $this->getReference(ActionsFixtures::WASH_IN_SINK);

        $kitchenMechanic = $this->createTool([$cookAction], EquipmentEnum::KITCHEN);

        $kitchen = new EquipmentConfig();
        $kitchen
            ->setEquipmentName(EquipmentEnum::KITCHEN)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$kitchenMechanic]))
            ->setActions(new ArrayCollection([
                $repair12,
                $sabotage12,
                $reportAction,
                $examineAction,
                $washAction,
            ]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($kitchen);
        $manager->persist($kitchenMechanic);

        /** @var Action $dispenseAction */
        $dispenseAction = $this->getReference(ActionsFixtures::DISPENSE_DRUG);
        /** @var ChargeStatusConfig $dispenserCharge */
        $dispenserCharge = $this->getReference(ChargeStatusFixtures::DISPENSER_CHARGE);

        $narcoticDistiller = new EquipmentConfig();
        $narcoticDistiller
            ->setEquipmentName(EquipmentEnum::NARCOTIC_DISTILLER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setInitStatuses(new ArrayCollection([$dispenserCharge]))
            ->setActions(new ArrayCollection([$dismantle25, $examineAction, $dispenseAction]))
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($narcoticDistiller);

        $shower = new EquipmentConfig();
        $shower
            ->setEquipmentName(EquipmentEnum::SHOWER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair25, $dismantle25, $examineAction, $showerAction]))
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($shower);

        $dynarcade = new EquipmentConfig();
        $dynarcade
            ->setEquipmentName(EquipmentEnum::DYNARCADE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($dynarcade);

        /** @var Action $lieDownAction */
        $lieDownAction = $this->getReference(ActionsFixtures::LIE_DOWN);
        $bed = new EquipmentConfig();
        $bed
            ->setEquipmentName(EquipmentEnum::BED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$examineAction, $lieDownAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($bed);

        $medlabBed = new EquipmentConfig();
        $medlabBed
            ->setEquipmentName(EquipmentEnum::MEDLAB_BED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$examineAction, $lieDownAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($medlabBed);

        /** @var Action $coffeeAction */
        $coffeeAction = $this->getReference(ActionsFixtures::COFFEE_DEFAULT);
        /** @var ChargeStatusConfig $coffeeCharge */
        $coffeeCharge = $this->getReference(ChargeStatusFixtures::COFFEE_CHARGE);
        $coffeMachine = new EquipmentConfig();
        $coffeMachine
            ->setEquipmentName(EquipmentEnum::COFFEE_MACHINE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setInitStatuses(new ArrayCollection([$coffeeCharge]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction, $coffeeAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($coffeMachine);

        $cryoModule = new EquipmentConfig();
        $cryoModule
            ->setEquipmentName(EquipmentEnum::CRYO_MODULE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions(new ArrayCollection([$examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($cryoModule);

        /** @var Action $checkSporeLevelAction */
        $checkSporeLevelAction = $this->getReference(ActionsFixtures::CHECK_SPORE_LEVEL);
        $mycoscan = new EquipmentConfig();
        $mycoscan
            ->setEquipmentName(EquipmentEnum::MYCOSCAN)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction, $checkSporeLevelAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($mycoscan);

        /** @var ChargeStatusConfig $turretCharge */
        $turretCharge = $this->getReference(ChargeStatusFixtures::TURRET_CHARGE);

        $turretCommand = new EquipmentConfig();
        $turretCommand
            ->setEquipmentName(EquipmentEnum::TURRET_COMMAND)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setInitStatuses(new ArrayCollection([$turretCharge]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($turretCommand);

        /** @var Action $selfSurgeryAction */
        $selfSurgeryAction = $this->getReference(ActionsFixtures::SELF_SURGERY);
        $surgicalPlotMechanic = $this->createTool([$selfSurgeryAction], EquipmentEnum::SURGERY_PLOT);
        $surgicalPlot = new EquipmentConfig();
        $surgicalPlot
            ->setEquipmentName(EquipmentEnum::SURGERY_PLOT)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$surgicalPlotMechanic]))
            ->setActions(new ArrayCollection([$repair12, $sabotage12, $reportAction, $examineAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($surgicalPlot);
        $manager->persist($surgicalPlotMechanic);

        $fuelTankMechanic = $this->createTool([$fuelInjectAction], EquipmentEnum::FUEL_TANK);
        $fuelTank = new EquipmentConfig();
        $fuelTank
            ->setEquipmentName(EquipmentEnum::FUEL_TANK)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$fuelTankMechanic]))
            ->setActions(new ArrayCollection([$repair25, $sabotage25, $reportAction, $examineAction, $fuelRetrieveAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;
        $manager->persist($fuelTank);
        $manager->persist($fuelTankMechanic);

        /** @var Action $oxygenInjectAction */
        $oxygenInjectAction = $this->getReference(ActionsFixtures::OXYGEN_INJECT);
        /** @var Action $oxygenRetrieveAction */
        $oxygenRetrieveAction = $this->getReference(ActionsFixtures::OXYGEN_RETRIEVE);

        $oxygenTankMechanic = $this->createTool([$oxygenInjectAction], EquipmentEnum::OXYGEN_TANK);

        $oxygenTankGear = $this->createGear([GearModifierConfigFixtures::OXYGEN_TANK_MODIFIER], EquipmentEnum::OXYGEN_TANK);

        $oxygenTank = new EquipmentConfig();
        $oxygenTank
            ->setEquipmentName(EquipmentEnum::OXYGEN_TANK)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics(new ArrayCollection([$oxygenTankMechanic, $oxygenTankGear]))
            ->setActions(new ArrayCollection([$repair25, $sabotage25, $reportAction, $examineAction, $oxygenRetrieveAction]))
            ->buildName(GameConfigEnum::DEFAULT)
        ;

        $manager->persist($oxygenTank);
        $manager->persist($oxygenTankMechanic);
        $manager->persist($oxygenTankGear);

        $gameConfig
            ->addEquipmentConfig($icarus)
            ->addEquipmentConfig($door)
            ->addEquipmentConfig($comsCenter)
            ->addEquipmentConfig($neronCore)
            ->addEquipmentConfig($astroTerminal)
            ->addEquipmentConfig($researchLab)
            ->addEquipmentConfig($pilgred)
            ->addEquipmentConfig($calculator)
            ->addEquipmentConfig($biosTerminal)
            ->addEquipmentConfig($commandTerminal)
            ->addEquipmentConfig($planetScanner)
            ->addEquipmentConfig($jukebox)
            ->addEquipmentConfig($emergencyReactor)
            ->addEquipmentConfig($reactorLateral)
            ->addEquipmentConfig($antenna)
            ->addEquipmentConfig($gravitySimulator)
            ->addEquipmentConfig($thalasso)
            ->addEquipmentConfig($patrolShip)
            ->addEquipmentConfig($pasiphae)
            ->addEquipmentConfig($camera)
            ->addEquipmentConfig($combustionChamber)
            ->addEquipmentConfig($kitchen)
            ->addEquipmentConfig($narcoticDistiller)
            ->addEquipmentConfig($shower)
            ->addEquipmentConfig($dynarcade)
            ->addEquipmentConfig($bed)
            ->addEquipmentConfig($medlabBed)
            ->addEquipmentConfig($coffeMachine)
            ->addEquipmentConfig($cryoModule)
            ->addEquipmentConfig($mycoscan)
            ->addEquipmentConfig($turretCommand)
            ->addEquipmentConfig($surgicalPlot)
            ->addEquipmentConfig($fuelTank)
            ->addEquipmentConfig($oxygenTank)
        ;
        $manager->persist($gameConfig);

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

    private function createGear(array $modifierConfigNames, string $name): Gear
    {
        $gear = new Gear();

        $modifierConfigsArray = [];
        foreach ($modifierConfigNames as $modifierConfigName) {
            /** @var AbstractModifierConfig $currentModifierConfig */
            $currentModifierConfig = $this->getReference($modifierConfigName);

            $modifierConfigsArray[] = $currentModifierConfig;
        }

        $modifierConfigs = new ArrayCollection($modifierConfigsArray);

        $gear
            ->setModifierConfigs($modifierConfigs)
            ->buildName(EquipmentMechanicEnum::GEAR . '_' . $name, GameConfigEnum::DEFAULT)
        ;

        return $gear;
    }

    private function createTool(array $actions, string $name): Tool
    {
        $tool = new Tool();

        $tool
            ->setActions(new ArrayCollection($actions))
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . $name, GameConfigEnum::DEFAULT)
        ;

        return $tool;
    }
}
