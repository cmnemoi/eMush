<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\GameItem;

class ActionsFixtures extends Fixture implements DependentFixtureInterface
{
    public const REJUVENATE_ALPHA = 'rejuvenate.alpha';

    public const MOVE_DEFAULT = 'move.default';
    public const SEARCH_DEFAULT = 'search.default';
    public const HIT_DEFAULT = 'hit.default';
    public const HIDE_DEFAULT = 'hide.default';
    public const DEFAULT_TAKE = 'default.take';
    public const DEFAULT_DROP = 'default.drop';
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
    public const HEAL_DEFAULT = 'heal.default';
    public const HEAL_SELF = 'heal.self';
    public const HEAL_ULTRA = 'heal.ultra';
    public const COMFORT_DEFAULT = 'confort.default';
    public const WRITE_DEFAULT = 'write.default';
    public const GAG_DEFAULT = 'gag.default';
    public const HYPERFREEZE_DEFAULT = 'hyperfreeze.default';
    public const SHOWER_DEFAULT = 'shower.default';
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
    public const SPREAD_FIRE = 'spread.fire';

    public const EXTRACT_SPORE = 'extract.spore';
    public const INFECT_PLAYER = 'infect.player';

    public function load(ObjectManager $manager): void
    {
        /** @var ActionCost $freeCost */
        $freeCost = $this->getReference(ActionCostFixture::ACTION_COST_FREE);
        /** @var ActionCost $oneActionPointCost */
        $oneActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_ONE_ACTION);
        /** @var ActionCost $twoActionPointCost */
        $twoActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_TWO_ACTION);
        /** @var ActionCost $threeActionPointCost */
        $threeActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_THREE_ACTION);
        /** @var ActionCost $fourActionPointCost */
        $fourActionPointCost = $this->getReference(ActionCostFixture::ACTION_COST_FOUR_ACTION);
        /** @var ActionCost $oneMovementPointCost */
        $oneMovementPointCost = $this->getReference(ActionCostFixture::ACTION_COST_ONE_MOVEMENT);

        //@TODO remove this after alpha
        $rejuvenateAlpha = new Action();
        $rejuvenateAlpha
            ->setName(ActionEnum::REJUVENATE_ALPHA)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($freeCost)
        ;
        $manager->persist($rejuvenateAlpha);

        $moveAction = new Action();
        $moveAction
            ->setName(ActionEnum::MOVE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($oneMovementPointCost)
        ;
        $manager->persist($moveAction);

        $searchAction = new Action();
        $searchAction
            ->setName(ActionEnum::SEARCH)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($oneActionPointCost)
        ;
        $manager->persist($searchAction);

        $hitAction = new Action();
        $hitAction
            ->setName(ActionEnum::HIT)
            ->setTypes([]) //@TODO
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(1)
            ->setActionCost($oneActionPointCost)
            ->setSuccessRate(60)
        ;
        $manager->persist($hitAction);

        $hideAction = new Action();
        $hideAction
            ->setName(ActionEnum::HIDE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setTarget(GameItem::class)
            ->setActionCost($oneActionPointCost)
        ;
        $manager->persist($hideAction);

        $takeItemAction = new Action();
        $takeItemAction
            ->setName(ActionEnum::TAKE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
            ->setActionCost($freeCost)
        ;

        $manager->persist($takeItemAction);

        $dropItemAction = new Action();
        $dropItemAction
            ->setName(ActionEnum::DROP)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
            ->setActionCost($freeCost)
        ;

        $manager->persist($dropItemAction);

        $rationConsumeAction = new Action();
        $rationConsumeAction
            ->setName(ActionEnum::CONSUME)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setDirtyRate(50)
            ->setActionCost($freeCost)
        ;

        $manager->persist($rationConsumeAction);

        $drugConsumeAction = new Action();
        $drugConsumeAction
            ->setName(ActionEnum::CONSUME_DRUG)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setDirtyRate(10)
            ->setActionCost($freeCost)
        ;

        $manager->persist($drugConsumeAction);

        $buildAction = new Action();
        $buildAction
            ->setName(ActionEnum::BUILD)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(25)
            ->setDirtyRate(50)
            ->setActionCost($threeActionPointCost)
        ;

        $manager->persist($buildAction);

        $readAction = new Action();
        $readAction
            ->setName(ActionEnum::READ_BOOK)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setDirtyRate(0)
            ->setActionCost($twoActionPointCost)
        ;

        $manager->persist($readAction);

        $readDocument = new Action();
        $readDocument
            ->setName(ActionEnum::READ_DOCUMENT)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setDirtyRate(0)
            ->setActionCost($freeCost)
        ;

        $manager->persist($readDocument);

        $attackAction = new Action();
        $attackAction
            ->setName(ActionEnum::ATTACK)
            ->setTypes([]) //@TODO
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setDirtyRate(0)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($attackAction);

        $extinguishAction = new Action();
        $extinguishAction
            ->setName(ActionEnum::EXTINGUISH)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setSuccessRate(50)
            ->setInjuryRate(10)
            ->setDirtyRate(0)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($extinguishAction);

        $tryKubeAction = new Action();
        $tryKubeAction
            ->setName(ActionEnum::TRY_THE_KUBE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($tryKubeAction);

        $openSpaceCapsuleAction = new Action();
        $openSpaceCapsuleAction
            ->setName(ActionEnum::OPEN)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($openSpaceCapsuleAction);

        $injectSerumAction = new Action();
        $injectSerumAction
            ->setName(ActionEnum::CURE)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($injectSerumAction);

        $bandageAction = new Action();
        $bandageAction
            ->setName(ActionEnum::USE_BANDAGE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($bandageAction);

        $expressCookAction = new Action();
        $expressCookAction
            ->setName(ActionEnum::EXPRESS_COOK)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
            ->setActionCost($freeCost)
            ->setDirtyRate(50)
        ;

        $manager->persist($expressCookAction);

        $cookAction = new Action();
        $cookAction
            ->setName(ActionEnum::COOK)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
            ->setActionCost($oneActionPointCost)
            ->setDirtyRate(50)
        ;

        $manager->persist($cookAction);

        $selfHealAction = new Action();
        $selfHealAction
            ->setName(ActionEnum::SELF_HEAL)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($threeActionPointCost)
        ;

        $manager->persist($selfHealAction);

        $healAction = new Action();
        $healAction
            ->setName(ActionEnum::HEAL)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($twoActionPointCost)
        ;

        $manager->persist($healAction);

        $comfortAction = new Action();
        $comfortAction
            ->setName(ActionEnum::COMFORT)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($comfortAction);

        $ultraHealAction = new Action();
        $ultraHealAction
            ->setName(ActionEnum::ULTRAHEAL)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($freeCost)
        ;

        $manager->persist($ultraHealAction);

        $writeAction = new Action();
        $writeAction
            ->setName(ActionEnum::WRITE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($freeCost)
        ;

        $manager->persist($writeAction);

        $hyperfreezeAction = new Action();
        $hyperfreezeAction
            ->setName(ActionEnum::HYPERFREEZE)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($hyperfreezeAction);

        $gagAction = new Action();
        $gagAction
            ->setName(ActionEnum::GAG)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($gagAction);

        $showerAction = new Action();
        $showerAction
            ->setName(ActionEnum::SHOWER)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($twoActionPointCost)
        ;

        $manager->persist($showerAction);

        $fuelInjectAction = new Action();
        $fuelInjectAction
            ->setName(ActionEnum::INSERT_FUEL)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
            ->setDirtyRate(50)
            ->setActionCost($freeCost);

        $manager->persist($fuelInjectAction);

        $retrieveFuelAction = new Action();
        $retrieveFuelAction
            ->setName(ActionEnum::RETRIEVE_FUEL)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(50)
            ->setInjuryRate(5)
            ->setActionCost($freeCost);

        $manager->persist($retrieveFuelAction);

        $oxygenInjectAction = new Action();
        $oxygenInjectAction
            ->setName(ActionEnum::INSERT_OXYGEN)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
            ->setActionCost($freeCost)
        ;

        $manager->persist($oxygenInjectAction);

        $retrieveOxygenAction = new Action();
        $retrieveOxygenAction
            ->setName(ActionEnum::RETRIEVE_OXYGEN)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($freeCost)
        ;

        $manager->persist($retrieveOxygenAction);

        $strengthenHullAction = new Action();
        $strengthenHullAction
            ->setName(ActionEnum::STRENGTHEN_HULL)
            ->setScope(ActionScopeEnum::SELF)
            ->setTarget(GameItem::class)
            ->setDirtyRate(50)
            ->setInjuryRate(25)
            ->setActionCost($oneActionPointCost)
            ->setSuccessRate(25)
        ;

        $manager->persist($strengthenHullAction);

        $lieDownActon = new Action();
        $lieDownActon
            ->setName(ActionEnum::LIE_DOWN)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($freeCost)
        ;

        $manager->persist($lieDownActon);

        $getUpAction = new Action();
        $getUpAction
            ->setName(ActionEnum::GET_UP)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($freeCost)
        ;

        $manager->persist($getUpAction);

        $coffeeAction = new Action();
        $coffeeAction
            ->setName(ActionEnum::COFFEE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(50)
            ->setActionCost($freeCost)
        ;

        $manager->persist($coffeeAction);

        $dispenseAction = new Action();
        $dispenseAction
            ->setName(ActionEnum::DISPENSE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($freeCost)
        ;

        $manager->persist($dispenseAction);

        $transplantAction = new Action();
        $transplantAction
            ->setName(ActionEnum::TRANSPLANT)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($twoActionPointCost)
        ;

        $manager->persist($transplantAction);

        $treatPlantAction = new Action();
        $treatPlantAction
            ->setName(ActionEnum::TREAT_PLANT)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(50)
            ->setActionCost($twoActionPointCost)
        ;

        $manager->persist($treatPlantAction);

        $waterPlantAction = new Action();
        $waterPlantAction
            ->setName(ActionEnum::WATER_PLANT)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(50)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($waterPlantAction);

        $extractSporeAction = new Action();
        $extractSporeAction
            ->setName(ActionEnum::EXTRACT_SPORE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($twoActionPointCost)
            ->setDirtyRate(101)
        ;

        $manager->persist($extractSporeAction);

        $infectAction = new Action();
        $infectAction
            ->setName(ActionEnum::INFECT)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setActionCost($oneActionPointCost)
        ;

        $manager->persist($infectAction);

        $reportEquipmentAction = new Action();
        $reportEquipmentAction
            ->setName(ActionEnum::REPORT_EQUIPMENT)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($freeCost)
        ;

        $manager->persist($reportEquipmentAction);

        $reportFireAction = new Action();
        $reportFireAction
            ->setName(ActionEnum::REPORT_FIRE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($freeCost)
        ;

        $manager->persist($reportFireAction);

        $spreadFireAction = new Action();
        $spreadFireAction
            ->setName(ActionEnum::SPREAD_FIRE)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($fourActionPointCost)
        ;

        $manager->persist($spreadFireAction);

        $manager->flush();

        $this->addReference(self::REJUVENATE_ALPHA, $rejuvenateAlpha);

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
        $this->addReference(self::HEAL_DEFAULT, $healAction);
        $this->addReference(self::HEAL_SELF, $selfHealAction);
        $this->addReference(self::HEAL_ULTRA, $ultraHealAction);
        $this->addReference(self::COMFORT_DEFAULT, $comfortAction);
        $this->addReference(self::WRITE_DEFAULT, $writeAction);
        $this->addReference(self::HYPERFREEZE_DEFAULT, $hyperfreezeAction);
        $this->addReference(self::GAG_DEFAULT, $gagAction);
        $this->addReference(self::SHOWER_DEFAULT, $showerAction);
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
        $this->addReference(self::EXTRACT_SPORE, $extractSporeAction);
        $this->addReference(self::INFECT_PLAYER, $infectAction);
        $this->addReference(self::REPORT_FIRE, $reportFireAction);
        $this->addReference(self::REPORT_EQUIPMENT, $reportEquipmentAction);
        $this->addReference(self::SPREAD_FIRE, $spreadFireAction);
    }

    public function getDependencies(): array
    {
        return [
            ActionCostFixture::class,
        ];
    }
}
