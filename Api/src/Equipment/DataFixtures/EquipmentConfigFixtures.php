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
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\ChargeStatusConfig;

/** @codeCoverageIgnore */
class EquipmentConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var Action $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var Action $accessTerminalAction */
        $accessTerminalAction = $this->getReference(ActionsFixtures::ACCESS_TERMINAL);

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

        /** @var Action $exitTerminalAction */
        $exitTerminalAction = $this->getReference(ActionsFixtures::EXIT_TERMINAL);

        /** @var Action $takeoffToPlanetAction */
        $takeoffToPlanetAction = $this->getReference(ActionsFixtures::TAKEOFF_TO_PLANET);

        /** @TODO terminals */
        /** @var Action $moveAction */
        $moveAction = $this->getReference(ActionsFixtures::MOVE_DEFAULT);

        /** @TODO terminals */
        $door = new EquipmentConfig();
        $door
            ->setEquipmentName(EquipmentEnum::DOOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$moveAction, $repair25, $sabotage25, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($door);

        $comsCenter = new EquipmentConfig();
        $comsCenter
            ->setEquipmentName(EquipmentEnum::COMMUNICATION_CENTER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($comsCenter);

        $neronCore = new EquipmentConfig();
        $neronCore
            ->setEquipmentName(EquipmentEnum::NERON_CORE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair6, $sabotage6, $reportAction, $examineAction, $accessTerminalAction, $exitTerminalAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($neronCore);

        $auxiliaryTerminal = new EquipmentConfig();
        $auxiliaryTerminal
            ->setEquipmentName(EquipmentEnum::AUXILIARY_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair6, $sabotage6, $reportAction, $examineAction, $accessTerminalAction, $exitTerminalAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($auxiliaryTerminal);

        /** @var Action $scanAction */
        $scanAction = $this->getReference(ActionsFixtures::SCAN);

        /** @var Action $analyzePlanetAction */
        $analyzePlanetAction = $this->getReference(ActionsFixtures::ANALYZE_PLANET);

        /** @var Action $deletePlanetAction */
        $deletePlanetAction = $this->getReference(ActionsFixtures::DELETE_PLANET);

        $astroTerminalTool = $this->createTool([$analyzePlanetAction, $deletePlanetAction], EquipmentEnum::ASTRO_TERMINAL);
        $manager->persist($astroTerminalTool);

        $astroTerminal = new EquipmentConfig();
        $astroTerminal
            ->setEquipmentName(EquipmentEnum::ASTRO_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair12, $sabotage12, $reportAction, $examineAction, $exitTerminalAction, $scanAction, $accessTerminalAction])
            ->setMechanics([$astroTerminalTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($astroTerminal);

        $researchLab = new EquipmentConfig();
        $researchLab
            ->setEquipmentName(EquipmentEnum::RESEARCH_LABORATORY)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($researchLab);

        /** @var Action $repairPilgredAction */
        $repairPilgredAction = $this->getReference(ActionEnum::REPAIR_PILGRED);

        $pilgredTerminalTool = $this->createTool([$repairPilgredAction], EquipmentEnum::PILGRED);
        $manager->persist($pilgredTerminalTool);

        $pilgred = new EquipmentConfig();
        $pilgred
            ->setEquipmentName(EquipmentEnum::PILGRED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions([$examineAction, $accessTerminalAction, $exitTerminalAction])
            ->setMechanics([$pilgredTerminalTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($pilgred);

        $calculator = new EquipmentConfig();
        $calculator
            ->setEquipmentName(EquipmentEnum::CALCULATOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($calculator);

        /** @var Action $changeNeronCpuPriorityAction */
        $changeNeronCpuPriorityAction = $this->getReference(ActionsFixtures::CHANGE_NERON_CPU_PRIORITY);
        $biosTerminal = new EquipmentConfig();
        $biosTerminal
            ->setEquipmentName(EquipmentEnum::BIOS_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair3, $sabotage3, $reportAction, $examineAction, $changeNeronCpuPriorityAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($biosTerminal);

        /** @var Action $hackAction */
        $hackAction = $this->getReference(ActionsFixtures::HACK);

        /** @var Action $advanceDaedalusAction */
        $advanceDaedalusAction = $this->getReference(ActionsFixtures::ADVANCE_DAEDALUS);

        /** @var Action $turnDaedalusLeftAction */
        $turnDaedalusLeftAction = $this->getReference(ActionsFixtures::TURN_DAEDALUS_LEFT);

        /** @var Action $turnDaedalusRightAction */
        $turnDaedalusRightAction = $this->getReference(ActionsFixtures::TURN_DAEDALUS_RIGHT);

        /** @var Action $leaveOrbitAction */
        $leaveOrbitAction = $this->getReference(ActionsFixtures::LEAVE_ORBIT);

        /** @var Action $returnToSolAction */
        $returnToSolAction = $this->getReference(ActionEnum::RETURN_TO_SOL);

        $commandTerminal = new EquipmentConfig();
        $commandTerminal
            ->setEquipmentName(EquipmentEnum::COMMAND_TERMINAL)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([
                $repair12,
                $sabotage12,
                $reportAction,
                $examineAction,
                $hackAction,
                $exitTerminalAction,
                $advanceDaedalusAction,
                $turnDaedalusLeftAction,
                $turnDaedalusRightAction,
                $leaveOrbitAction,
                $accessTerminalAction,
                $returnToSolAction,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($commandTerminal);

        $planetScannerGear = $this->createGear([GearModifierConfigFixtures::PLANET_SCANNER_MODIFIER], EquipmentEnum::PLANET_SCANNER);
        $manager->persist($planetScannerGear);

        /** @TODO gears */
        $planetScanner = new EquipmentConfig();
        $planetScanner
            ->setEquipmentName(EquipmentEnum::PLANET_SCANNER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair12, $sabotage12, $reportAction, $examineAction])
            ->setMechanics([$planetScannerGear])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($planetScanner);

        $jukebox = new EquipmentConfig();
        $jukebox
            ->setEquipmentName(EquipmentEnum::JUKEBOX)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair12, $sabotage12, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($jukebox);

        $emergencyReactor = new EquipmentConfig();
        $emergencyReactor
            ->setEquipmentName(EquipmentEnum::EMERGENCY_REACTOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($emergencyReactor);

        $reactorLateralAlpha = new EquipmentConfig();
        $reactorLateralAlpha
            ->setEquipmentName(EquipmentEnum::REACTOR_LATERAL_ALPHA)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($reactorLateralAlpha);

        $reactorLateralBravo = new EquipmentConfig();
        $reactorLateralBravo
            ->setEquipmentName(EquipmentEnum::REACTOR_LATERAL_BRAVO)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($reactorLateralBravo);

        $antennaGear = $this->createGear([GearModifierConfigFixtures::ANTENNA_MODIFIER], EquipmentEnum::ANTENNA);

        $antenna = new EquipmentConfig();
        $antenna
            ->setEquipmentName(EquipmentEnum::ANTENNA)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair12, $sabotage12, $reportAction, $examineAction])
            ->setMechanics([$antennaGear])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($antenna);
        $manager->persist($antennaGear);

        $gravitySimulator = new EquipmentConfig();
        $gravitySimulator
            ->setEquipmentName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($gravitySimulator);

        /** @var Action $showerAction */
        $showerAction = $this->getReference(ActionsFixtures::SHOWER_DEFAULT);

        $thalasso = new EquipmentConfig();
        $thalasso
            ->setEquipmentName(EquipmentEnum::THALASSO)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair25, $dismantle25, $examineAction, $reportAction, $showerAction])
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($thalasso);

        /** @TODO ships */
        /** @var Action $takeoffAction */
        $takeoffAction = $this->getReference(ActionsFixtures::TAKEOFF);

        /** @var Action $landAction */
        $landAction = $this->getReference(ActionsFixtures::LAND);

        /** @var Action $shootHunterPatrolShipAction */
        $shootHunterPatrolShipAction = $this->getReference(ActionsFixtures::SHOOT_HUNTER_PATROL_SHIP);

        /** @var Action $renovateAction */
        $renovateAction = $this->getReference(ActionsFixtures::RENOVATE);

        /** @var Action $collectScrap */
        $collectScrap = $this->getReference(ActionsFixtures::COLLECT_SCRAP);

        /** @var ChargeStatusConfig $patrolShipChargeStatus */
        $patrolShipChargeStatus = $this->getReference(ChargeStatusFixtures::PATROLLER_CHARGE);

        $icarusPatrolShip = $this->createPatrolShip(
            [$takeoffAction, $landAction, $renovateAction, $collectScrap, $takeoffToPlanetAction],
            EquipmentEnum::ICARUS,
        );
        $icarusPatrolShip
            ->setCollectScrapNumber([])
            ->setCollectScrapPatrolShipDamage([])
            ->setCollectScrapPlayerDamage([])
            ->setFailedManoeuvreDaedalusDamage([])
            ->setFailedManoeuvrePatrolShipDamage([])
            ->setFailedManoeuvrePlayerDamage([])
            ->setNumberOfExplorationSteps(9)
            ->setDockingPlace(RoomEnum::ICARUS_BAY);
        $manager->persist($icarusPatrolShip);

        $icarus = new EquipmentConfig();
        $icarus
            ->setEquipmentName(EquipmentEnum::ICARUS)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions([$examineAction])
            ->setMechanics([$icarusPatrolShip])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($icarus);

        $patrolShipMechanic = $this->createPatrolShip(
            [$takeoffAction, $landAction, $renovateAction, $collectScrap, $takeoffToPlanetAction],
            EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN
        );
        $patrolShipMechanic
            ->setCollectScrapNumber([
                1 => 1,
            ])
            ->setCollectScrapPatrolShipDamage([
                2 => 1,
                3 => 1,
                4 => 1,
            ])
            ->setCollectScrapPlayerDamage([
                2 => 1,
                3 => 1,
                4 => 1,
            ])
            ->setFailedManoeuvreDaedalusDamage([
                2 => 1,
                3 => 1,
                4 => 1,
            ])
            ->setFailedManoeuvrePatrolShipDamage([1 => 1])
            ->setFailedManoeuvrePlayerDamage([
                1 => 1,
                2 => 1,
            ])
            ->setNumberOfExplorationSteps(3)
            ->setDockingPlace(RoomEnum::ALPHA_BAY);
        $patrolShipWeapon = $this->createWeapon(
            [],
            EquipmentEnum::PATROL_SHIP
        );
        $patrolShipWeapon
            ->setBaseDamageRange(
                [
                    3 => 1,
                    4 => 1,
                    5 => 1,
                    6 => 1,
                ]
            )
            ->addAction($shootHunterPatrolShipAction);

        $patrolShip = new EquipmentConfig();
        $patrolShip
            ->setEquipmentName(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$sabotage12, $examineAction])
            ->setMechanics([$patrolShipMechanic, $patrolShipWeapon])
            ->setInitStatuses([$patrolShipChargeStatus])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($patrolShipMechanic);
        $manager->persist($patrolShipWeapon);
        $manager->persist($patrolShip);

        $pasiphaeMechanic = $this->createPatrolShip(
            [$takeoffAction, $landAction, $collectScrap, $renovateAction, $takeoffToPlanetAction],
            EquipmentEnum::PASIPHAE
        );
        $pasiphaeMechanic
            ->setCollectScrapNumber([
                1 => 1,
                2 => 1,
                3 => 1,
            ])
            ->setCollectScrapPatrolShipDamage([
                2 => 1,
                3 => 1,
                4 => 1,
            ])
            ->setCollectScrapPlayerDamage([
                2 => 1,
                3 => 1,
                4 => 1,
            ])
            ->setFailedManoeuvreDaedalusDamage([
                2 => 1,
                3 => 1,
                4 => 1,
            ])
            ->setFailedManoeuvrePatrolShipDamage([1 => 1])
            ->setFailedManoeuvrePlayerDamage([
                1 => 1,
                2 => 1,
            ])
            ->setNumberOfExplorationSteps(3)
            ->setDockingPlace(RoomEnum::ALPHA_BAY_2);

        /** @var ChargeStatusConfig $pasiphaeArmor */
        $pasiphaeArmor = $this->getReference(ChargeStatusFixtures::PASIPHAE_ARMOR);
        $pasiphae = new EquipmentConfig();
        $pasiphae
            ->setEquipmentName(EquipmentEnum::PASIPHAE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$sabotage12, $examineAction])
            ->setMechanics([$pasiphaeMechanic])
            ->setInitStatuses([$pasiphaeArmor])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($pasiphaeMechanic);
        $manager->persist($pasiphae);

        /** @var Action $removeCamera */
        $removeCamera = $this->getReference(ActionsFixtures::REMOVE_CAMERA);

        $camera = new EquipmentConfig();
        $camera
            ->setEquipmentName(EquipmentEnum::CAMERA_EQUIPMENT)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions([$dismantle25, $repair25, $sabotage25, $reportAction, $examineAction, $removeCamera])
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($camera);

        /** @var Action $fuelInjectAction */
        $fuelInjectAction = $this->getReference(ActionsFixtures::FUEL_INJECT);

        /** @var Action $fuelRetrieveAction */
        $fuelRetrieveAction = $this->getReference(ActionsFixtures::FUEL_RETRIEVE);

        /** @var Action $retrieveFuelChamberAction */
        $retrieveFuelChamberAction = $this->getReference(ActionsFixtures::RETRIEVE_FUEL_CHAMBER);

        /** @var Action $checkFuelChamberLevelAction */
        $checkFuelChamberLevelAction = $this->getReference(ActionsFixtures::CHECK_FUEL_CHAMBER_LEVEL);

        // Tools
        $combustionChamber = new EquipmentConfig();
        $combustionChamber
            ->setEquipmentName(EquipmentEnum::COMBUSTION_CHAMBER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair12, $sabotage12, $reportAction, $examineAction, $retrieveFuelChamberAction, $checkFuelChamberLevelAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($combustionChamber);

        /** @var Action $cookAction */
        $cookAction = $this->getReference(ActionsFixtures::COOK_DEFAULT);

        /** @var Action $washAction */
        $washAction = $this->getReference(ActionsFixtures::WASH_IN_SINK);

        $kitchenMechanic = $this->createTool([$cookAction, $washAction], EquipmentEnum::KITCHEN);

        /** @var ChargeStatusConfig $sinkCharge */
        $sinkCharge = $this->getReference(ChargeStatusFixtures::SINK_CHARGE);

        $kitchen = new EquipmentConfig();
        $kitchen
            ->setEquipmentName(EquipmentEnum::KITCHEN)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setInitStatuses([$sinkCharge])
            ->setMechanics([$kitchenMechanic])
            ->setActions([
                $repair12,
                $sabotage12,
                $reportAction,
                $examineAction,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($kitchen);
        $manager->persist($kitchenMechanic);

        /** @var Action $dispenseAction */
        $dispenseAction = $this->getReference(ActionsFixtures::DISPENSE_DRUG);

        /** @var ChargeStatusConfig $dispenserCharge */
        $dispenserCharge = $this->getReference(ChargeStatusFixtures::DISPENSER_CHARGE);

        $distillerMechanic = $this->createTool([$dispenseAction], EquipmentEnum::NARCOTIC_DISTILLER);

        $narcoticDistiller = new EquipmentConfig();
        $narcoticDistiller
            ->setEquipmentName(EquipmentEnum::NARCOTIC_DISTILLER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setInitStatuses([$dispenserCharge])
            ->setMechanics([$distillerMechanic])
            ->setActions([$dismantle25, $examineAction])
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($narcoticDistiller);
        $manager->persist($distillerMechanic);

        $shower = new EquipmentConfig();
        $shower
            ->setEquipmentName(EquipmentEnum::SHOWER)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair25, $dismantle25, $examineAction, $showerAction])
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($shower);

        /** @var Action $playArcadeAction */
        $playArcadeAction = $this->getReference(ActionsFixtures::PLAY_ARCADE);
        $dynarcadeActions = [$repair12, $sabotage12, $reportAction, $examineAction, $playArcadeAction];
        $dynarcade = new EquipmentConfig();
        $dynarcade
            ->setEquipmentName(EquipmentEnum::DYNARCADE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions($dynarcadeActions)
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($dynarcade);

        /** @var Action $lieDownAction */
        $lieDownAction = $this->getReference(ActionsFixtures::LIE_DOWN);
        $bed = new EquipmentConfig();
        $bed
            ->setEquipmentName(EquipmentEnum::BED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions([$examineAction, $lieDownAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($bed);

        $medlabBed = new EquipmentConfig();
        $medlabBed
            ->setEquipmentName(EquipmentEnum::MEDLAB_BED)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions([$examineAction, $lieDownAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($medlabBed);

        /** @var Action $coffeeAction */
        $coffeeAction = $this->getReference(ActionsFixtures::COFFEE_DEFAULT);

        /** @var ChargeStatusConfig $coffeeCharge */
        $coffeeCharge = $this->getReference(ChargeStatusFixtures::COFFEE_CHARGE);

        $coffeeMachineMechanic = $this->createTool([$coffeeAction], EquipmentEnum::COFFEE_MACHINE);

        $coffeeMachine = new EquipmentConfig();
        $coffeeMachine
            ->setEquipmentName(EquipmentEnum::COFFEE_MACHINE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics([$coffeeMachineMechanic])
            ->setInitStatuses([$coffeeCharge])
            ->setActions([$repair12, $sabotage12, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($coffeeMachine);
        $manager->persist($coffeeMachineMechanic);

        $cryoModule = new EquipmentConfig();
        $cryoModule
            ->setEquipmentName(EquipmentEnum::CRYO_MODULE)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setActions([$examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($cryoModule);

        /** @var Action $checkSporeLevelAction */
        $checkSporeLevelAction = $this->getReference(ActionsFixtures::CHECK_SPORE_LEVEL);
        $mycoscan = new EquipmentConfig();
        $mycoscan
            ->setEquipmentName(EquipmentEnum::MYCOSCAN)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions([$repair12, $sabotage12, $reportAction, $examineAction, $checkSporeLevelAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($mycoscan);

        /** @var ChargeStatusConfig $turretCharge */
        $turretCharge = $this->getReference(ChargeStatusFixtures::TURRET_CHARGE);

        /** @var Action $shootHunterTurret */
        $shootHunterTurret = $this->getReference(ActionsFixtures::SHOOT_HUNTER_TURRET);

        /** @var Action $shootRandomHunterTurret */
        $shootRandomHunterTurret = $this->getReference(ActionsFixtures::SHOOT_RANDOM_HUNTER_TURRET);

        $turretWeapon = $this->createWeapon([], EquipmentEnum::TURRET_COMMAND);
        $turretWeapon
            ->setBaseDamageRange(
                [
                    2 => 1,
                    3 => 1,
                    4 => 1,
                ]
            )
            ->addAction($shootHunterTurret)
            ->addAction($shootRandomHunterTurret);

        $turretCommand = new EquipmentConfig();
        $turretCommand
            ->setEquipmentName(EquipmentEnum::TURRET_COMMAND)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics([$turretWeapon])
            ->setInitStatuses([$turretCharge])
            ->setActions([$repair12, $sabotage12, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($turretCommand);
        $manager->persist($turretWeapon);

        /** @var Action $selfSurgeryAction */
        $selfSurgeryAction = $this->getReference(ActionsFixtures::SELF_SURGERY);
        $surgicalPlotMechanic = $this->createTool([$selfSurgeryAction], EquipmentEnum::SURGERY_PLOT);
        $surgicalPlot = new EquipmentConfig();
        $surgicalPlot
            ->setEquipmentName(EquipmentEnum::SURGERY_PLOT)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics([$surgicalPlotMechanic])
            ->setActions([$repair12, $sabotage12, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($surgicalPlot);
        $manager->persist($surgicalPlotMechanic);

        $fuelTankMechanic = $this->createTool([$fuelInjectAction], EquipmentEnum::FUEL_TANK);
        $fuelTank = new EquipmentConfig();
        $fuelTank
            ->setEquipmentName(EquipmentEnum::FUEL_TANK)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setMechanics([$fuelTankMechanic])
            ->setActions([$repair25, $sabotage25, $reportAction, $examineAction, $fuelRetrieveAction])
            ->buildName(GameConfigEnum::DEFAULT);
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
            ->setMechanics([$oxygenTankMechanic, $oxygenTankGear])
            ->setActions([$repair25, $sabotage25, $reportAction, $examineAction, $oxygenRetrieveAction])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($oxygenTank);
        $manager->persist($oxygenTankMechanic);
        $manager->persist($oxygenTankGear);

        /** @var Action $dismantle12 */
        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);
        $tabulatrixActions = [
            $dismantle12,
            $repair12,
            $sabotage12,
            $reportAction,
            $examineAction,
        ];
        $tabulatrix = new EquipmentConfig();
        $tabulatrix
            ->setEquipmentName(EquipmentEnum::TABULATRIX)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(true)
            ->setIsBreakable(true)
            ->setActions($tabulatrixActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($tabulatrix);

        $sofaActions = [
            $examineAction,
            $lieDownAction,
            $repair25,
            $sabotage25,
            $dismantle25,
            $reportAction,
        ];
        $swedishSofa = new EquipmentConfig();
        $swedishSofa
            ->setEquipmentName(EquipmentEnum::SWEDISH_SOFA)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setIsBreakable(true)
            ->setActions($sofaActions)
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($swedishSofa);

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
            ->addEquipmentConfig($reactorLateralAlpha)
            ->addEquipmentConfig($reactorLateralBravo)
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
            ->addEquipmentConfig($coffeeMachine)
            ->addEquipmentConfig($cryoModule)
            ->addEquipmentConfig($mycoscan)
            ->addEquipmentConfig($turretCommand)
            ->addEquipmentConfig($surgicalPlot)
            ->addEquipmentConfig($fuelTank)
            ->addEquipmentConfig($oxygenTank)
            ->addEquipmentConfig($tabulatrix)
            ->addEquipmentConfig($swedishSofa)
            ->addEquipmentConfig($auxiliaryTerminal);
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

        /** @var ArrayCollection $modifierConfigs */
        $modifierConfigs = new ArrayCollection($modifierConfigsArray);

        $gear
            ->setModifierConfigs($modifierConfigs)
            ->buildName(EquipmentMechanicEnum::GEAR . '_' . $name, GameConfigEnum::DEFAULT);

        return $gear;
    }

    private function createPatrolShip(array $actions, string $name): PatrolShip
    {
        $patrolShip = new PatrolShip();

        $patrolShip
            ->setActions(new ArrayCollection($actions))
            ->buildName(EquipmentMechanicEnum::PATROL_SHIP . '_' . $name, GameConfigEnum::DEFAULT);

        return $patrolShip;
    }

    private function createTool(array $actions, string $name): Tool
    {
        $tool = new Tool();

        $tool
            ->setActions(new ArrayCollection($actions))
            ->buildName(EquipmentMechanicEnum::TOOL . '_' . $name, GameConfigEnum::DEFAULT);

        return $tool;
    }

    private function createWeapon(array $actions, string $name): Weapon
    {
        $weapon = new Weapon();

        $weapon
            ->setActions(new ArrayCollection($actions))
            ->buildName(EquipmentMechanicEnum::WEAPON . '_' . $name, GameConfigEnum::DEFAULT);

        return $weapon;
    }
}
