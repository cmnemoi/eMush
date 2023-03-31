<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;

class ActionsFixtures extends Fixture
{
    public const SUICIDE = 'suicide';
    public const AUTO_DESTROY = 'auto.destruction';
    public const KILL_PLAYER = 'kill.player';

    public const REJUVENATE_ALPHA = 'rejuvenate.alpha';
    public const UPDATING_TALKIE = 'updating.talkie';

    public const MOVE_DEFAULT = 'move.default';
    public const SEARCH_DEFAULT = 'search.default';
    public const HIT_DEFAULT = 'hit.default';
    public const HIDE_DEFAULT = 'hide.default';
    public const DEFAULT_TAKE = 'default.take';
    public const DEFAULT_DROP = 'default.drop';
    public const DO_THE_THING = 'do.the.thing';
    public const DRUG_CONSUME = 'drug.consume';
    public const RATION_CONSUME = 'ration.consume';
    public const BUILD_DEFAULT = 'build.default';
    public const READ_DOCUMENT = 'read.document';
    public const READ_BOOK = 'read.book';
    public const ATTACK_DEFAULT = 'attack.default';
    public const EXTINGUISH_DEFAULT = 'extinguish.default';
    public const TRY_KUBE = 'try.kube';
    public const OPEN_SPACE_CAPSULE = 'open.space.capsule';
    public const INJECT_SERUM = 'inject.serum';
    public const BANDAGE_DEFAULT = 'bandage.default';
    public const COOK_EXPRESS = 'cook.express';
    public const COOK_DEFAULT = 'cook.default';
    public const HEAL = 'heal';
    public const SELF_HEAL = 'self.heal';
    public const HEAL_ULTRA = 'heal.ultra';
    public const COMFORT_DEFAULT = 'comfort.default';
    public const WRITE_DEFAULT = 'write.default';
    public const GAG_DEFAULT = 'gag.default';
    public const UNGAG_DEFAULT = 'ungag.default';
    public const HYPERFREEZE_DEFAULT = 'hyperfreeze.default';
    public const SHOWER_DEFAULT = 'shower.default';
    public const WASH_IN_SINK = 'wash.in.sink';
    public const FLIRT_DEFAULT = 'flirt.default';
    public const FUEL_INJECT = 'fuel.inject';
    public const FUEL_RETRIEVE = 'fuel.retrieve';
    public const OXYGEN_INJECT = 'oxygen.inject';
    public const STRENGTHEN_HULL = 'strength_hull';
    public const OXYGEN_RETRIEVE = 'oxygen.retrieve';
    public const LIE_DOWN = 'lie.down';
    public const GET_UP = 'get.up';
    public const COFFEE_DEFAULT = 'coffee.default';
    public const DISPENSE_DRUG = 'dispense.drug';
    public const TRANSPLANT_DEFAULT = 'transplant.default';
    public const TREAT_PLANT = 'treat.plant';
    public const WATER_PLANT = 'water.plant';
    public const REPORT_EQUIPMENT = 'report.equipment';
    public const REPORT_FIRE = 'report.fire';
    public const INSTALL_CAMERA = 'install.camera';
    public const REMOVE_CAMERA = 'remove.camera';
    public const CHECK_SPORE_LEVEL = 'check.spore.level';
    public const EXAMINE_EQUIPMENT = 'examine.equipment';
    public const REMOVE_SPORE = 'remove.spore';
    public const PUBLIC_BROADCAST = 'public.broadcast';
    public const EXTINGUISH_MANUALLY = 'extinguish.manually';
    public const MOTIVATIONAL_SPEECH = 'motivational.speech';
    public const BORING_SPEECH = 'boring.speech';
    public const SURGERY = 'surgery';
    public const SELF_SURGERY = 'self.surgery';
    public const SHOOT = 'shoot';
    public const PLAY_ARCADE = 'play.arcade';
    public const SHOOT_HUNTER_TURRET = 'shoot.hunter.turret';

