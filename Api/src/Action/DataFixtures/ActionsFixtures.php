<?php

declare(strict_types=1);

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;

class ActionsFixtures extends Fixture
{
    public const string SUICIDE = 'suicide';
    public const string AUTO_DESTROY = 'auto_destruction';
    public const string KILL_PLAYER = 'kill_player';
    public const string USURP_IDENTITY = 'usurp_identity';
    public const string RESET_SKILL_POINT = ActionEnum::RESET_SKILL_POINTS->value;
    public const string REJUVENATE_ALPHA = 'rejuvenate_alpha';
    public const string UPDATING_TALKIE = 'updating_talkie';
    public const string MOVE_DEFAULT = 'move_default';
    public const string SEARCH_DEFAULT = 'search_default';
    public const string HIT_DEFAULT = 'hit_default';
    public const string HIDE_DEFAULT = 'hide_default';
    public const string DEFAULT_TAKE = 'default_take';
    public const string DEFAULT_DROP = 'default_drop';
    public const string DO_THE_THING = 'do_the_thing';
    public const string DRUG_CONSUME = 'drug_consume';
    public const string RATION_CONSUME = 'ration_consume';
    public const string BUILD_DEFAULT = 'build_default';
    public const string READ_DOCUMENT = 'read_document';
    public const string READ_BOOK = 'read_book';
    public const string ATTACK_DEFAULT = 'attack_default';
    public const string EXTINGUISH_DEFAULT = 'extinguish_default';
    public const string TRY_KUBE = 'try_kube';
    public const string OPEN_SPACE_CAPSULE = 'open_space_capsule';
    public const string INJECT_SERUM = 'inject_serum';
    public const string BANDAGE_DEFAULT = 'bandage_default';
    public const string COOK_EXPRESS = 'cook_express';
    public const string COOK_DEFAULT = 'cook_default';
    public const string HEAL = 'heal';
    public const string SELF_HEAL = 'self.heal';
    public const string HEAL_ULTRA = 'heal.ultra';
    public const string COMFORT = 'comfort';
    public const string WRITE = 'write';
    public const string SHRED = 'shred';
    public const string GAG_DEFAULT = 'gag_default';
    public const string UNGAG_DEFAULT = 'ungag_default';
    public const string HYPERFREEZE_DEFAULT = 'hyperfreeze_default';
    public const string SHOWER_DEFAULT = 'shower_default';
    public const string WASH_IN_SINK = 'wash_in_sink';
    public const string FLIRT_DEFAULT = 'flirt_default';
    public const string FUEL_INJECT = 'fuel_inject';
    public const string FUEL_RETRIEVE = 'fuel_retrieve';
    public const string OXYGEN_INJECT = 'oxygen_inject';
    public const string STRENGTHEN_HULL = 'strength_hull';
    public const string OXYGEN_RETRIEVE = 'oxygen_retrieve';
    public const string LIE_DOWN = 'lie_down';
    public const string GET_UP = 'get_up';
    public const string COFFEE_DEFAULT = 'coffee_default';
    public const string DISPENSE_DRUG = 'dispense_drug';
    public const string TRANSPLANT = 'transplant';
    public const string TREAT_PLANT = 'treat_plant';
    public const string WATER_PLANT = 'water_plant';
    public const string REPORT_EQUIPMENT = 'report_equipment';
    public const string REPORT_FIRE = 'report_fire';
    public const string INSTALL_CAMERA = 'install_camera';
    public const string REMOVE_CAMERA = 'remove_camera';
    public const string CHECK_SPORE_LEVEL = 'check_spore_level';
    public const string EXAMINE_EQUIPMENT = 'examine_equipment';
    public const string PUBLIC_BROADCAST = 'public_broadcast';
    public const string EXTINGUISH_MANUALLY = 'extinguish_manually';
    public const string MOTIVATIONAL_SPEECH = 'motivational_speech';
    public const string BORING_SPEECH = 'boring_speech';
    public const string SURGERY = 'surgery';
    public const string SELF_SURGERY = 'self_surgery';
    public const string SHOOT = 'shoot';
    public const string PLAY_ARCADE = 'play_arcade';
    public const string SHOOT_HUNTER_TURRET = 'shoot_hunter_turret';
    public const string SHOOT_RANDOM_HUNTER_TURRET = 'shoot_random_hunter_turret';
    public const string TAKEOFF = 'takeoff';
    public const string ACCESS_TERMINAL = 'access_terminal';
    public const string LAND = 'land';
    public const string SHOOT_HUNTER_PATROL_SHIP = 'shoot_hunter_patrol_ship';
    public const string SHOOT_RANDOM_HUNTER_PATROL_SHIP = 'shoot_random_hunter_patrol_ship';
    public const string COLLECT_SCRAP = 'collect_scrap';
    public const string RENOVATE = 'renovate';
    public const string CONVERT_ACTION_TO_MOVEMENT = 'convert_action_to_movement';
    public const string AUTO_EJECT = 'auto_eject';
    public const string INSERT_FUEL_CHAMBER = 'insert_fuel_chamber';
    public const string RETRIEVE_FUEL_CHAMBER = 'retrieve_fuel_chamber';
    public const string CHECK_FUEL_CHAMBER_LEVEL = 'check_fuel_chamber_level';
    public const string HACK = 'hack';
    public const string EXIT_TERMINAL = 'exit_terminal';
    public const string ADVANCE_DAEDALUS = 'advance_daedalus';
    public const string SCAN = 'scan';
    public const string ANALYZE_PLANET = 'analyze_planet';
    public const string TURN_DAEDALUS_LEFT = 'turn_daedalus_left';
    public const string TURN_DAEDALUS_RIGHT = 'turn_daedalus_right';
    public const string DELETE_PLANET = 'delete_planet';
    public const string LEAVE_ORBIT = 'leave_orbit';
    public const string TAKEOFF_TO_PLANET = 'takeoff_to_planet';
    public const string TAKEOFF_TO_PLANET_PATROL_SHIP = 'takeoff_to_planet_patrol_ship';
    public const string CHANGE_NERON_CPU_PRIORITY = 'change_neron_cpu_priority';
    public const string OPEN_CONTAINER_COST_0 = 'open_container_cost_0';

