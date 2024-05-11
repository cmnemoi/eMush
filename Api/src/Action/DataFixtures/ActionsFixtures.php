<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\PlayerVariableEnum;

class ActionsFixtures extends Fixture
{
    public const string SUICIDE = 'suicide';
    public const string AUTO_DESTROY = 'auto.destruction';
    public const string KILL_PLAYER = 'kill.player';
    public const string RESET_SPECIALIST_POINT = ActionEnum::RESET_SPECIALIST_POINTS->value;
    public const string REJUVENATE_ALPHA = 'rejuvenate.alpha';
    public const string UPDATING_TALKIE = 'updating.talkie';
    public const string MOVE_DEFAULT = 'move.default';
    public const string SEARCH_DEFAULT = 'search.default';
    public const string HIT_DEFAULT = 'hit.default';
    public const string HIDE_DEFAULT = 'hide.default';
    public const string DEFAULT_TAKE = 'default.take';
    public const string DEFAULT_DROP = 'default.drop';
    public const string DO_THE_THING = 'do.the.thing';
    public const string DRUG_CONSUME = 'drug.consume';
    public const string RATION_CONSUME = 'ration.consume';
    public const string BUILD_DEFAULT = 'build.default';
    public const string READ_DOCUMENT = 'read.document';
    public const string READ_BOOK = 'read.book';
    public const string ATTACK_DEFAULT = 'attack.default';
    public const string EXTINGUISH_DEFAULT = 'extinguish.default';
    public const string TRY_KUBE = 'try.kube';
    public const string OPEN_SPACE_CAPSULE = 'open.space.capsule';
    public const string INJECT_SERUM = 'inject.serum';
    public const string BANDAGE_DEFAULT = 'bandage.default';
    public const string COOK_EXPRESS = 'cook.express';
    public const string COOK_DEFAULT = 'cook.default';
    public const string HEAL = 'heal';
    public const string SELF_HEAL = 'self.heal';
    public const string HEAL_ULTRA = 'heal.ultra';
    public const string COMFORT_DEFAULT = 'comfort.default';
    public const string WRITE = 'write';
    public const string SHRED = 'shred';
    public const string GAG_DEFAULT = 'gag.default';
    public const string UNGAG_DEFAULT = 'ungag.default';
    public const string HYPERFREEZE_DEFAULT = 'hyperfreeze.default';
    public const string SHOWER_DEFAULT = 'shower.default';
    public const string WASH_IN_SINK = 'wash.in.sink';
    public const string FLIRT_DEFAULT = 'flirt.default';
    public const string FUEL_INJECT = 'fuel.inject';
    public const string FUEL_RETRIEVE = 'fuel.retrieve';
    public const string OXYGEN_INJECT = 'oxygen.inject';
    public const string STRENGTHEN_HULL = 'strength_hull';
    public const string OXYGEN_RETRIEVE = 'oxygen.retrieve';
    public const string LIE_DOWN = 'lie.down';
    public const string GET_UP = 'get.up';
    public const string COFFEE_DEFAULT = 'coffee.default';
    public const string DISPENSE_DRUG = 'dispense.drug';
    public const string TRANSPLANT_DEFAULT = 'transplant.default';
    public const string TREAT_PLANT = 'treat.plant';
    public const string WATER_PLANT = 'water.plant';
    public const string REPORT_EQUIPMENT = 'report.equipment';
    public const string REPORT_FIRE = 'report.fire';
    public const string INSTALL_CAMERA = 'install.camera';
    public const string REMOVE_CAMERA = 'remove.camera';
    public const string CHECK_SPORE_LEVEL = 'check.spore.level';
    public const string EXAMINE_EQUIPMENT = 'examine.equipment';
    public const string REMOVE_SPORE = 'remove.spore';
    public const string PUBLIC_BROADCAST = 'public.broadcast';
    public const string EXTINGUISH_MANUALLY = 'extinguish.manually';
    public const string MOTIVATIONAL_SPEECH = 'motivational.speech';
    public const string BORING_SPEECH = 'boring.speech';
    public const string SURGERY = 'surgery';
    public const string SELF_SURGERY = 'self.surgery';
    public const string SHOOT = 'shoot';
    public const string PLAY_ARCADE = 'play.arcade';
    public const string SHOOT_HUNTER_TURRET = 'shoot.hunter.turret';
    public const string SHOOT_RANDOM_HUNTER_TURRET = 'shoot.random.hunter.turret';
    public const string TAKEOFF = 'takeoff';
    public const string ACCESS_TERMINAL = 'access.terminal';
    public const string LAND = 'land';
    public const string SHOOT_HUNTER_PATROL_SHIP = 'shoot.hunter.patrol_ship';
    public const string SHOOT_RANDOM_HUNTER_PATROL_SHIP = 'shoot.random.hunter.patrol_ship';
    public const string COLLECT_SCRAP = 'collect.scrap';
    public const string RENOVATE = 'renovate';
    public const string CONVERT_ACTION_TO_MOVEMENT = 'convert_action_to_movement';
    public const string AUTO_EJECT = 'auto.eject';
    public const string INSERT_FUEL_CHAMBER = 'insert.fuel.chamber';
    public const string RETRIEVE_FUEL_CHAMBER = 'retrieve.fuel.chamber';
    public const string CHECK_FUEL_CHAMBER_LEVEL = 'check.fuel.chamber.level';
    public const string HACK = 'hack';
    public const string EXIT_TERMINAL = 'exit.terminal';
    public const string ADVANCE_DAEDALUS = 'advance.daedalus';
    public const string SCAN = 'scan';
    public const string ANALYZE_PLANET = 'analyze.planet';
    public const string TURN_DAEDALUS_LEFT = 'turn.daedalus.left';
    public const string TURN_DAEDALUS_RIGHT = 'turn.daedalus.right';
    public const string DELETE_PLANET = 'delete.planet';
    public const string LEAVE_ORBIT = 'leave.orbit';
    public const string TAKEOFF_TO_PLANET = 'takeoff.to.planet';
    public const string TAKEOFF_TO_PLANET_PATROL_SHIP = 'takeoff.to.planet.patrol_ship';
    public const string CHANGE_NERON_CPU_PRIORITY = 'change.neron.cpu.priority';