    public function load(ObjectManager $manager): void
    {
        // @TODO remove this after alpha
        $suicide = new Action();
        $suicide
            ->setName(ActionEnum::SUICIDE)
            ->setActionName(ActionEnum::SUICIDE)
            ->setScope(ActionScopeEnum::SELF)
        ;
        $manager->persist($suicide);

        $autoDestroy = new Action();
        $autoDestroy
            ->setName(ActionEnum::AUTO_DESTROY)
            ->setActionName(ActionEnum::AUTO_DESTROY)
            ->setScope(ActionScopeEnum::SELF)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;
        $manager->persist($autoDestroy);

        $killPlayer = new Action();
        $killPlayer
            ->setName(ActionEnum::KILL_PLAYER)
            ->setActionName(ActionEnum::KILL_PLAYER)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
        ;
        $manager->persist($killPlayer);

        $rejuvenateAlpha = new Action();
        $rejuvenateAlpha
            ->setActionName(ActionEnum::REJUVENATE)
            ->setScope(ActionScopeEnum::SELF)
            ->buildName(GameConfigEnum::ALPHA)
        ;
        $manager->persist($rejuvenateAlpha);

        $updatingTalkie = new Action();
        $updatingTalkie
            ->setName(ActionEnum::UPDATE_TALKIE)
            ->setActionName(ActionEnum::UPDATE_TALKIE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setInjuryRate(10)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($updatingTalkie);

        $moveAction = new Action();
        $moveAction
            ->setName(ActionEnum::MOVE)
            ->setActionName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setMovementCost(1)
        ;
        $manager->persist($moveAction);

        $searchAction = new Action();
        $searchAction
            ->setName(ActionEnum::SEARCH)
            ->setActionName(ActionEnum::SEARCH)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost(1)
        ;
        $manager->persist($searchAction);

        $hitAction = new Action();
        $hitAction
            ->setName(ActionEnum::HIT)
            ->setActionName(ActionEnum::HIT)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setSuccessRate(60)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::PUBLIC)
        ;
        $manager->persist($hitAction);

        $hideAction = new Action();
        $hideAction
            ->setName(ActionEnum::HIDE)
            ->setActionName(ActionEnum::HIDE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET)
        ;
        $manager->persist($hideAction);

        $takeItemAction = new Action();
        $takeItemAction
            ->setName(ActionEnum::TAKE)
            ->setActionName(ActionEnum::TAKE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
        ;

        $manager->persist($takeItemAction);

        $dropItemAction = new Action();
        $dropItemAction
            ->setName(ActionEnum::DROP)
            ->setActionName(ActionEnum::DROP)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($dropItemAction);

        $rationConsumeAction = new Action();
        $rationConsumeAction
            ->setName(ActionEnum::CONSUME)
            ->setActionName(ActionEnum::CONSUME)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET)
        ;

        $manager->persist($rationConsumeAction);

        $drugConsumeAction = new Action();
        $drugConsumeAction
            ->setName(ActionEnum::CONSUME_DRUG)
            ->setActionName(ActionEnum::CONSUME_DRUG)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET)
        ;

        $manager->persist($drugConsumeAction);

        $buildAction = new Action();
        $buildAction
            ->setName(ActionEnum::BUILD)
            ->setActionName(ActionEnum::BUILD)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(3)
            ->setDirtyRate(25)
            ->setInjuryRate(5)
        ;

        $manager->persist($buildAction);

        $readAction = new Action();
        $readAction
            ->setName(ActionEnum::READ_BOOK)
            ->setActionName(ActionEnum::READ_BOOK)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(2)
        ;

        $manager->persist($readAction);

        $readDocument = new Action();
        $readDocument
            ->setName(ActionEnum::READ_DOCUMENT)
            ->setActionName(ActionEnum::READ_DOCUMENT)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($readDocument);