    public function load(ObjectManager $manager): void
    {
        $suicide = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SUICIDE));
        $manager->persist($suicide);

        $forceCycleChange = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::FORCE_CYCLE_CHANGE));
        $manager->persist($forceCycleChange);

        $autoDestroy = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::AUTO_DESTROY));
        $manager->persist($autoDestroy);

        $killPlayer = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::KILL_PLAYER));
        $manager->persist($killPlayer);

        $usurpIdentity = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::USURP_IDENTITY));
        $manager->persist($usurpIdentity);

        $rejuvenateAlpha = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REJUVENATE));
        $manager->persist($rejuvenateAlpha);

        $resetSpecializationPoint = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::RESET_SKILL_POINTS));
        $manager->persist($resetSpecializationPoint);

        $updatingTalkie = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPDATE_TALKIE));

        $manager->persist($updatingTalkie);

        $moveAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::MOVE));
        $manager->persist($moveAction);

        $searchAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SEARCH));
        $manager->persist($searchAction);

        $hitAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::HIT));
        $manager->persist($hitAction);

        $hideAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::HIDE));
        $manager->persist($hideAction);

        $takeItemAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TAKE));

        $manager->persist($takeItemAction);

        $dropItemAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DROP));

        $manager->persist($dropItemAction);

        $rationConsumeAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CONSUME));

        $manager->persist($rationConsumeAction);

        $drugConsumeAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CONSUME_DRUG));

        $manager->persist($drugConsumeAction);

        $buildAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::BUILD));

        $manager->persist($buildAction);

        $readAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::READ_BOOK));

        $manager->persist($readAction);

        $readDocument = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::READ_DOCUMENT));

        $manager->persist($readDocument);

        $attackAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ATTACK));

        $manager->persist($attackAction);

        $extinguishAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::EXTINGUISH));
        $manager->persist($extinguishAction);

        $tryKubeAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TRY_KUBE));

        $manager->persist($tryKubeAction);

        $openSpaceCapsuleAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::OPEN));

        $manager->persist($openSpaceCapsuleAction);

        $injectSerumAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CURE));

        $manager->persist($injectSerumAction);

        $bandageAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::USE_BANDAGE));

        $manager->persist($bandageAction);

        $expressCookAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::EXPRESS_COOK));

        $manager->persist($expressCookAction);

        $cookAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COOK));
        $manager->persist($cookAction);

        $selfHealAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SELF_HEAL));
        $manager->persist($selfHealAction);

        $healAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::HEAL));
        $manager->persist($healAction);

        $comfortAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COMFORT));
        $manager->persist($comfortAction);

        $ultraHealAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ULTRAHEAL));

        $manager->persist($ultraHealAction);

        $writeAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::WRITE));

        $manager->persist($writeAction);

        $shredAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHRED));

        $manager->persist($shredAction);

        $hyperfreezeAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::HYPERFREEZE));
        $manager->persist($hyperfreezeAction);

        $gagAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GAG));

        $manager->persist($gagAction);

        $ungagAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UNGAG));

        $manager->persist($ungagAction);

        $showerAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TAKE_SHOWER));

        $manager->persist($showerAction);

        $sinkAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::WASH_IN_SINK));

        $manager->persist($sinkAction);

        $washWithPerfumeAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::WASH_WITH_PERFUME));

        $manager->persist($washWithPerfumeAction);

        $fuelInjectAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::INSERT_FUEL));

        $manager->persist($fuelInjectAction);

        $retrieveFuelAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::RETRIEVE_FUEL));

        $manager->persist($retrieveFuelAction);

        $oxygenInjectAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::INSERT_OXYGEN));

        $manager->persist($oxygenInjectAction);

        $retrieveOxygenAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::RETRIEVE_OXYGEN));

        $manager->persist($retrieveOxygenAction);

        $strengthenHullAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::STRENGTHEN_HULL));

        $manager->persist($strengthenHullAction);

        $lieDownAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::LIE_DOWN));

        $manager->persist($lieDownAction);

        $getUpAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GET_UP));

        $manager->persist($getUpAction);

        $coffeeAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COFFEE));

        $manager->persist($coffeeAction);

        $dispenseAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DISPENSE));

        $manager->persist($dispenseAction);

        $transplantAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TRANSPLANT));
        $manager->persist($transplantAction);

        $treatPlantAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TREAT_PLANT));
        $manager->persist($treatPlantAction);

        $waterPlantAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::WATER_PLANT));
        $manager->persist($waterPlantAction);

        $reportEquipmentAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REPORT_EQUIPMENT));

        $manager->persist($reportEquipmentAction);

        $reportFireAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REPORT_FIRE));

        $manager->persist($reportFireAction);

        $installCameraAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::INSTALL_CAMERA));
        $manager->persist($installCameraAction);

        $removeCameraAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REMOVE_CAMERA));
        $manager->persist($removeCameraAction);

        $examineEquipmentAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::EXAMINE));
        $manager->persist($examineEquipmentAction);

        $checkSporeLevelAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CHECK_SPORE_LEVEL));
        $manager->persist($checkSporeLevelAction);

        $flirtAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::FLIRT));

        $manager->persist($flirtAction);

        $bondAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::BOND));
        $manager->persist($bondAction);

        $doTheThingAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DO_THE_THING));

        $manager->persist($doTheThingAction);

        $relaxAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::RELAX));
        $manager->persist($relaxAction);

        $removeSporeAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REMOVE_SPORE));
        $manager->persist($removeSporeAction);

        $publicBroadcastAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PUBLIC_BROADCAST));

        $manager->persist($publicBroadcastAction);

        $extinguishManuallyAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::EXTINGUISH_MANUALLY));

        $manager->persist($extinguishManuallyAction);

        $motivationalSpeechAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::MOTIVATIONAL_SPEECH));
        $manager->persist($motivationalSpeechAction);

        $boringSpeechAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::BORING_SPEECH));

        $manager->persist($boringSpeechAction);

        $surgeryAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SURGERY));
        $manager->persist($surgeryAction);

        $selfSurgeryAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SELF_SURGERY));
        $manager->persist($selfSurgeryAction);

        $shootAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHOOT));
        $manager->persist($shootAction);

        $playArcade = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PLAY_ARCADE));
        $manager->persist($playArcade);

        $shootHunterTurret = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHOOT_HUNTER_TURRET));
        $manager->persist($shootHunterTurret);

        $shootRandomHunterTurret = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHOOT_RANDOM_HUNTER_TURRET));
        $manager->persist($shootRandomHunterTurret);

        $takeoff = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TAKEOFF));
        $manager->persist($takeoff);

        $accessTerminal = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ACCESS_TERMINAL));
        $manager->persist($accessTerminal);

        $land = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::LAND));
        $manager->persist($land);

        $shootHunterPatrolShip = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHOOT_HUNTER_PATROL_SHIP));
        $manager->persist($shootHunterPatrolShip);

        $shootRandomHunterPatrolShip = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP));
        $manager->persist($shootRandomHunterPatrolShip);

        $collectScrap = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COLLECT_SCRAP));
        $manager->persist($collectScrap);

        $renovate = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::RENOVATE));
        $manager->persist($renovate);

        $convertActionToMovement = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT));
        $manager->persist($convertActionToMovement);

        $autoEject = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::AUTO_EJECT));
        $manager->persist($autoEject);

        $insertFuelChamber = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::INSERT_FUEL_CHAMBER));
        $manager->persist($insertFuelChamber);

        $retrieveFuelChamber = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::RETRIEVE_FUEL_CHAMBER));
        $manager->persist($retrieveFuelChamber);

        $checkFuelChamberLevel = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CHECK_FUEL_CHAMBER_LEVEL));
        $manager->persist($checkFuelChamberLevel);

        $hack = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::HACK));
        $manager->persist($hack);

        $exitTerminal = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::EXIT_TERMINAL));
        $manager->persist($exitTerminal);

        $advanceDaedalus = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ADVANCE_DAEDALUS));
        $manager->persist($advanceDaedalus);

        $scan = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SCAN));
        $manager->persist($scan);

        $analyzePlanet = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ANALYZE_PLANET));
        $manager->persist($analyzePlanet);

        $turnDaedalusLeft = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TURN_DAEDALUS_LEFT));
        $manager->persist($turnDaedalusLeft);

        $turnDaedalusRight = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TURN_DAEDALUS_RIGHT));
        $manager->persist($turnDaedalusRight);

        $deletePlanet = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DELETE_PLANET));
        $manager->persist($deletePlanet);

        $leaveOrbit = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::LEAVE_ORBIT));
        $manager->persist($leaveOrbit);

        $takeoffToPlanet = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TAKEOFF_TO_PLANET));
        $manager->persist($takeoffToPlanet);

        $takeoffToPlanetPatrolShip = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TAKEOFF_TO_PLANET_PATROL_SHIP));
        $manager->persist($takeoffToPlanetPatrolShip);

        $changeNeronCpuPriority = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CHANGE_NERON_CPU_PRIORITY));
        $manager->persist($changeNeronCpuPriority);

        $repairPilgred = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REPAIR_PILGRED));
        $manager->persist($repairPilgred);

        $returnToSol = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::RETURN_TO_SOL));
        $manager->persist($returnToSol);

        $participate = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PARTICIPATE));
        $manager->persist($participate);

        $changeNeronCrewLock = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CHANGE_NERON_CREW_LOCK));
        $manager->persist($changeNeronCrewLock);

        $changeNeronFoodDestructionOption = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CHANGE_NERON_FOOD_DESTRUCTION_OPTION));
        $manager->persist($changeNeronFoodDestructionOption);

        $togglePlasmaShield = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TOGGLE_PLASMA_SHIELD));
        $manager->persist($togglePlasmaShield);

        $toggleMagneticNet = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TOGGLE_MAGNETIC_NET));
        $manager->persist($toggleMagneticNet);

        $chitchat = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CHITCHAT));
        $manager->persist($chitchat);

        $whisper = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::WHISPER));
        $manager->persist($whisper);

        $graft = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GRAFT));
        $manager->persist($graft);

        $learn = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::LEARN));
        $manager->persist($learn);

        $putThroughDoor = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PUT_THROUGH_DOOR));
        $manager->persist($putThroughDoor);

        $becomeGenius = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::BECOME_GENIUS));
        $manager->persist($becomeGenius);

        $premonition = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PREMONITION));
        $manager->persist($premonition);

        $ceasefire = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CEASEFIRE));
        $manager->persist($ceasefire);

        $guard = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GUARD));
        $manager->persist($guard);

        $commanderOrder = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COMMANDER_ORDER));
        $manager->persist($commanderOrder);

        $acceptMission = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ACCEPT_MISSION));
        $manager->persist($acceptMission);

        $rejectMission = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REJECT_MISSION));
        $manager->persist($rejectMission);

        $printZeList = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PRINT_ZE_LIST));
        $manager->persist($printZeList);

        $throwGrenade = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::THROW_GRENADE));
        $manager->persist($throwGrenade);

        $toggleNeronInhibition = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TOGGLE_NERON_INHIBITION));
        $manager->persist($toggleNeronInhibition);

        $delog = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DELOG));
        $manager->persist($delog);

        $runHome = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::RUN_HOME));
        $manager->persist($runHome);

        $putsch = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PUTSCH));
        $manager->persist($putsch);

        $anathema = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ANATHEMA));
        $manager->persist($anathema);

        $mixRationSpore = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::MIX_RATION_SPORE));
        $manager->persist($mixRationSpore);

        $depress = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DEPRESS));
        $manager->persist($depress);

        $slimeTrap = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SLIME_TRAP));
        $manager->persist($slimeTrap);

        $slimeObject = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SLIME_OBJECT));
        $manager->persist($slimeObject);

        $massGgedon = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::MASS_GGEDON));
        $manager->persist($massGgedon);

        $reinforce = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REINFORCE));
        $manager->persist($reinforce);

        $upgradeDroneToTurbo = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPGRADE_DRONE_TO_TURBO));
        $manager->persist($upgradeDroneToTurbo);

        $upgradeDroneToFirefighter = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPGRADE_DRONE_TO_FIREFIGHTER));
        $manager->persist($upgradeDroneToFirefighter);

        $upgradeDroneToPilot = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPGRADE_DRONE_TO_PILOT));
        $manager->persist($upgradeDroneToPilot);

        $upgradeDroneToSensor = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPGRADE_DRONE_TO_SENSOR));
        $manager->persist($upgradeDroneToSensor);

        $takeCat = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TAKE_CAT));
        $manager->persist($takeCat);

        $petCat = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PET_CAT));
        $manager->persist($petCat);

        $cureCat = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CURE_CAT));
        $manager->persist($cureCat);

        $shootEquipment = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SHOOT_EQUIPMENT));
        $manager->persist($shootEquipment);

        $takeChicken = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TAKE_CHICKEN));
        $manager->persist($takeChicken);

        $cureChicken = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CURE_CHICKEN));
        $manager->persist($cureChicken);

        $torture = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TORTURE));
        $manager->persist($torture);

        $daunt = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DAUNT));
        $manager->persist($daunt);

        $genMetal = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GEN_METAL));
        $manager->persist($genMetal);

        $doorSabotage = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DOOR_SABOTAGE));
        $manager->persist($doorSabotage);

        $giveNightmare = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::GIVE_NIGHTMARE));
        $manager->persist($giveNightmare);

        $neronDepress = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::NERON_DEPRESS));
        $manager->persist($neronDepress);

        $participateResearch = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PARTICIPATE_RESEARCH));
        $manager->persist($participateResearch);

        $computeEden = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COMPUTE_EDEN));
        $manager->persist($computeEden);

        $travelToEden = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TRAVEL_TO_EDEN));
        $manager->persist($travelToEden);

        $comManagerAnnouncement = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::COM_MANAGER_ANNOUNCEMENT));
        $manager->persist($comManagerAnnouncement);

        $establishLinkWithSol = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ESTABLISH_LINK_WITH_SOL));
        $manager->persist($establishLinkWithSol);

        $upgradeNeron = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPGRADE_NERON));
        $manager->persist($upgradeNeron);

        $decodeRebelSignal = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::DECODE_REBEL_SIGNAL));
        $manager->persist($decodeRebelSignal);

        $contactXyloph = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CONTACT_XYLOPH));
        $manager->persist($contactXyloph);

        $openContainerCost0 = new ActionConfig();
        $openContainerCost0
            ->setName('open_container_cost_0')
            ->setActionName(ActionEnum::OPEN_CONTAINER)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN)
            ->setActionCost(0)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setSuccessRate(100);
        $manager->persist($openContainerCost0);

        $playWithDog = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PLAY_WITH_DOG));
        $manager->persist($playWithDog);

        $acceptTrade = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ACCEPT_TRADE));
        $manager->persist($acceptTrade);

        $refuseTrade = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::REFUSE_TRADE));
        $manager->persist($refuseTrade);

        $toggleVocodedAnnouncements = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TOGGLE_VOCODED_ANNOUNCEMENTS));
        $manager->persist($toggleVocodedAnnouncements);

        $checkRoster = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CHECK_ROSTER));
        $manager->persist($checkRoster);

        $toggleDeathAnnouncements = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TOGGLE_DEATH_ANNOUNCEMENTS));
        $manager->persist($toggleDeathAnnouncements);

        $adaptEpigenetics = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::ADAPT_EPIGENETICS));
        $manager->persist($adaptEpigenetics);

        $sabotageExploration = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SABOTAGE_EXPLORATION));
        $manager->persist($sabotageExploration);

        $lieDownInShipAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::LIE_DOWN_IN_SHIP));
        $manager->persist($lieDownInShipAction);

        $readSchoolbooksAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::READ_SCHOOLBOOKS));
        $manager->persist($readSchoolbooksAction);

        $useResetVaccineAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::USE_RESET_VACCINE));
        $manager->persist($useResetVaccineAction);

        $protectAction = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::PROTECT));
        $manager->persist($protectAction);

        $checkSongs = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::CHECK_JUKEBOX_SONGS));
        $manager->persist($checkSongs);

        $travelToEventPlanet = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::TRAVEL_TO_EVENT_PLANET));
        $manager->persist($travelToEventPlanet);

        $upgradeReactor = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::UPGRADE_REACTOR));
        $manager->persist($upgradeReactor);

        $openTreasure = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::OPEN_TREASURE));
        $manager->persist($openTreasure);

        $searchTreasure = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SEARCH_FOR_THE_TREASURE));
        $manager->persist($searchTreasure);

        $feedPet = ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::FEED_THE_PET));
        $manager->persist($feedPet);

        $manager->flush();

        $this->addReference(self::SUICIDE, $suicide);
        $this->addReference(ActionEnum::FORCE_CYCLE_CHANGE->value, $forceCycleChange);
        $this->addReference(self::AUTO_DESTROY, $autoDestroy);
        $this->addReference(self::KILL_PLAYER, $killPlayer);
        $this->addReference(self::USURP_IDENTITY, $usurpIdentity);

        $this->addReference(self::REJUVENATE_ALPHA, $rejuvenateAlpha);
        $this->addReference(self::RESET_SKILL_POINT, $resetSpecializationPoint);
        $this->addReference(self::UPDATING_TALKIE, $updatingTalkie);

        $this->addReference(self::MOVE_DEFAULT, $moveAction);
        $this->addReference(self::SEARCH_DEFAULT, $searchAction);
        $this->addReference(self::HIT_DEFAULT, $hitAction);
        $this->addReference(self::HIDE_DEFAULT, $hideAction);
        $this->addReference(self::DEFAULT_TAKE, $takeItemAction);
        $this->addReference(self::DEFAULT_DROP, $dropItemAction);
        $this->addReference(self::RATION_CONSUME, $rationConsumeAction);
        $this->addReference(self::DRUG_CONSUME, $drugConsumeAction);
        $this->addReference(self::BUILD_DEFAULT, $buildAction);
        $this->addReference(self::READ_DOCUMENT, $readDocument);
        $this->addReference(self::READ_BOOK, $readAction);
        $this->addReference(self::ATTACK_DEFAULT, $attackAction);
        $this->addReference(ActionEnum::EXTINGUISH->value, $extinguishAction);
        $this->addReference(self::TRY_KUBE, $tryKubeAction);
        $this->addReference(self::OPEN_SPACE_CAPSULE, $openSpaceCapsuleAction);
        $this->addReference(self::INJECT_SERUM, $injectSerumAction);
        $this->addReference(ActionEnum::USE_BANDAGE->value, $bandageAction);
        $this->addReference(self::COOK_EXPRESS, $expressCookAction);
        $this->addReference(self::COOK_DEFAULT, $cookAction);
        $this->addReference(self::HEAL, $healAction);
        $this->addReference(self::SELF_HEAL, $selfHealAction);
        $this->addReference(self::HEAL_ULTRA, $ultraHealAction);
        $this->addReference($comfortAction->getName(), $comfortAction);
        $this->addReference(self::WRITE, $writeAction);
        $this->addReference(self::SHRED, $shredAction);
        $this->addReference(self::HYPERFREEZE_DEFAULT, $hyperfreezeAction);
        $this->addReference(self::GAG_DEFAULT, $gagAction);
        $this->addReference(self::UNGAG_DEFAULT, $ungagAction);
        $this->addReference(ActionEnum::TAKE_SHOWER->toString(), $showerAction);
        $this->addReference(self::WASH_IN_SINK, $sinkAction);
        $this->addReference(ActionEnum::WASH_WITH_PERFUME->toString(), $washWithPerfumeAction);
        $this->addReference(self::FUEL_INJECT, $fuelInjectAction);
        $this->addReference(self::FUEL_RETRIEVE, $retrieveFuelAction);
        $this->addReference(self::OXYGEN_INJECT, $oxygenInjectAction);
        $this->addReference(self::OXYGEN_RETRIEVE, $retrieveOxygenAction);
        $this->addReference(self::STRENGTHEN_HULL, $strengthenHullAction);
        $this->addReference(self::LIE_DOWN, $lieDownAction);
        $this->addReference(self::GET_UP, $getUpAction);
        $this->addReference(ActionEnum::COFFEE->value, $coffeeAction);
        $this->addReference(self::DISPENSE_DRUG, $dispenseAction);
        $this->addReference(self::TRANSPLANT, $transplantAction);
        $this->addReference(self::TREAT_PLANT, $treatPlantAction);
        $this->addReference(self::WATER_PLANT, $waterPlantAction);
        $this->addReference(self::REPORT_FIRE, $reportFireAction);
        $this->addReference(self::REPORT_EQUIPMENT, $reportEquipmentAction);
        $this->addReference(self::INSTALL_CAMERA, $installCameraAction);
        $this->addReference(self::REMOVE_CAMERA, $removeCameraAction);
        $this->addReference(self::EXAMINE_EQUIPMENT, $examineEquipmentAction);
        $this->addReference(self::CHECK_SPORE_LEVEL, $checkSporeLevelAction);
        $this->addReference(self::FLIRT_DEFAULT, $flirtAction);
        $this->addReference(self::DO_THE_THING, $doTheThingAction);
        $this->addReference(ActionEnum::REMOVE_SPORE->value, $removeSporeAction);
        $this->addReference(self::PUBLIC_BROADCAST, $publicBroadcastAction);
        $this->addReference(self::EXTINGUISH_MANUALLY, $extinguishManuallyAction);
        $this->addReference(self::MOTIVATIONAL_SPEECH, $motivationalSpeechAction);
        $this->addReference(self::BORING_SPEECH, $boringSpeechAction);
        $this->addReference(self::SURGERY, $surgeryAction);
        $this->addReference(self::SELF_SURGERY, $selfSurgeryAction);
        $this->addReference(self::SHOOT, $shootAction);
        $this->addReference(self::PLAY_ARCADE, $playArcade);
        $this->addReference(self::SHOOT_HUNTER_TURRET, $shootHunterTurret);
        $this->addReference(self::SHOOT_RANDOM_HUNTER_TURRET, $shootRandomHunterTurret);
        $this->addReference(self::TAKEOFF, $takeoff);
        $this->addReference(self::ACCESS_TERMINAL, $accessTerminal);
        $this->addReference(self::LAND, $land);
        $this->addReference(self::SHOOT_HUNTER_PATROL_SHIP, $shootHunterPatrolShip);
        $this->addReference(self::SHOOT_RANDOM_HUNTER_PATROL_SHIP, $shootRandomHunterPatrolShip);
        $this->addReference(self::COLLECT_SCRAP, $collectScrap);
        $this->addReference(self::RENOVATE, $renovate);
        $this->addReference(self::CONVERT_ACTION_TO_MOVEMENT, $convertActionToMovement);
        $this->addReference(self::AUTO_EJECT, $autoEject);
        $this->addReference(self::INSERT_FUEL_CHAMBER, $insertFuelChamber);
        $this->addReference(self::RETRIEVE_FUEL_CHAMBER, $retrieveFuelChamber);
        $this->addReference(self::CHECK_FUEL_CHAMBER_LEVEL, $checkFuelChamberLevel);
        $this->addReference(self::HACK, $hack);
        $this->addReference(self::EXIT_TERMINAL, $exitTerminal);
        $this->addReference(self::ADVANCE_DAEDALUS, $advanceDaedalus);
        $this->addReference(self::SCAN, $scan);
        $this->addReference(self::ANALYZE_PLANET, $analyzePlanet);
        $this->addReference(self::TURN_DAEDALUS_LEFT, $turnDaedalusLeft);
        $this->addReference(self::TURN_DAEDALUS_RIGHT, $turnDaedalusRight);
        $this->addReference(self::DELETE_PLANET, $deletePlanet);
        $this->addReference(self::LEAVE_ORBIT, $leaveOrbit);
        $this->addReference(self::TAKEOFF_TO_PLANET, $takeoffToPlanet);
        $this->addReference(self::TAKEOFF_TO_PLANET_PATROL_SHIP, $takeoffToPlanetPatrolShip);
        $this->addReference(self::CHANGE_NERON_CPU_PRIORITY, $changeNeronCpuPriority);
        $this->addReference(ActionEnum::REPAIR_PILGRED->value, $repairPilgred);
        $this->addReference(ActionEnum::RETURN_TO_SOL->value, $returnToSol);
        $this->addReference(ActionEnum::PARTICIPATE->value, $participate);
        $this->addReference(ActionEnum::CHANGE_NERON_CREW_LOCK->value, object: $changeNeronCrewLock);
        $this->addReference(ActionEnum::CHANGE_NERON_FOOD_DESTRUCTION_OPTION->value, $changeNeronFoodDestructionOption);
        $this->addReference(ActionEnum::TOGGLE_PLASMA_SHIELD->value, $togglePlasmaShield);
        $this->addReference(ActionEnum::TOGGLE_MAGNETIC_NET->value, $toggleMagneticNet);
        $this->addReference(ActionEnum::CHITCHAT->value, $chitchat);
        $this->addReference(ActionEnum::GRAFT->value, $graft);
        $this->addReference(ActionEnum::LEARN->value, $learn);
        $this->addReference(ActionEnum::PUT_THROUGH_DOOR->value, $putThroughDoor);
        $this->addReference(ActionEnum::BECOME_GENIUS->value, $becomeGenius);
        $this->addReference(ActionEnum::PREMONITION->value, $premonition);
        $this->addReference(ActionEnum::CEASEFIRE->value, $ceasefire);
        $this->addReference(ActionEnum::GUARD->value, $guard);
        $this->addReference(ActionEnum::COMMANDER_ORDER->value, $commanderOrder);
        $this->addReference(ActionEnum::ACCEPT_MISSION->value, $acceptMission);
        $this->addReference(ActionEnum::REJECT_MISSION->value, $rejectMission);
        $this->addReference(ActionEnum::PRINT_ZE_LIST->value, $printZeList);
        $this->addReference(ActionEnum::THROW_GRENADE->value, $throwGrenade);
        $this->addReference(ActionEnum::TOGGLE_NERON_INHIBITION->value, $toggleNeronInhibition);
        $this->addReference(ActionEnum::DELOG->value, $delog);
        $this->addReference(ActionEnum::RUN_HOME->value, $runHome);
        $this->addReference(ActionEnum::PUTSCH->value, $putsch);
        $this->addReference(ActionEnum::ANATHEMA->value, $anathema);
        $this->addReference(ActionEnum::MIX_RATION_SPORE->value, $mixRationSpore);
        $this->addReference(ActionEnum::DEPRESS->value, $depress);
        $this->addReference(ActionEnum::SLIME_TRAP->value, $slimeTrap);
        $this->addReference(ActionEnum::SLIME_OBJECT->value, $slimeObject);
        $this->addReference(ActionEnum::MASS_GGEDON->value, $massGgedon);
        $this->addReference(ActionEnum::REINFORCE->value, $reinforce);
        $this->addReference(ActionEnum::UPGRADE_DRONE_TO_TURBO->value, $upgradeDroneToTurbo);
        $this->addReference(ActionEnum::UPGRADE_DRONE_TO_FIREFIGHTER->value, $upgradeDroneToFirefighter);
        $this->addReference(ActionEnum::UPGRADE_DRONE_TO_PILOT->value, $upgradeDroneToPilot);
        $this->addReference(ActionEnum::UPGRADE_DRONE_TO_SENSOR->value, $upgradeDroneToSensor);
        $this->addReference(ActionEnum::TAKE_CAT->value, $takeCat);
        $this->addReference(ActionEnum::PET_CAT->value, $petCat);
        $this->addReference(ActionEnum::CURE_CAT->value, $cureCat);
        $this->addReference(ActionEnum::TAKE_CHICKEN->value, $takeChicken);
        $this->addReference(ActionEnum::CURE_CHICKEN->value, $cureChicken);
        $this->addReference(ActionEnum::SHOOT_EQUIPMENT->value, $shootEquipment);
        $this->addReference(ActionEnum::TORTURE->value, $torture);
        $this->addReference(ActionEnum::DAUNT->value, $daunt);
        $this->addReference(ActionEnum::GEN_METAL->value, $genMetal);
        $this->addReference(ActionEnum::DOOR_SABOTAGE->value, $doorSabotage);
        $this->addReference(ActionEnum::GIVE_NIGHTMARE->value, $giveNightmare);
        $this->addReference(ActionEnum::NERON_DEPRESS->value, $neronDepress);
        $this->addReference(ActionEnum::PARTICIPATE_RESEARCH->value, $participateResearch);
        $this->addReference(ActionEnum::COMPUTE_EDEN->value, $computeEden);
        $this->addReference(ActionEnum::TRAVEL_TO_EDEN->value, $travelToEden);
        $this->addReference(self::OPEN_CONTAINER_COST_0, $openContainerCost0);
        $this->addReference(ActionEnum::COM_MANAGER_ANNOUNCEMENT->value, $comManagerAnnouncement);
        $this->addReference(ActionEnum::ESTABLISH_LINK_WITH_SOL->value, $establishLinkWithSol);
        $this->addReference(ActionEnum::UPGRADE_NERON->value, $upgradeNeron);
        $this->addReference(ActionEnum::DECODE_REBEL_SIGNAL->value, $decodeRebelSignal);
        $this->addReference(ActionEnum::CONTACT_XYLOPH->value, $contactXyloph);
        $this->addReference(ActionEnum::PLAY_WITH_DOG->value, $playWithDog);
        $this->addReference(ActionEnum::ACCEPT_TRADE->value, $acceptTrade);
        $this->addReference(ActionEnum::REFUSE_TRADE->value, $refuseTrade);
        $this->addReference(ActionEnum::TOGGLE_VOCODED_ANNOUNCEMENTS->value, $toggleVocodedAnnouncements);
        $this->addReference(ActionEnum::CHECK_ROSTER->value, $checkRoster);
        $this->addReference(ActionEnum::TOGGLE_DEATH_ANNOUNCEMENTS->value, $toggleDeathAnnouncements);
        $this->addReference(ActionEnum::WHISPER->value, $whisper);
        $this->addReference(ActionEnum::ADAPT_EPIGENETICS->value, $adaptEpigenetics);
        $this->addReference(ActionEnum::SABOTAGE_EXPLORATION->value, $sabotageExploration);
        $this->addReference(ActionEnum::LIE_DOWN_IN_SHIP->value, $lieDownInShipAction);
        $this->addReference(ActionEnum::READ_SCHOOLBOOKS->value, $readSchoolbooksAction);
        $this->addReference(ActionEnum::USE_RESET_VACCINE->value, $useResetVaccineAction);
        $this->addReference(ActionEnum::PROTECT->value, $protectAction);
        $this->addReference(ActionEnum::CHECK_JUKEBOX_SONGS->value, $checkSongs);
        $this->addReference(ActionEnum::BOND->value, $bondAction);
        $this->addReference(ActionEnum::RELAX->value, $relaxAction);
        $this->addReference(ActionEnum::TRAVEL_TO_EVENT_PLANET->value, $travelToEventPlanet);
        $this->addReference(ActionEnum::UPGRADE_REACTOR->value, $upgradeReactor);
        $this->addReference(ActionEnum::OPEN_TREASURE->value, $openTreasure);
        $this->addReference(ActionEnum::SEARCH_FOR_THE_TREASURE->value, $searchTreasure);
        $this->addReference(ActionEnum::FEED_THE_PET->value, $feedPet);
    }
}
