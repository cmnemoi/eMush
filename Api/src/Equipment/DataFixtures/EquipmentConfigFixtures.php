<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Action\DataFixtures\TechnicianFixtures;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\SpaceShipConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Entity\Mechanics\Plumbing;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\BreakableTypeEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\DataFixtures\GearModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Status\DataFixtures\ChargeStatusFixtures;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;

/** @codeCoverageIgnore */
class EquipmentConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var ActionConfig $examineAction */
        $examineAction = $this->getReference(ActionsFixtures::EXAMINE_EQUIPMENT);

        /** @var ActionConfig $accessTerminalAction */
        $accessTerminalAction = $this->getReference(ActionsFixtures::ACCESS_TERMINAL);

        /** @var ActionConfig $reportAction */
        $reportAction = $this->getReference(ActionsFixtures::REPORT_EQUIPMENT);

        /** @var ActionConfig $repair3 */
        $repair3 = $this->getReference(TechnicianFixtures::REPAIR_3);

        /** @var ActionConfig $repair6 */
        $repair6 = $this->getReference(TechnicianFixtures::REPAIR_6);

        /** @var ActionConfig $repair12 */
        $repair12 = $this->getReference(TechnicianFixtures::REPAIR_12);

        /** @var ActionConfig $repair25 */
        $repair25 = $this->getReference(TechnicianFixtures::REPAIR_25);

        /** @var ActionConfig $sabotage3 */
        $sabotage3 = $this->getReference(TechnicianFixtures::SABOTAGE_3);

        /** @var ActionConfig $sabotage6 */
        $sabotage6 = $this->getReference(TechnicianFixtures::SABOTAGE_6);

        /** @var ActionConfig $sabotage12 */
        $sabotage12 = $this->getReference(TechnicianFixtures::SABOTAGE_12);

        /** @var ActionConfig $sabotage25 */
        $sabotage25 = $this->getReference(TechnicianFixtures::SABOTAGE_25);

        /** @var ActionConfig $dismantle25 */
        $dismantle25 = $this->getReference(TechnicianFixtures::DISMANTLE_3_25);

        /** @var ActionConfig $exitTerminalAction */
        $exitTerminalAction = $this->getReference(ActionsFixtures::EXIT_TERMINAL);

        /** @var ActionConfig $takeoffToPlanetAction */
        $takeoffToPlanetAction = $this->getReference(ActionsFixtures::TAKEOFF_TO_PLANET);

        /** @TODO terminals */
        /** @var ActionConfig $moveAction */
        $moveAction = $this->getReference(ActionsFixtures::MOVE_DEFAULT);
        $toolDoor = $this->createTool([$moveAction], EquipmentEnum::DOOR);
        $manager->persist($toolDoor);

        /** @TODO terminals */
        $door = new EquipmentConfig();
        $door
            ->setEquipmentName(EquipmentEnum::DOOR)
            ->setBreakableType(BreakableTypeEnum::BREAKABLE)
            ->setActionConfigs([$repair25, $sabotage25, $reportAction, $examineAction])
            ->setMechanics([$toolDoor])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($door);

        /** @var ActionConfig $establishLinkWithSol */
        $establishLinkWithSol = $this->getReference(ActionEnum::ESTABLISH_LINK_WITH_SOL->value);

        /** @var ActionConfig $upgradeNeron */
        $upgradeNeron = $this->getReference(ActionEnum::UPGRADE_NERON->value);

        /** @var ActionConfig $decodeRebelSignal */
        $decodeRebelSignal = $this->getReference(ActionEnum::DECODE_REBEL_SIGNAL->value);

        /** @var ActionConfig $decodeRebelSignal */
        $contactXyloph = $this->getReference(ActionEnum::CONTACT_XYLOPH->value);

        /** @var ActionConfig $acceptTrade */
        $acceptTrade = $this->getReference(ActionEnum::ACCEPT_TRADE->value);

        /** @var ActionConfig $refuseTrade */
        $refuseTrade = $this->getReference(ActionEnum::REFUSE_TRADE->value);

        $commsCenterTool = $this->createTool(
            [
                $establishLinkWithSol,
                $upgradeNeron,
                $decodeRebelSignal,
                $accessTerminalAction,
                $exitTerminalAction,
                $contactXyloph,
                $acceptTrade,
                $refuseTrade,
            ],
            EquipmentEnum::COMMUNICATION_CENTER
        );
        $manager->persist($commsCenterTool);

        $comsCenter = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::COMMUNICATION_CENTER));
        $comsCenter
            ->setActionConfigs([$repair6, $sabotage6, $reportAction, $examineAction])
            ->setMechanics([$commsCenterTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($comsCenter);

        /** @var ActionConfig $participateAction */
        $participateAction = $this->getReference(ActionEnum::PARTICIPATE->value);

        /** @var ActionConfig $putschAction */
        $putschAction = $this->getReference(ActionEnum::PUTSCH->value);

        $neronCoreTool = $this->createTool([$participateAction, $putschAction, $accessTerminalAction, $exitTerminalAction], EquipmentEnum::NERON_CORE);
        $manager->persist($neronCoreTool);

        $neronCore = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::NERON_CORE));
        $neronCore
            ->setActionConfigs([$repair6, $sabotage6, $reportAction, $examineAction])
            ->setMechanics([$neronCoreTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($neronCore);

        $auxiliaryTerminalTool = $this->createTool([$participateAction, $accessTerminalAction, $exitTerminalAction], EquipmentEnum::AUXILIARY_TERMINAL);
        $manager->persist($auxiliaryTerminalTool);

        $auxiliaryTerminal = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::AUXILIARY_TERMINAL));
        $auxiliaryTerminal
            ->setActionConfigs([$repair6, $sabotage6, $reportAction, $examineAction])
            ->setMechanics([$auxiliaryTerminalTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($auxiliaryTerminal);

        /** @var ActionConfig $scanAction */
        $scanAction = $this->getReference(ActionsFixtures::SCAN);

        /** @var ActionConfig $analyzePlanetAction */
        $analyzePlanetAction = $this->getReference(ActionsFixtures::ANALYZE_PLANET);

        /** @var ActionConfig $deletePlanetAction */
        $deletePlanetAction = $this->getReference(ActionsFixtures::DELETE_PLANET);

        $astroTerminalTool = $this->createTool([
            $analyzePlanetAction,
            $deletePlanetAction,
            $exitTerminalAction,
            $scanAction,
            $accessTerminalAction,
        ], EquipmentEnum::ASTRO_TERMINAL);
        $manager->persist($astroTerminalTool);

        $astroTerminal = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::ASTRO_TERMINAL));
        $astroTerminal
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->setMechanics([$astroTerminalTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($astroTerminal);

        /** @var ActionConfig $participateResearchAction */
        $participateResearchAction = $this->getReference(ActionEnum::PARTICIPATE_RESEARCH->value);

        $researchLabTool = $this->createTool([
            $accessTerminalAction,
            $exitTerminalAction,
            $participateResearchAction,
        ], EquipmentEnum::RESEARCH_LABORATORY);
        $manager->persist($researchLabTool);

        $researchLab = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::RESEARCH_LABORATORY));
        $researchLab
            ->setActionConfigs([$repair6, $sabotage6, $reportAction, $examineAction, $participateAction])
            ->setMechanics(mechanics: [$researchLabTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($researchLab);

        /** @var ActionConfig $repairPilgredAction */
        $repairPilgredAction = $this->getReference(ActionEnum::REPAIR_PILGRED->value);

        $pilgredTerminalTool = $this->createTool([$repairPilgredAction], EquipmentEnum::PILGRED);
        $manager->persist($pilgredTerminalTool);

        $pilgred = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::PILGRED));
        $pilgred
            ->setActionConfigs([$examineAction, $accessTerminalAction, $exitTerminalAction])
            ->setMechanics([$pilgredTerminalTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($pilgred);

        /** @var ActionConfig $computeEdenAction */
        $computeEdenAction = $this->getReference(ActionEnum::COMPUTE_EDEN->toString());

        $calculatorTool = $this->createTool([$accessTerminalAction, $exitTerminalAction, $computeEdenAction], EquipmentEnum::CALCULATOR);
        $manager->persist($calculatorTool);

        $calculator = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::CALCULATOR));
        $calculator
            ->setActionConfigs([$repair6, $sabotage6, $reportAction, $examineAction])
            ->setMechanics([$calculatorTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($calculator);

        /** @var ActionConfig $changeNeronCpuPriorityAction */
        $changeNeronCpuPriorityAction = $this->getReference(ActionsFixtures::CHANGE_NERON_CPU_PRIORITY);

        /** @var ActionConfig $changeNeronCrewLockAction */
        $changeNeronCrewLockAction = $this->getReference(ActionEnum::CHANGE_NERON_CREW_LOCK->value);

        /** @var ActionConfig $changeNeronFoodDestructionOption */
        $changeNeronFoodDestructionOption = $this->getReference(ActionEnum::CHANGE_NERON_FOOD_DESTRUCTION_OPTION->value);

        /** @var ActionConfig $togglePlasmaShieldAction */
        $togglePlasmaShieldAction = $this->getReference(ActionEnum::TOGGLE_PLASMA_SHIELD->value);

        /** @var ActionConfig $toggleMagneticNetAction */
        $toggleMagneticNetAction = $this->getReference(ActionEnum::TOGGLE_MAGNETIC_NET->value);

        /** @var ActionConfig $toggleNeronInhibitionAction */
        $toggleNeronInhibitionAction = $this->getReference(ActionEnum::TOGGLE_NERON_INHIBITION->value);

        /** @var ActionConfig $toggleVocodedAnnouncementsAction */
        $toggleVocodedAnnouncementsAction = $this->getReference(ActionEnum::TOGGLE_VOCODED_ANNOUNCEMENTS->value);

        /** @var ActionConfig $toggleDeathAnnouncementsAction */
        $toggleDeathAnnouncementsAction = $this->getReference(ActionEnum::TOGGLE_DEATH_ANNOUNCEMENTS->value);

        $biosTerminalTool = $this->createTool(
            [
                $changeNeronCpuPriorityAction,
                $changeNeronCrewLockAction,
                $changeNeronFoodDestructionOption,
                $togglePlasmaShieldAction,
                $toggleMagneticNetAction,
                $toggleNeronInhibitionAction,
                $toggleVocodedAnnouncementsAction,
                $toggleDeathAnnouncementsAction,
                $accessTerminalAction,
                $exitTerminalAction,
            ],
            EquipmentEnum::BIOS_TERMINAL
        );
        $manager->persist($biosTerminalTool);

        $biosTerminal = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::BIOS_TERMINAL));
        $biosTerminal
            ->setActionConfigs([$repair3, $sabotage3, $reportAction, $examineAction])
            ->setMechanics([$biosTerminalTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($biosTerminal);

        /** @var ActionConfig $hackAction */
        $hackAction = $this->getReference(ActionsFixtures::HACK);

        /** @var ActionConfig $advanceDaedalusAction */
        $advanceDaedalusAction = $this->getReference(ActionsFixtures::ADVANCE_DAEDALUS);

        /** @var ActionConfig $turnDaedalusLeftAction */
        $turnDaedalusLeftAction = $this->getReference(ActionsFixtures::TURN_DAEDALUS_LEFT);

        /** @var ActionConfig $turnDaedalusRightAction */
        $turnDaedalusRightAction = $this->getReference(ActionsFixtures::TURN_DAEDALUS_RIGHT);

        /** @var ActionConfig $leaveOrbitAction */
        $leaveOrbitAction = $this->getReference(ActionsFixtures::LEAVE_ORBIT);

        /** @var ActionConfig $returnToSolAction */
        $returnToSolAction = $this->getReference(ActionEnum::RETURN_TO_SOL->value);

        /** @var ActionConfig $travelToEdenAction */
        $travelToEdenAction = $this->getReference(ActionEnum::TRAVEL_TO_EDEN->value);

        $toolCommandTerminal = $this->createTool([
            $hackAction,
            $exitTerminalAction,
            $advanceDaedalusAction,
            $turnDaedalusLeftAction,
            $turnDaedalusRightAction,
            $leaveOrbitAction,
            $accessTerminalAction,
            $returnToSolAction,
            $travelToEdenAction,
        ], EquipmentEnum::COMMAND_TERMINAL);
        $manager->persist($toolCommandTerminal);
        $commandTerminal = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::COMMAND_TERMINAL));
        $commandTerminal
            ->setActionConfigs([
                $repair12,
                $sabotage12,
                $reportAction,
                $examineAction,
            ])
            ->setMechanics([$toolCommandTerminal])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($commandTerminal);

        $planetScannerGear = $this->createGear([GearModifierConfigFixtures::PLANET_SCANNER_MODIFIER], EquipmentEnum::PLANET_SCANNER);
        $manager->persist($planetScannerGear);

        /** @TODO gears */
        $planetScanner = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::PLANET_SCANNER));
        $planetScanner
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->setMechanics([$planetScannerGear])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($planetScanner);

        $quantumSensorsPlanetScannerGear = $this->createGear(
            [
                GearModifierConfigFixtures::PLANET_SCANNER_MODIFIER,
                'modifier_for_daedalus_+1sector_revealed_on_action_analyze_planet',
            ],
            EquipmentEnum::QUANTUM_SENSORS_PLANET_SCANNER
        );
        $manager->persist($quantumSensorsPlanetScannerGear);

        $quantumSensorsPlanetScanner = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::QUANTUM_SENSORS_PLANET_SCANNER));
        $quantumSensorsPlanetScanner
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->setMechanics([$quantumSensorsPlanetScannerGear])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($quantumSensorsPlanetScanner);

        /** @var StatusConfig $jukeboxSongStatus */
        $jukeboxSongStatus = $this->getReference(EquipmentStatusEnum::JUKEBOX_SONG);

        $jukebox = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::JUKEBOX));
        $jukebox
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->setInitStatuses([$jukeboxSongStatus])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($jukebox);

        $emergencyReactor = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::EMERGENCY_REACTOR));
        $emergencyReactor
            ->setActionConfigs([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($emergencyReactor);

        $reactorLateralAlpha = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::REACTOR_LATERAL_ALPHA));
        $reactorLateralAlpha
            ->setActionConfigs([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($reactorLateralAlpha);

        $reactorLateralBravo = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::REACTOR_LATERAL_BRAVO));
        $reactorLateralBravo
            ->setActionConfigs([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($reactorLateralBravo);

        $antennaGear = $this->createGear([GearModifierConfigFixtures::ANTENNA_MODIFIER], EquipmentEnum::ANTENNA);

        $antenna = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::ANTENNA));
        $antenna
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->setMechanics([$antennaGear])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($antenna);
        $manager->persist($antennaGear);

        $radarTransVoidAntennaGear = $this->createGear(
            [
                GearModifierConfigFixtures::ANTENNA_MODIFIER,
                'modifier_for_daedalus_x2_signal_on_action_contact_sol',
            ],
            EquipmentEnum::RADAR_TRANS_VOID_ANTENNA
        );
        $manager->persist($radarTransVoidAntennaGear);

        $radarTransVoidAntenna = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::RADAR_TRANS_VOID_ANTENNA));
        $radarTransVoidAntenna
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->setMechanics([$radarTransVoidAntennaGear])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($radarTransVoidAntenna);
        $manager->persist($radarTransVoidAntennaGear);

        $gravitySimulator = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::GRAVITY_SIMULATOR));
        $gravitySimulator
            ->setActionConfigs([$repair6, $sabotage6, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($gravitySimulator);

        /** @var ActionConfig $showerAction */
        $showerAction = $this->getReference(ActionsFixtures::SHOWER_DEFAULT);
        $plumbingShower = $this->createPlumbing([$showerAction], EquipmentEnum::SHOWER);
        $plumbingShower->setWaterDamage([3 => 1, 4 => 1]);
        $manager->persist($plumbingShower);

        $thalassoGear = $this->createGear(
            [
                ModifierNameEnum::THALASSO_HEALTH_POINTS_MODIFIER,
                ModifierNameEnum::THALASSO_MORALE_POINTS_MODIFIER,
                ModifierNameEnum::THALASSO_MOVEMENT_POINTS_MODIFIER,
            ],
            EquipmentEnum::THALASSO
        );
        $manager->persist($thalassoGear);

        $thalasso = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::THALASSO));
        $thalasso
            ->setActionConfigs([$repair25, $dismantle25, $examineAction, $reportAction])
            ->setMechanics([$plumbingShower, $thalassoGear])
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($thalasso);

        /** @TODO ships */
        /** @var ActionConfig $takeoffAction */
        $takeoffAction = $this->getReference(ActionsFixtures::TAKEOFF);

        /** @var ActionConfig $landAction */
        $landAction = $this->getReference(ActionsFixtures::LAND);

        /** @var ActionConfig $shootHunterPatrolShipAction */
        $shootHunterPatrolShipAction = $this->getReference(ActionsFixtures::SHOOT_HUNTER_PATROL_SHIP);

        /** @var ActionConfig $renovateAction */
        $renovateAction = $this->getReference(ActionsFixtures::RENOVATE);

        /** @var ActionConfig $collectScrap */
        $collectScrap = $this->getReference(ActionsFixtures::COLLECT_SCRAP);

        /** @var ChargeStatusConfig $patrolShipChargeStatus */
        $patrolShipChargeStatus = $this->getReference(ChargeStatusFixtures::PATROLLER_CHARGE);

        /** @var ChargeStatusConfig $patrolShipArmorStatus */
        $patrolShipArmorStatus = $this->getReference(ChargeStatusFixtures::PATROL_SHIP_ARMOR);

        $icarus = new SpaceShipConfig();
        $icarus
            ->setCollectScrapNumber([])
            ->setCollectScrapPatrolShipDamage([])
            ->setCollectScrapPlayerDamage([])
            ->setFailedManoeuvreDaedalusDamage([])
            ->setFailedManoeuvrePatrolShipDamage([])
            ->setFailedManoeuvrePlayerDamage([])
            ->setNumberOfExplorationSteps(9)
            ->setEquipmentName(EquipmentEnum::ICARUS)
            ->setBreakableType(BreakableTypeEnum::NONE)
            ->setActionConfigs([$examineAction, $takeoffToPlanetAction])
            ->setMechanics([])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($icarus);

        /** @var ActionConfig $shootHunterRandomPatrolShipAction */
        $shootHunterRandomPatrolShipAction = $this->getReference(ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP->value);

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
            ->setBaseAccuracy(40)
            ->addAction($shootHunterPatrolShipAction)
            ->addAction($shootHunterRandomPatrolShipAction);

        $patrolShip = new SpaceShipConfig();
        $patrolShip
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
            ->setEquipmentName(EquipmentEnum::PATROL_SHIP)
            ->setBreakableType(BreakableTypeEnum::BREAKABLE)
            ->setActionConfigs([$sabotage12, $examineAction, $renovateAction, $takeoffAction, $landAction, $collectScrap, $takeoffToPlanetAction])
            ->setMechanics([$patrolShipWeapon])
            ->setInitStatuses([$patrolShipChargeStatus, $patrolShipArmorStatus])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($patrolShipWeapon);
        $manager->persist($patrolShip);

        /** @var ChargeStatusConfig $pasiphaeArmor */
        $pasiphaeArmor = $this->getReference(ChargeStatusFixtures::PASIPHAE_ARMOR);
        $pasiphae = new SpaceShipConfig();
        $pasiphae
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
            ->setEquipmentName(EquipmentEnum::PASIPHAE)
            ->setBreakableType(BreakableTypeEnum::BREAKABLE)
            ->setActionConfigs([$sabotage12, $examineAction, $renovateAction, $takeoffAction, $landAction, $collectScrap, $takeoffToPlanetAction])
            ->setMechanics([])
            ->setInitStatuses([$pasiphaeArmor])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($pasiphae);

        /** @var ActionConfig $removeCamera */
        $removeCamera = $this->getReference(ActionsFixtures::REMOVE_CAMERA);

        $camera = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::CAMERA_EQUIPMENT));
        $camera
            ->setActionConfigs([$dismantle25, $repair25, $sabotage25, $reportAction, $examineAction, $removeCamera])
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($camera);

        /** @var ActionConfig $fuelInjectAction */
        $fuelInjectAction = $this->getReference(ActionsFixtures::FUEL_INJECT);

        /** @var ActionConfig $fuelRetrieveAction */
        $fuelRetrieveAction = $this->getReference(ActionsFixtures::FUEL_RETRIEVE);

        /** @var ActionConfig $retrieveFuelChamberAction */
        $retrieveFuelChamberAction = $this->getReference(ActionsFixtures::RETRIEVE_FUEL_CHAMBER);

        /** @var ActionConfig $checkFuelChamberLevelAction */
        $checkFuelChamberLevelAction = $this->getReference(ActionsFixtures::CHECK_FUEL_CHAMBER_LEVEL);

        $toolCombustionChamber = $this->createTool([$retrieveFuelChamberAction, $checkFuelChamberLevelAction], EquipmentEnum::COMBUSTION_CHAMBER);
        $manager->persist($toolCombustionChamber);
        // Tools
        $combustionChamber = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::COMBUSTION_CHAMBER));
        $combustionChamber
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->setMechanics([$toolCombustionChamber])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($combustionChamber);

        /** @var ActionConfig $cookAction */
        $cookAction = $this->getReference(ActionsFixtures::COOK_DEFAULT);

        /** @var ActionConfig $washAction */
        $washAction = $this->getReference(ActionsFixtures::WASH_IN_SINK);

        $kitchenMechanic = $this->createPlumbing([$cookAction, $washAction], EquipmentEnum::KITCHEN);
        $kitchenMechanic->setWaterDamage([3 => 1, 4 => 1]);

        /** @var ChargeStatusConfig $sinkCharge */
        $sinkCharge = $this->getReference(ChargeStatusFixtures::SINK_CHARGE);

        $kitchen = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::KITCHEN));
        $kitchen
            ->setInitStatuses([$sinkCharge])
            ->setMechanics([$kitchenMechanic])
            ->setActionConfigs([
                $repair12,
                $sabotage12,
                $reportAction,
                $examineAction,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($kitchen);
        $manager->persist($kitchenMechanic);

        $sncKitchenMechanic = $this->createTool([$cookAction, $washAction], EquipmentEnum::SNC_KITCHEN);

        $sncKitchen = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::SNC_KITCHEN));
        $sncKitchen
            ->setInitStatuses([$sinkCharge])
            ->setMechanics([$sncKitchenMechanic])
            ->setActionConfigs([
                $repair12,
                $sabotage12,
                $reportAction,
                $examineAction,
            ])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($sncKitchen);
        $manager->persist($sncKitchenMechanic);

        /** @var ActionConfig $dispenseAction */
        $dispenseAction = $this->getReference(ActionsFixtures::DISPENSE_DRUG);

        /** @var ChargeStatusConfig $dispenserCharge */
        $dispenserCharge = $this->getReference(ChargeStatusFixtures::DISPENSER_CHARGE);

        $distillerMechanic = $this->createTool([$dispenseAction], EquipmentEnum::NARCOTIC_DISTILLER);

        $narcoticDistiller = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::NARCOTIC_DISTILLER));
        $narcoticDistiller
            ->setInitStatuses([$dispenserCharge])
            ->setMechanics([$distillerMechanic])
            ->setActionConfigs([$dismantle25, $examineAction])
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::METAL_SCRAPS => 2])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($narcoticDistiller);
        $manager->persist($distillerMechanic);

        $shower = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::SHOWER));
        $shower
            ->setActionConfigs([$repair25, $dismantle25, $examineAction])
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1, ItemEnum::THICK_TUBE => 1])
            ->setMechanics([$plumbingShower])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($shower);

        /** @var ActionConfig $playArcadeAction */
        $playArcadeAction = $this->getReference(ActionsFixtures::PLAY_ARCADE);
        $toolArcade = $this->createTool([$playArcadeAction], EquipmentEnum::DYNARCADE);
        $manager->persist($toolArcade);

        $dynarcadeActions = [$repair12, $sabotage12, $reportAction, $examineAction];
        $dynarcade = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::DYNARCADE));
        $dynarcade
            ->setActionConfigs($dynarcadeActions)
            ->setMechanics([$toolArcade])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($dynarcade);

        /** @var ActionConfig $lieDownAction */
        $lieDownAction = $this->getReference(ActionsFixtures::LIE_DOWN);
        $toolBed = $this->createTool([$lieDownAction], EquipmentEnum::BED);
        $manager->persist($toolBed);

        $bed = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::BED));
        $bed
            ->setActionConfigs([$examineAction])
            ->setMechanics([$toolBed])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($bed);

        $medlabBed = new EquipmentConfig();
        $medlabBed
            ->setEquipmentName(EquipmentEnum::MEDLAB_BED)
            ->setBreakableType(BreakableTypeEnum::NONE)
            ->setActionConfigs([$examineAction])
            ->setMechanics([$toolBed])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($medlabBed);

        /** @var ActionConfig $coffeeAction */
        $coffeeAction = $this->getReference(ActionsFixtures::COFFEE_DEFAULT);

        /** @var ChargeStatusConfig $coffeeCharge */
        $coffeeCharge = $this->getReference(ChargeStatusFixtures::COFFEE_CHARGE);

        $coffeeMachineMechanic = $this->createTool([$coffeeAction], EquipmentEnum::COFFEE_MACHINE);

        $coffeeMachine = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::COFFEE_MACHINE));
        $coffeeMachine
            ->setMechanics([$coffeeMachineMechanic])
            ->setInitStatuses([$coffeeCharge])
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($coffeeMachine);
        $manager->persist($coffeeMachineMechanic);

        $cryoModule = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::CRYO_MODULE));
        $cryoModule
            ->setActionConfigs([$examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($cryoModule);

        /** @var ActionConfig $checkSporeLevelAction */
        $checkSporeLevelAction = $this->getReference(ActionsFixtures::CHECK_SPORE_LEVEL);
        $toolMycoscan = $this->createTool([$checkSporeLevelAction], EquipmentEnum::MYCOSCAN);
        $manager->persist($toolMycoscan);

        $mycoscan = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::MYCOSCAN));
        $mycoscan
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->setMechanics([$toolMycoscan])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($mycoscan);

        /** @var ChargeStatusConfig $turretCharge */
        $turretCharge = $this->getReference(ChargeStatusFixtures::TURRET_CHARGE);

        /** @var ActionConfig $shootHunterTurret */
        $shootHunterTurret = $this->getReference(ActionsFixtures::SHOOT_HUNTER_TURRET);

        /** @var ActionConfig $shootRandomHunterTurret */
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

        $turretCommand = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::TURRET_COMMAND));
        $turretCommand
            ->setMechanics([$turretWeapon])
            ->setInitStatuses([$turretCharge])
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($turretCommand);
        $manager->persist($turretWeapon);

        /** @var ActionConfig $selfSurgeryAction */
        $selfSurgeryAction = $this->getReference(ActionsFixtures::SELF_SURGERY);
        $surgicalPlotMechanic = $this->createTool([$selfSurgeryAction], EquipmentEnum::SURGERY_PLOT);
        $surgicalPlot = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::SURGERY_PLOT));
        $surgicalPlot
            ->setMechanics([$surgicalPlotMechanic])
            ->setActionConfigs([$repair12, $sabotage12, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($surgicalPlot);
        $manager->persist($surgicalPlotMechanic);

        $fuelTankMechanic = $this->createTool([$fuelInjectAction, $fuelRetrieveAction], EquipmentEnum::FUEL_TANK);
        $fuelTank = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::FUEL_TANK));
        $fuelTank
            ->setMechanics([$fuelTankMechanic])
            ->setActionConfigs([$repair25, $sabotage25, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($fuelTank);
        $manager->persist($fuelTankMechanic);

        /** @var ActionConfig $oxygenInjectAction */
        $oxygenInjectAction = $this->getReference(ActionsFixtures::OXYGEN_INJECT);

        /** @var ActionConfig $oxygenRetrieveAction */
        $oxygenRetrieveAction = $this->getReference(ActionsFixtures::OXYGEN_RETRIEVE);

        $oxygenTankMechanic = $this->createTool([$oxygenInjectAction, $oxygenRetrieveAction], EquipmentEnum::OXYGEN_TANK);

        $oxygenTankGear = $this->createGear([GearModifierConfigFixtures::OXYGEN_TANK_MODIFIER], EquipmentEnum::OXYGEN_TANK);

        $oxygenTank = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::OXYGEN_TANK));
        $oxygenTank
            ->setMechanics([$oxygenTankMechanic, $oxygenTankGear])
            ->setActionConfigs([$repair25, $sabotage25, $reportAction, $examineAction])
            ->buildName(GameConfigEnum::DEFAULT);

        $manager->persist($oxygenTank);
        $manager->persist($oxygenTankMechanic);
        $manager->persist($oxygenTankGear);

        /** @var ActionConfig $dismantle12 */
        $dismantle12 = $this->getReference(TechnicianFixtures::DISMANTLE_3_12);

        /** @var ActionConfig $printZeListAction */
        $printZeListAction = $this->getReference(ActionEnum::PRINT_ZE_LIST->value);

        $tabulatrixTool = $this->createTool([$printZeListAction], EquipmentEnum::TABULATRIX);
        $manager->persist($tabulatrixTool);

        $tabulatrixActions = [
            $dismantle12,
            $repair12,
            $sabotage12,
            $reportAction,
            $examineAction,
        ];
        $tabulatrix = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::TABULATRIX));
        $tabulatrix
            ->setActionConfigs($tabulatrixActions)
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
            ->setMechanics([$tabulatrixTool])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($tabulatrix);

        $sofaActions = [
            $examineAction,
            $repair25,
            $sabotage25,
            $dismantle25,
            $reportAction,
        ];
        $swedishSofa = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::SWEDISH_SOFA));
        $swedishSofa
            ->setActionConfigs($sofaActions)
            ->setMechanics([$toolBed])
            ->setDismountedProducts([ItemEnum::PLASTIC_SCRAPS => 1])
            ->buildName(GameConfigEnum::DEFAULT);
        $manager->persist($swedishSofa);

        $hydroponicIncubatorGear = $this->createGear(
            [
                'modifier_for_place_x2_maturation_time',
            ],
            EquipmentEnum::HYDROPONIC_INCUBATOR
        );
        $manager->persist($hydroponicIncubatorGear);

        $hydroponicIncubator = EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::HYDROPONIC_INCUBATOR));
        $hydroponicIncubator->setMechanics([$hydroponicIncubatorGear]);
        $manager->persist($hydroponicIncubator);

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
            ->addEquipmentConfig($quantumSensorsPlanetScanner)
            ->addEquipmentConfig($jukebox)
            ->addEquipmentConfig($emergencyReactor)
            ->addEquipmentConfig($reactorLateralAlpha)
            ->addEquipmentConfig($reactorLateralBravo)
            ->addEquipmentConfig($antenna)
            ->addEquipmentConfig($radarTransVoidAntenna)
            ->addEquipmentConfig($gravitySimulator)
            ->addEquipmentConfig($thalasso)
            ->addEquipmentConfig($patrolShip)
            ->addEquipmentConfig($pasiphae)
            ->addEquipmentConfig($camera)
            ->addEquipmentConfig($combustionChamber)
            ->addEquipmentConfig($kitchen)
            ->addEquipmentConfig($sncKitchen)
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
            ->addEquipmentConfig($auxiliaryTerminal)
            ->addEquipmentConfig($hydroponicIncubator);
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

    private function createPlumbing(array $actions, string $name): Plumbing
    {
        $plumbing = new Plumbing();

        $plumbing
            ->setActions(new ArrayCollection($actions))
            ->buildName(EquipmentMechanicEnum::PLUMBING . '_' . $name, GameConfigEnum::DEFAULT);

        return $plumbing;
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