        $attackAction = new Action();
        $attackAction
            ->setName(ActionEnum::ATTACK)
            ->setActionName(ActionEnum::ATTACK)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE, ActionTypeEnum::ACTION_ATTACK])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setSuccessRate(60)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::ONE_SHOT, VisibilityEnum::PUBLIC)
        ;

        $manager->persist($attackAction);

        $extinguishAction = new Action();
        $extinguishAction
            ->setName(ActionEnum::EXTINGUISH)
            ->setActionName(ActionEnum::EXTINGUISH)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setInjuryRate(1)
            ->setSuccessRate(50)
        ;

        $manager->persist($extinguishAction);

        $tryKubeAction = new Action();
        $tryKubeAction
            ->setName(ActionEnum::TRY_KUBE)
            ->setActionName(ActionEnum::TRY_KUBE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
        ;

        $manager->persist($tryKubeAction);

        $openSpaceCapsuleAction = new Action();
        $openSpaceCapsuleAction
            ->setName(ActionEnum::OPEN)
            ->setActionName(ActionEnum::OPEN)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setInjuryRate(1)
        ;

        $manager->persist($openSpaceCapsuleAction);

        $injectSerumAction = new Action();
        $injectSerumAction
            ->setName(ActionEnum::CURE)
            ->setActionName(ActionEnum::CURE)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
        ;

        $manager->persist($injectSerumAction);

        $bandageAction = new Action();
        $bandageAction
            ->setName(ActionEnum::USE_BANDAGE)
            ->setActionName(ActionEnum::USE_BANDAGE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setDirtyRate(5)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($bandageAction);

        $expressCookAction = new Action();
        $expressCookAction
            ->setName(ActionEnum::EXPRESS_COOK)
            ->setActionName(ActionEnum::EXPRESS_COOK)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
            ->setDirtyRate(20)
        ;

        $manager->persist($expressCookAction);

        $cookAction = new Action();
        $cookAction
            ->setName(ActionEnum::COOK)
            ->setActionName(ActionEnum::COOK)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
            ->setActionCost(1)
            ->setDirtyRate(20)
        ;

        $manager->persist($cookAction);

        $selfHealAction = new Action();
        $selfHealAction
            ->setName(ActionEnum::SELF_HEAL)
            ->setActionName(ActionEnum::SELF_HEAL)
            // ->setTypes([ActionTypeEnum::ACTION_HEAL])
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost(3)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($selfHealAction);

        $healAction = new Action();
        $healAction
            ->setName(ActionEnum::HEAL)
            ->setActionName(ActionEnum::HEAL)
            // ->setTypes([ActionTypeEnum::ACTION_HEAL])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(2)
        ;

        $manager->persist($healAction);

        $comfortAction = new Action();
        $comfortAction
            ->setName(ActionEnum::COMFORT)
            ->setActionName(ActionEnum::COMFORT)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setTypes([ActionTypeEnum::ACTION_SPOKEN])
            ->setActionCost(1)
        ;

        $manager->persist($comfortAction);

        $ultraHealAction = new Action();
        $ultraHealAction
            ->setName(ActionEnum::ULTRAHEAL)
            ->setActionName(ActionEnum::ULTRAHEAL)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($ultraHealAction);

        $writeAction = new Action();
        $writeAction
            ->setName(ActionEnum::WRITE)
            ->setActionName(ActionEnum::WRITE)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($writeAction);

        $hyperfreezeAction = new Action();
        $hyperfreezeAction
            ->setName(ActionEnum::HYPERFREEZE)
            ->setActionName(ActionEnum::HYPERFREEZE)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($hyperfreezeAction);

        $gagAction = new Action();
        $gagAction
            ->setName(ActionEnum::GAG)
            ->setActionName(ActionEnum::GAG)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
        ;

        $manager->persist($gagAction);

        $ungagAction = new Action();
        $ungagAction
            ->setName(ActionEnum::UNGAG)
            ->setActionName(ActionEnum::UNGAG)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost(1)
        ;

        $manager->persist($ungagAction);

        $showerAction = new Action();
        $showerAction
            ->setName(ActionEnum::SHOWER)
            ->setActionName(ActionEnum::SHOWER)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(2)
            ->setInjuryRate(2)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($showerAction);

        $sinkAction = new Action();
        $sinkAction
            ->setName(ActionEnum::WASH_IN_SINK)
            ->setActionName(ActionEnum::WASH_IN_SINK)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(3)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($sinkAction);

        $fuelInjectAction = new Action();
        $fuelInjectAction
            ->setName(ActionEnum::INSERT_FUEL)
            ->setActionName(ActionEnum::INSERT_FUEL)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
            ->setDirtyRate(10)
            ->setInjuryRate(1)
        ;

        $manager->persist($fuelInjectAction);

        $retrieveFuelAction = new Action();
        $retrieveFuelAction
            ->setName(ActionEnum::RETRIEVE_FUEL)
            ->setActionName(ActionEnum::RETRIEVE_FUEL)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET)
        ;

        $manager->persist($retrieveFuelAction);

        $oxygenInjectAction = new Action();
        $oxygenInjectAction
            ->setName(ActionEnum::INSERT_OXYGEN)
            ->setActionName(ActionEnum::INSERT_OXYGEN)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($oxygenInjectAction);

        $retrieveOxygenAction = new Action();
        $retrieveOxygenAction
            ->setName(ActionEnum::RETRIEVE_OXYGEN)
            ->setActionName(ActionEnum::RETRIEVE_OXYGEN)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::SECRET)
        ;

        $manager->persist($retrieveOxygenAction);

        $strengthenHullAction = new Action();
        $strengthenHullAction
            ->setName(ActionEnum::STRENGTHEN_HULL)
            ->setActionName(ActionEnum::STRENGTHEN_HULL)
            ->setTypes([ActionTypeEnum::ACTION_TECHNICIAN])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setTarget(GameItem::class)
            ->setActionCost(1)
            ->setDirtyRate(50)
            ->setInjuryRate(5)
            ->setSuccessRate(25)
        ;

        $manager->persist($strengthenHullAction);

        $lieDownActon = new Action();
        $lieDownActon
            ->setName(ActionEnum::LIE_DOWN)
            ->setActionName(ActionEnum::LIE_DOWN)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($lieDownActon);

        $getUpAction = new Action();
        $getUpAction
            ->setName(ActionEnum::GET_UP)
            ->setActionName(ActionEnum::GET_UP)
            ->setScope(ActionScopeEnum::SELF)
        ;

        $manager->persist($getUpAction);

        $coffeeAction = new Action();
        $coffeeAction
            ->setName(ActionEnum::COFFEE)
            ->setActionName(ActionEnum::COFFEE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(3)
        ;

        $manager->persist($coffeeAction);

        $dispenseAction = new Action();
        $dispenseAction
            ->setName(ActionEnum::DISPENSE)
            ->setActionName(ActionEnum::DISPENSE)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($dispenseAction);

        $transplantAction = new Action();
        $transplantAction
            ->setName(ActionEnum::TRANSPLANT)
            ->setActionName(ActionEnum::TRANSPLANT)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(2)
            ->setDirtyRate(15)
        ;

        $manager->persist($transplantAction);

        $treatPlantAction = new Action();
        $treatPlantAction
            ->setName(ActionEnum::TREAT_PLANT)
            ->setActionName(ActionEnum::TREAT_PLANT)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(2)
            ->setDirtyRate(15)
            ->setInjuryRate(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($treatPlantAction);

        $waterPlantAction = new Action();
        $waterPlantAction
            ->setName(ActionEnum::WATER_PLANT)
            ->setActionName(ActionEnum::WATER_PLANT)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setDirtyRate(15)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($waterPlantAction);

        $reportEquipmentAction = new Action();
        $reportEquipmentAction
            ->setName(ActionEnum::REPORT_EQUIPMENT)
            ->setActionName(ActionEnum::REPORT_EQUIPMENT)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($reportEquipmentAction);

        $reportFireAction = new Action();
        $reportFireAction
            ->setName(ActionEnum::REPORT_FIRE)
            ->setActionName(ActionEnum::REPORT_FIRE)
            ->setScope(ActionScopeEnum::SELF)
        ;

        $manager->persist($reportFireAction);

        $installCameraAction = new Action();
        $installCameraAction
            ->setName(ActionEnum::INSTALL_CAMERA)
            ->setActionName(ActionEnum::INSTALL_CAMERA)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(2)
            ->setDirtyRate(15)
        ;

        $manager->persist($installCameraAction);

        $removeCameraAction = new Action();
        $removeCameraAction
            ->setName(ActionEnum::REMOVE_CAMERA)
            ->setActionName(ActionEnum::REMOVE_CAMERA)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setDirtyRate(5)
        ;

        $manager->persist($removeCameraAction);

        $examineEquipmentAction = new Action();
        $examineEquipmentAction
            ->setName(ActionEnum::EXAMINE)
            ->setActionName(ActionEnum::EXAMINE)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($examineEquipmentAction);

        $checkSporeLevelAction = new Action();
        $checkSporeLevelAction
            ->setName(ActionEnum::CHECK_SPORE_LEVEL)
            ->setActionName(ActionEnum::CHECK_SPORE_LEVEL)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($checkSporeLevelAction);

        $flirtAction = new Action();
        $flirtAction
            ->setName(ActionEnum::FLIRT)
            ->setActionName(ActionEnum::FLIRT)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
        ;

        $manager->persist($flirtAction);

        $doTheThingAction = new Action();
        $doTheThingAction
            ->setName(ActionEnum::DO_THE_THING)
            ->setActionName(ActionEnum::DO_THE_THING)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(1)
        ;

        $manager->persist($doTheThingAction);

        $removeSporeAction = new Action();
        $removeSporeAction
            ->setName(ActionEnum::REMOVE_SPORE)
            ->setActionName(ActionEnum::REMOVE_SPORE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;

        $manager->persist($removeSporeAction);

        $publicBroadcastAction = new Action();
        $publicBroadcastAction
            ->setName(ActionEnum::PUBLIC_BROADCAST)
            ->setActionName(ActionEnum::PUBLIC_BROADCAST)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(2)
        ;

        $manager->persist($publicBroadcastAction);

        $extinguishManuallyAction = new Action();
        $extinguishManuallyAction
            ->setName(ActionEnum::EXTINGUISH_MANUALLY)
            ->setActionName(ActionEnum::EXTINGUISH_MANUALLY)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost(1)
            ->setDirtyRate(50)
            ->setInjuryRate(5)
            ->setSuccessRate(10)
        ;

        $manager->persist($extinguishManuallyAction);

        $motivationalSpeechAction = new Action();
        $motivationalSpeechAction
            ->setName(ActionEnum::MOTIVATIONAL_SPEECH)
            ->setActionName(ActionEnum::MOTIVATIONAL_SPEECH)
            ->setScope(ActionScopeEnum::SELF)
            ->setTypes([ActionTypeEnum::ACTION_SPOKEN])
            ->setActionCost(2)
        ;

        $manager->persist($motivationalSpeechAction);

        $boringSpeechAction = new Action();
        $boringSpeechAction
            ->setName(ActionEnum::BORING_SPEECH)
            ->setActionName(ActionEnum::BORING_SPEECH)
            ->setScope(ActionScopeEnum::SELF)
            ->setTypes([ActionTypeEnum::ACTION_SPOKEN])
            ->setActionCost(2)
        ;

        $manager->persist($boringSpeechAction);

        $surgeryAction = new Action();
        $surgeryAction
            ->setName(ActionEnum::SURGERY)
            ->setActionName(ActionEnum::SURGERY)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost(2)
            ->setDirtyRate(80)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
        ;

        $manager->persist($surgeryAction);

        $selfSurgeryAction = new Action();
        $selfSurgeryAction
            ->setName(ActionEnum::SELF_SURGERY)
            ->setActionName(ActionEnum::SELF_SURGERY)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(4)
            ->setDirtyRate(100)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
        ;

        $manager->persist($selfSurgeryAction);

        $shootAction = new Action();
        $shootAction
            ->setName(ActionEnum::SHOOT)
            ->setActionName(ActionEnum::SHOOT)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE, ActionTypeEnum::ACTION_SHOOT])
            ->setActionCost(1)
            ->setSuccessRate(50)
            ->setVisibility(ActionOutputEnum::FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_SUCCESS, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::CRITICAL_FAIL, VisibilityEnum::PUBLIC)
            ->setVisibility(ActionOutputEnum::ONE_SHOT, VisibilityEnum::PUBLIC)
        ;
        $manager->persist($shootAction);

        $playArcade = new Action();
        $playArcade
            ->setName(ActionEnum::PLAY_ARCADE)
            ->setActionName(ActionEnum::PLAY_ARCADE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost(1)
            ->setSuccessRate(33)
        ;
        $manager->persist($playArcade);

        $shootHunterTurret = new Action();
        $shootHunterTurret
            ->setName(ActionEnum::SHOOT_HUNTER)
            ->setActionName(ActionEnum::SHOOT_HUNTER)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setTypes([ActionTypeEnum::ACTION_AGGRESSIVE, ActionTypeEnum::ACTION_SHOOT])
            ->setActionCost(1)
            ->setSuccessRate(30)
        ;
        $manager->persist($shootHunterTurret);

        $manager->flush();

        $this->addReference(self::SUICIDE, $suicide);
        $this->addReference(self::AUTO_DESTROY, $autoDestroy);
        $this->addReference(self::KILL_PLAYER, $killPlayer);

        $this->addReference(self::REJUVENATE_ALPHA, $rejuvenateAlpha);
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
        $this->addReference(self::WRITE_DEFAULT, $writeAction);
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
    }
}