    public function load(ObjectManager $manager): void
    {
        /** @TODO remove this after alpha */
        $suicide = new ActionConfig();
        $suicide
            ->setName(ActionEnum::SUICIDE->value)
            ->setActionName(ActionEnum::SUICIDE)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER);
        $manager->persist($suicide);

        $autoDestroy = new ActionConfig();
        $autoDestroy
            ->setName(ActionEnum::AUTO_DESTROY->value)
            ->setActionName(ActionEnum::AUTO_DESTROY)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);
        $manager->persist($autoDestroy);

        $killPlayer = new ActionConfig();
        $killPlayer
            ->setName(ActionEnum::KILL_PLAYER->value)
            ->setActionName(ActionEnum::KILL_PLAYER)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER);
        $manager->persist($killPlayer);

        $rejuvenateAlpha = new ActionConfig();
        $rejuvenateAlpha
            ->setActionName(ActionEnum::REJUVENATE)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->buildName(GameConfigEnum::ALPHA);
        $manager->persist($rejuvenateAlpha);

        $resetSpecializationPoint = new ActionConfig();
        $resetSpecializationPoint
            ->setActionName(ActionEnum::RESET_SPECIALIST_POINTS)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setRange(ActionRangeEnum::PLAYER)
            ->buildName(GameConfigEnum::ALPHA);
        $manager->persist($resetSpecializationPoint);

        $updatingTalkie = new ActionConfig();
        $updatingTalkie
            ->setName(ActionEnum::UPDATE_TALKIE->value)
            ->setActionName(ActionEnum::UPDATE_TALKIE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setInjuryRate(10)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($updatingTalkie);

        $moveAction = new ActionConfig();
        $moveAction
            ->setName(ActionEnum::MOVE->value)
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setMovementCost(1);
        $manager->persist($moveAction);

        $searchAction = new ActionConfig();
        $searchAction
            ->setName(ActionEnum::SEARCH->value)
            ->setActionName(ActionEnum::SEARCH)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1);
        $manager->persist($searchAction);

        $hitAction = new ActionConfig();
        $hitAction
            ->setName(ActionEnum::HIT->value)
            ->setActionName(ActionEnum::HIT)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setSuccessRate(60)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::PUBLIC);
        $manager->persist($hitAction);

        $hideAction = new ActionConfig();
        $hideAction
            ->setName(ActionEnum::HIDE->value)
            ->setActionName(ActionEnum::HIDE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);
        $manager->persist($hideAction);

        $takeItemAction = new ActionConfig();
        $takeItemAction
            ->setName(ActionEnum::TAKE->value)
            ->setActionName(ActionEnum::TAKE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setInjuryRate(1);

        $manager->persist($takeItemAction);

        $dropItemAction = new ActionConfig();
        $dropItemAction
            ->setName(ActionEnum::DROP->value)
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($dropItemAction);

        $rationConsumeAction = new ActionConfig();
        $rationConsumeAction
            ->setName(ActionEnum::CONSUME->value)
            ->setActionName(ActionEnum::CONSUME)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($rationConsumeAction);

        $drugConsumeAction = new ActionConfig();
        $drugConsumeAction
            ->setName(ActionEnum::CONSUME_DRUG->value)
            ->setActionName(ActionEnum::CONSUME_DRUG)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET)
            ->setTypes([ActionEnum::CONSUME]);

        $manager->persist($drugConsumeAction);

        $buildAction = new ActionConfig();
        $buildAction
            ->setName(ActionEnum::BUILD->value)
            ->setActionName(ActionEnum::BUILD)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(3)
            ->setDirtyRate(25)
            ->setInjuryRate(5);

        $manager->persist($buildAction);

        $readAction = new ActionConfig();
        $readAction
            ->setName(ActionEnum::READ_BOOK->value)
            ->setActionName(ActionEnum::READ_BOOK)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2);

        $manager->persist($readAction);

        $readDocument = new ActionConfig();
        $readDocument
            ->setName(ActionEnum::READ_DOCUMENT->value)
            ->setActionName(ActionEnum::READ_DOCUMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($readDocument);

        $attackAction = new ActionConfig();
        $attackAction
            ->setName(ActionEnum::ATTACK->value)
            ->setActionName(ActionEnum::ATTACK)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE, ActionTypeEnum::ACTION_ATTACK])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setSuccessRate(60)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::ONE_SHOT, VisibilityEnum::PUBLIC);

        $manager->persist($attackAction);

        $extinguishAction = new ActionConfig();
        $extinguishAction
            ->setName(ActionEnum::EXTINGUISH->value)
            ->setActionName(ActionEnum::EXTINGUISH)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1)
            ->setInjuryRate(1)
            ->setSuccessRate(50);

        $manager->persist($extinguishAction);

        $tryKubeAction = new ActionConfig();
        $tryKubeAction
            ->setName(ActionEnum::TRY_KUBE->value)
            ->setActionName(ActionEnum::TRY_KUBE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1);

        $manager->persist($tryKubeAction);

        $openSpaceCapsuleAction = new ActionConfig();
        $openSpaceCapsuleAction
            ->setName(ActionEnum::OPEN->value)
            ->setActionName(ActionEnum::OPEN)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setInjuryRate(1);

        $manager->persist($openSpaceCapsuleAction);

        $injectSerumAction = new ActionConfig();
        $injectSerumAction
            ->setName(ActionEnum::CURE->value)
            ->setActionName(ActionEnum::CURE)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1);

        $manager->persist($injectSerumAction);

        $bandageAction = new ActionConfig();
        $bandageAction
            ->setName(ActionEnum::USE_BANDAGE->value)
            ->setActionName(ActionEnum::USE_BANDAGE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(5)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setOutputQuantity(2);

        $manager->persist($bandageAction);

        $expressCookAction = new ActionConfig();
        $expressCookAction
            ->setName(ActionEnum::EXPRESS_COOK->value)
            ->setActionName(ActionEnum::EXPRESS_COOK)
            ->setRange(ActionRangeEnum::SHELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(20);

        $manager->persist($expressCookAction);

        $cookAction = new ActionConfig();
        $cookAction
            ->setName(ActionEnum::COOK->value)
            ->setActionName(ActionEnum::COOK)
            ->setRange(ActionRangeEnum::SHELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(20);

        $manager->persist($cookAction);

        $selfHealAction = new ActionConfig();
        $selfHealAction
            ->setName(ActionEnum::SELF_HEAL->value)
            ->setActionName(ActionEnum::SELF_HEAL)
            ->setTypes([ActionTypeEnum::ACTION_HEAL])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(3)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setOutputQuantity(3);

        $manager->persist($selfHealAction);

        $healAction = new ActionConfig();
        $healAction
            ->setName(ActionEnum::HEAL->value)
            ->setActionName(ActionEnum::HEAL)
            ->setTypes([ActionTypeEnum::ACTION_HEAL])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(2)
            ->setOutputQuantity(3);

        $manager->persist($healAction);

        $comfortAction = new ActionConfig();
        $comfortAction
            ->setName(ActionEnum::COMFORT->value)
            ->setActionName(ActionEnum::COMFORT)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setTypes([ActionTypeEnum::ACTION_SPOKEN])
            ->setActionCost(1)
            ->setOutputQuantity(2);

        $manager->persist($comfortAction);

        $ultraHealAction = new ActionConfig();
        $ultraHealAction
            ->setName(ActionEnum::ULTRAHEAL->value)
            ->setActionName(ActionEnum::ULTRAHEAL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($ultraHealAction);

        $writeAction = new ActionConfig();
        $writeAction
            ->setName(ActionEnum::WRITE->value)
            ->setActionName(ActionEnum::WRITE)
            ->setRange(ActionRangeEnum::SHELF)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL);

        $manager->persist($writeAction);

        $shredAction = new ActionConfig();
        $shredAction
            ->setName(ActionEnum::SHRED->value)
            ->setActionName(ActionEnum::SHRED)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($shredAction);

        $hyperfreezeAction = new ActionConfig();
        $hyperfreezeAction
            ->setName(ActionEnum::HYPERFREEZE->value)
            ->setActionName(ActionEnum::HYPERFREEZE)
            ->setRange(ActionRangeEnum::SHELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($hyperfreezeAction);

        $gagAction = new ActionConfig();
        $gagAction
            ->setName(ActionEnum::GAG->value)
            ->setActionName(ActionEnum::GAG)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1);

        $manager->persist($gagAction);

        $ungagAction = new ActionConfig();
        $ungagAction
            ->setName(ActionEnum::UNGAG->value)
            ->setActionName(ActionEnum::UNGAG)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1);

        $manager->persist($ungagAction);

        $showerAction = new ActionConfig();
        $showerAction
            ->setName(ActionEnum::SHOWER->value)
            ->setActionName(ActionEnum::SHOWER)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setInjuryRate(2)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($showerAction);

        $sinkAction = new ActionConfig();
        $sinkAction
            ->setName(ActionEnum::WASH_IN_SINK->value)
            ->setActionName(ActionEnum::WASH_IN_SINK)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(3)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($sinkAction);

        $fuelInjectAction = new ActionConfig();
        $fuelInjectAction
            ->setName(ActionEnum::INSERT_FUEL->value)
            ->setActionName(ActionEnum::INSERT_FUEL)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setOutputQuantity(1); // amount of fuel inserted

        $manager->persist($fuelInjectAction);

        $retrieveFuelAction = new ActionConfig();
        $retrieveFuelAction
            ->setName(ActionEnum::RETRIEVE_FUEL->value)
            ->setActionName(ActionEnum::RETRIEVE_FUEL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($retrieveFuelAction);

        $oxygenInjectAction = new ActionConfig();
        $oxygenInjectAction
            ->setName(ActionEnum::INSERT_OXYGEN->value)
            ->setActionName(ActionEnum::INSERT_OXYGEN)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setInjuryRate(1)
            ->setOutputQuantity(1) // amount of fuel inserted
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($oxygenInjectAction);

        $retrieveOxygenAction = new ActionConfig();
        $retrieveOxygenAction
            ->setName(ActionEnum::RETRIEVE_OXYGEN->value)
            ->setActionName(ActionEnum::RETRIEVE_OXYGEN)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);

        $manager->persist($retrieveOxygenAction);

        $strengthenHullAction = new ActionConfig();
        $strengthenHullAction
            ->setName(ActionEnum::STRENGTHEN_HULL->value)
            ->setActionName(ActionEnum::STRENGTHEN_HULL)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(50)
            ->setInjuryRate(5)
            ->setSuccessRate(25)
            ->setOutputQuantity(5);

        $manager->persist($strengthenHullAction);

        $lieDownActon = new ActionConfig();
        $lieDownActon
            ->setName(ActionEnum::LIE_DOWN->value)
            ->setActionName(ActionEnum::LIE_DOWN)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($lieDownActon);

        $getUpAction = new ActionConfig();
        $getUpAction
            ->setName(ActionEnum::GET_UP->value)
            ->setActionName(ActionEnum::GET_UP)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER);

        $manager->persist($getUpAction);

        $coffeeAction = new ActionConfig();
        $coffeeAction
            ->setName(ActionEnum::COFFEE->value)
            ->setActionName(ActionEnum::COFFEE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(3);

        $manager->persist($coffeeAction);

        $dispenseAction = new ActionConfig();
        $dispenseAction
            ->setName(ActionEnum::DISPENSE->value)
            ->setActionName(ActionEnum::DISPENSE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($dispenseAction);

        $transplantAction = new ActionConfig();
        $transplantAction
            ->setName(ActionEnum::TRANSPLANT->value)
            ->setActionName(ActionEnum::TRANSPLANT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setDirtyRate(15);

        $manager->persist($transplantAction);

        $treatPlantAction = new ActionConfig();
        $treatPlantAction
            ->setName(ActionEnum::TREAT_PLANT->value)
            ->setActionName(ActionEnum::TREAT_PLANT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($treatPlantAction);

        $waterPlantAction = new ActionConfig();
        $waterPlantAction
            ->setName(ActionEnum::WATER_PLANT->value)
            ->setActionName(ActionEnum::WATER_PLANT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($waterPlantAction);

        $reportEquipmentAction = new ActionConfig();
        $reportEquipmentAction
            ->setName(ActionEnum::REPORT_EQUIPMENT->value)
            ->setActionName(ActionEnum::REPORT_EQUIPMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($reportEquipmentAction);

        $reportFireAction = new ActionConfig();
        $reportFireAction
            ->setName(ActionEnum::REPORT_FIRE->value)
            ->setActionName(ActionEnum::REPORT_FIRE)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::PLAYER);

        $manager->persist($reportFireAction);

        $installCameraAction = new ActionConfig();
        $installCameraAction
            ->setName(ActionEnum::INSTALL_CAMERA->value)
            ->setActionName(ActionEnum::INSTALL_CAMERA)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setDirtyRate(15);

        $manager->persist($installCameraAction);

        $removeCameraAction = new ActionConfig();
        $removeCameraAction
            ->setName(ActionEnum::REMOVE_CAMERA->value)
            ->setActionName(ActionEnum::REMOVE_CAMERA)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setDirtyRate(5);

        $manager->persist($removeCameraAction);

        $examineEquipmentAction = new ActionConfig();
        $examineEquipmentAction
            ->setName(ActionEnum::EXAMINE->value)
            ->setActionName(ActionEnum::EXAMINE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);

        $manager->persist($examineEquipmentAction);

        $checkSporeLevelAction = new ActionConfig();
        $checkSporeLevelAction
            ->setName(ActionEnum::CHECK_SPORE_LEVEL->value)
            ->setActionName(ActionEnum::CHECK_SPORE_LEVEL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($checkSporeLevelAction);

        $flirtAction = new ActionConfig();
        $flirtAction
            ->setName(ActionEnum::FLIRT->value)
            ->setActionName(ActionEnum::FLIRT)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1);

        $manager->persist($flirtAction);

        $doTheThingAction = new ActionConfig();
        $doTheThingAction
            ->setName(ActionEnum::DO_THE_THING->value)
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setOutputQuantity(2)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC);

        $manager->persist($doTheThingAction);

        $removeSporeAction = new ActionConfig();
        $removeSporeAction
            ->setName(ActionEnum::REMOVE_SPORE->value)
            ->setActionName(ActionEnum::REMOVE_SPORE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);

        $manager->persist($removeSporeAction);

        $publicBroadcastAction = new ActionConfig();
        $publicBroadcastAction
            ->setName(ActionEnum::PUBLIC_BROADCAST->value)
            ->setActionName(ActionEnum::PUBLIC_BROADCAST)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setOutputQuantity(3);

        $manager->persist($publicBroadcastAction);

        $extinguishManuallyAction = new ActionConfig();
        $extinguishManuallyAction
            ->setName(ActionEnum::EXTINGUISH_MANUALLY->value)
            ->setActionName(ActionEnum::EXTINGUISH_MANUALLY)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1)
            ->setDirtyRate(50)
            ->setInjuryRate(5)
            ->setSuccessRate(10);

        $manager->persist($extinguishManuallyAction);

        $motivationalSpeechAction = new ActionConfig();
        $motivationalSpeechAction
            ->setName(ActionEnum::MOTIVATIONAL_SPEECH->value)
            ->setActionName(ActionEnum::MOTIVATIONAL_SPEECH)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setTypes([ActionTypeEnum::ACTION_SPOKEN])
            ->setActionCost(2)
            ->setOutputQuantity(2);

        $manager->persist($motivationalSpeechAction);

        $boringSpeechAction = new ActionConfig();
        $boringSpeechAction
            ->setName(ActionEnum::BORING_SPEECH->value)
            ->setActionName(ActionEnum::BORING_SPEECH)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setTypes([ActionTypeEnum::ACTION_SPOKEN])
            ->setActionCost(2)
            ->setOutputQuantity(3);

        $manager->persist($boringSpeechAction);

        $surgeryAction = new ActionConfig();
        $surgeryAction
            ->setName(ActionEnum::SURGERY->value)
            ->setActionName(ActionEnum::SURGERY)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(2)
            ->setDirtyRate(80)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC);

        $manager->persist($surgeryAction);

        $selfSurgeryAction = new ActionConfig();
        $selfSurgeryAction
            ->setName(ActionEnum::SELF_SURGERY->value)
            ->setActionName(ActionEnum::SELF_SURGERY)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(4)
            ->setDirtyRate(100)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC);

        $manager->persist($selfSurgeryAction);

        $shootAction = new ActionConfig();
        $shootAction
            ->setName(ActionEnum::SHOOT->value)
            ->setActionName(ActionEnum::SHOOT)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE, ActionTypeEnum::ACTION_SHOOT])
            ->setActionCost(1)
            ->setSuccessRate(50)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::ONE_SHOT, VisibilityEnum::PUBLIC);
        $manager->persist($shootAction);

        $playArcade = new ActionConfig();
        $playArcade
            ->setName(ActionEnum::PLAY_ARCADE->value)
            ->setActionName(ActionEnum::PLAY_ARCADE)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(1)
            ->setSuccessRate(33)
            ->setOutputQuantity(2);
        $manager->persist($playArcade);

        $shootHunterTurret = new ActionConfig();
        $shootHunterTurret
            ->setName(ActionEnum::SHOOT_HUNTER->value . '_turret')
            ->setActionName(ActionEnum::SHOOT_HUNTER)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::HUNTER)
            ->setTypes([ActionTypeEnum::ACTION_SHOOT_HUNTER])
            ->setActionCost(1)
            ->setSuccessRate(30)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($shootHunterTurret);

        $shootRandomHunterTurret = new ActionConfig();
        $shootRandomHunterTurret
            ->setName(ActionEnum::SHOOT_RANDOM_HUNTER->value . '_turret')
            ->setActionName(ActionEnum::SHOOT_RANDOM_HUNTER)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setTypes([ActionTypeEnum::ACTION_SHOOT_HUNTER])
            ->setActionCost(1)
            ->setSuccessRate(30)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($shootRandomHunterTurret);

        $takeoff = new ActionConfig();
        $takeoff
            ->setName(ActionEnum::TAKEOFF->value)
            ->setActionName(ActionEnum::TAKEOFF)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setSuccessRate(100)
            ->setCriticalRate(20)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PUBLIC);
        $manager->persist($takeoff);

        $accessTerminal = new ActionConfig();
        $accessTerminal
            ->setName(ActionEnum::ACCESS_TERMINAL->value)
            ->setActionName(ActionEnum::ACCESS_TERMINAL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT);
        $manager->persist($accessTerminal);

        $land = new ActionConfig();
        $land
            ->setName(ActionEnum::LAND->value)
            ->setActionName(ActionEnum::LAND)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setSuccessRate(100)
            ->setCriticalRate(20)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($land);

        $shootHunterPatrolShip = new ActionConfig();
        $shootHunterPatrolShip
            ->setName(ActionEnum::SHOOT_HUNTER->value . '_patrolship')
            ->setActionName(ActionEnum::SHOOT_HUNTER_PATROL_SHIP)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::HUNTER)
            ->setTypes([ActionTypeEnum::ACTION_SHOOT_HUNTER])
            ->setActionCost(1)
            ->setSuccessRate(40)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($shootHunterPatrolShip);

        $shootRandomHunterPatrolShip = new ActionConfig();
        $shootRandomHunterPatrolShip
            ->setName(ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP->value)
            ->setActionName(ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setTypes([ActionTypeEnum::ACTION_SHOOT_HUNTER])
            ->setActionCost(1)
            ->setSuccessRate(40)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN);
        $manager->persist($shootRandomHunterPatrolShip);

        $collectScrap = new ActionConfig();
        $collectScrap
            ->setName(ActionEnum::COLLECT_SCRAP->value)
            ->setActionName(ActionEnum::COLLECT_SCRAP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setSuccessRate(100)
            ->setCriticalRate(50)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($collectScrap);

        $renovate = new ActionConfig();
        $renovate
            ->setName(ActionEnum::RENOVATE->value)
            ->setActionName(ActionEnum::RENOVATE)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setSuccessRate(12)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PRIVATE);
        $manager->persist($renovate);

        $convertActionToMovement = new ActionConfig();
        $convertActionToMovement
            ->setName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT->value)
            ->setActionName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER);
        $convertActionToMovement->getGameVariables()->setValuesByName(['value' => 1, 'min_value' => 0, 'max_value' => null], PlayerVariableEnum::ACTION_POINT);
        $convertActionToMovement->getGameVariables()->setValuesByName(['value' => -3, 'min_value' => null, 'max_value' => 0], PlayerVariableEnum::MOVEMENT_POINT);
        $manager->persist($convertActionToMovement);

        $autoEject = new ActionConfig();
        $autoEject
            ->setName(ActionEnum::AUTO_EJECT->value)
            ->setActionName(ActionEnum::AUTO_EJECT)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->setActionCost(1)
            ->setInjuryRate(25)
            ->setDirtyRate(25);
        $manager->persist($autoEject);

        $insertFuelChamber = new ActionConfig();
        $insertFuelChamber
            ->setName(ActionEnum::INSERT_FUEL_CHAMBER->value)
            ->setActionName(ActionEnum::INSERT_FUEL_CHAMBER)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setOutputQuantity(1); // amount of oxygen inserted
        $manager->persist($insertFuelChamber);

        $retrieveFuelChamber = new ActionConfig();
        $retrieveFuelChamber
            ->setName(ActionEnum::RETRIEVE_FUEL_CHAMBER->value)
            ->setActionName(ActionEnum::RETRIEVE_FUEL_CHAMBER)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET);
        $manager->persist($retrieveFuelChamber);

        $checkFuelChamberLevel = new ActionConfig();
        $checkFuelChamberLevel
            ->setName(ActionEnum::CHECK_FUEL_CHAMBER_LEVEL->value)
            ->setActionName(ActionEnum::CHECK_FUEL_CHAMBER_LEVEL)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setDirtyRate(5)
            ->setInjuryRate(0)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);
        $manager->persist($checkFuelChamberLevel);

        $hack = new ActionConfig();
        $hack
            ->setName(ActionEnum::HACK->value)
            ->setActionName(ActionEnum::HACK)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setActionCost(2)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE);
        $manager->persist($hack);

        $exitTerminal = new ActionConfig();
        $exitTerminal
            ->setName(ActionEnum::EXIT_TERMINAL->value)
            ->setActionName(ActionEnum::EXIT_TERMINAL)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($exitTerminal);

        $advanceDaedalus = new ActionConfig();
        $advanceDaedalus
            ->setName(ActionEnum::ADVANCE_DAEDALUS->value)
            ->setActionName(ActionEnum::ADVANCE_DAEDALUS)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::NO_FUEL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::ARACK_PREVENTS_TRAVEL, VisibilityEnum::PUBLIC);
        $manager->persist($advanceDaedalus);

        $scan = new ActionConfig();
        $scan
            ->setName(ActionEnum::SCAN->value)
            ->setActionName(ActionEnum::SCAN)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setActionCost(3)
            ->setSuccessRate(50);
        $manager->persist($scan);

        $analyzePlanet = new ActionConfig();
        $analyzePlanet
            ->setName(ActionEnum::ANALYZE_PLANET->value)
            ->setActionName(ActionEnum::ANALYZE_PLANET)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::PLANET)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN)
            ->setActionCost(2)
            ->setSuccessRate(100)
            ->setOutputQuantity(1); // number of sectors revealed
        $manager->persist($analyzePlanet);

        $turnDaedalusLeft = new ActionConfig();
        $turnDaedalusLeft
            ->setName(ActionEnum::TURN_DAEDALUS_LEFT->value)
            ->setActionName(ActionEnum::TURN_DAEDALUS_LEFT)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($turnDaedalusLeft);

        $turnDaedalusRight = new ActionConfig();
        $turnDaedalusRight
            ->setName(ActionEnum::TURN_DAEDALUS_RIGHT->value)
            ->setActionName(ActionEnum::TURN_DAEDALUS_RIGHT)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($turnDaedalusRight);

        $deletePlanet = new ActionConfig();
        $deletePlanet
            ->setName(ActionEnum::DELETE_PLANET->value)
            ->setActionName(ActionEnum::DELETE_PLANET)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::PLANET)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($deletePlanet);

        $leaveOrbit = new ActionConfig();
        $leaveOrbit
            ->setName(ActionEnum::LEAVE_ORBIT->value)
            ->setActionName(ActionEnum::LEAVE_ORBIT)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::NO_FUEL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::ARACK_PREVENTS_TRAVEL, VisibilityEnum::PUBLIC);
        $manager->persist($leaveOrbit);

        $takeoffToPlanet = new ActionConfig();
        $takeoffToPlanet
            ->setName(ActionEnum::TAKEOFF_TO_PLANET->value)
            ->setActionName(ActionEnum::TAKEOFF_TO_PLANET)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN)
            ->setOutputQuantity(4); // max number of explorators allowed
        $manager->persist($takeoffToPlanet);

        $takeoffToPlanetPatrolShip = new ActionConfig();
        $takeoffToPlanetPatrolShip
            ->setName(ActionEnum::TAKEOFF_TO_PLANET_PATROL_SHIP->value)
            ->setActionName(ActionEnum::TAKEOFF_TO_PLANET_PATROL_SHIP)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN)
            ->setOutputQuantity(1); // max number of explorators allowed
        $manager->persist($takeoffToPlanetPatrolShip);

        $changeNeronCpuPriority = new ActionConfig();
        $changeNeronCpuPriority
            ->setName(ActionEnum::CHANGE_NERON_CPU_PRIORITY->value)
            ->setActionName(ActionEnum::CHANGE_NERON_CPU_PRIORITY)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($changeNeronCpuPriority);

        $repairPilgred = new ActionConfig();
        $repairPilgred
            ->setName(ActionEnum::REPAIR_PILGRED->value)
            ->setActionName(ActionEnum::REPAIR_PILGRED)
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::PROJECT)
            ->setActionCost(2)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($repairPilgred);

        $returnToSol = new ActionConfig();
        $returnToSol
            ->setName(ActionEnum::RETURN_TO_SOL->value)
            ->setActionName(ActionEnum::RETURN_TO_SOL)
            ->setTypes([ActionTypeEnum::ACTION_CONFIRM])
            ->setRange(ActionRangeEnum::ROOM)
            ->setDisplayHolder(ActionHolderEnum::TERMINAL)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::HIDDEN)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($returnToSol);

        $participate = new ActionConfig();
        $participate
            ->setName(ActionEnum::PARTICIPATE->value)
            ->setActionName(ActionEnum::PARTICIPATE)
            ->setRange(ActionRangeEnum::ROOM)
            ->setTypes([ActionTypeEnum::ACTION_CONCEPTOR])
            ->setDisplayHolder(ActionHolderEnum::PROJECT)
            ->setActionCost(2)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::HIDDEN);
        $manager->persist($participate);

        $manager->flush();

        $this->addReference(self::SUICIDE, $suicide);
        $this->addReference(self::AUTO_DESTROY, $autoDestroy);
        $this->addReference(self::KILL_PLAYER, $killPlayer);

        $this->addReference(self::REJUVENATE_ALPHA, $rejuvenateAlpha);
        $this->addReference(self::RESET_SPECIALIST_POINT, $resetSpecializationPoint);
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
        $this->addReference(self::EXTINGUISH_DEFAULT, $extinguishAction);
        $this->addReference(self::TRY_KUBE, $tryKubeAction);
        $this->addReference(self::OPEN_SPACE_CAPSULE, $openSpaceCapsuleAction);
        $this->addReference(self::INJECT_SERUM, $injectSerumAction);
        $this->addReference(self::BANDAGE_DEFAULT, $bandageAction);
        $this->addReference(self::COOK_EXPRESS, $expressCookAction);
        $this->addReference(self::COOK_DEFAULT, $cookAction);
        $this->addReference(self::HEAL, $healAction);
        $this->addReference(self::SELF_HEAL, $selfHealAction);
        $this->addReference(self::HEAL_ULTRA, $ultraHealAction);
        $this->addReference(self::COMFORT_DEFAULT, $comfortAction);
        $this->addReference(self::WRITE, $writeAction);
        $this->addReference(self::SHRED, $shredAction);
        $this->addReference(self::HYPERFREEZE_DEFAULT, $hyperfreezeAction);
        $this->addReference(self::GAG_DEFAULT, $gagAction);
        $this->addReference(self::UNGAG_DEFAULT, $ungagAction);
        $this->addReference(self::SHOWER_DEFAULT, $showerAction);
        $this->addReference(self::WASH_IN_SINK, $sinkAction);
        $this->addReference(self::FUEL_INJECT, $fuelInjectAction);
        $this->addReference(self::FUEL_RETRIEVE, $retrieveFuelAction);
        $this->addReference(self::OXYGEN_INJECT, $oxygenInjectAction);
        $this->addReference(self::OXYGEN_RETRIEVE, $retrieveOxygenAction);
        $this->addReference(self::STRENGTHEN_HULL, $strengthenHullAction);
        $this->addReference(self::LIE_DOWN, $lieDownActon);
        $this->addReference(self::GET_UP, $getUpAction);
        $this->addReference(self::COFFEE_DEFAULT, $coffeeAction);
        $this->addReference(self::DISPENSE_DRUG, $dispenseAction);
        $this->addReference(self::TRANSPLANT_DEFAULT, $transplantAction);
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
        $this->addReference(self::REMOVE_SPORE, $removeSporeAction);
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
        $this->addReference(self::SHOOT_RANDOM_HUNTER_PATROL_SHIP, $shootRandomHunterTurret);
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
    }
}
