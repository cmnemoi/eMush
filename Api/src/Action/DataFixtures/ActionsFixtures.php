<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\GameItem;

class ActionsFixtures extends Fixture
{
    public const MOVE_DEFAULT = 'move.default';
    public const SEARCH_DEFAULT = 'search.default';
    public const HIT_DEFAULT = 'hit.default';
    public const HIDE_DEFAULT = 'hide.default';
    public const DEFAULT_TAKE = 'default.take';
    public const DEFAULT_DROP = 'default.drop';
    public const DRUG_CONSUME = 'drug.consume';
    public const RATION_CONSUME = 'ration.consume';
    public const BUILD_DEFAULT = 'build.default';
    public const READ_DEFAULT = 'read.default';
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
    public const WRITE_DEFAULT = 'write.default';
    public const GAG_DEFAULT = 'gag.default';
    public const HYPERFREEZ_DEFAULT = 'hyperfreez.default';
    public const SHOWER_DEFAULT = 'shower.default';
    public const FUEL_INJECT = 'fuel.inject';
    public const FUEL_RETRIEVE = 'fuel.retrieve';
    public const OXYGEN_INJECT = 'oxygen.inject';
    public const OXYGEN_RETRIEVE = 'oxygen.retrieve';
    public const LIE_DOWN = 'lie.down';
    public const COFFEE_DEFAULT = 'coffee.default';
    public const TRANSPLANT_DEFAULT = 'transplant.default';
    public const TREAT_PLANT = 'treat.plant';
    public const WATER_PLANT = 'water.plant';

    public function load(ObjectManager $manager): void
    {
        $moveAction = new Action();
        $moveAction
            ->setName(ActionEnum::MOVE)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
        ;
        $manager->persist($moveAction);

        $searchAction = new Action();
        $searchAction
            ->setName(ActionEnum::SEARCH)
            ->setType([])
            ->setScope(ActionScopeEnum::SELF)
        ;
        $manager->persist($searchAction);

        $hitAction = new Action();
        $hitAction
            ->setName(ActionEnum::HIT)
            ->setType([])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(1)
        ;
        $manager->persist($hitAction);

        $hideAction = new Action();
        $hideAction
            ->setName(ActionEnum::HIDE)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
        ;
        $manager->persist($hideAction);

        $takeItemAction = new Action();
        $takeItemAction
            ->setName(ActionEnum::TAKE)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
        ;

        $manager->persist($takeItemAction);

        $dropItemAction = new Action();
        $dropItemAction
            ->setName(ActionEnum::DROP)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(1)
        ;

        $manager->persist($dropItemAction);

        $rationConsumeAction = new Action();
        $rationConsumeAction
            ->setName(ActionEnum::CONSUME)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setDirtyRate(50)
        ;

        $manager->persist($rationConsumeAction);

        $drugConsumeAction = new Action();
        $drugConsumeAction
            ->setName(ActionEnum::CONSUME)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setDirtyRate(10)
        ;

        $manager->persist($drugConsumeAction);

        $buildAction = new Action();
        $buildAction
            ->setName(ActionEnum::BUILD)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(25)
            ->setDirtyRate(50)
        ;

        $manager->persist($buildAction);

        $readAction = new Action();
        $readAction
            ->setName(ActionEnum::READ_BOOK)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setDirtyRate(0)
        ;

        $manager->persist($readAction);

        $attackAction = new Action();
        $attackAction
            ->setName(ActionEnum::ATTACK)
            ->setType([])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setDirtyRate(0)
        ;

        $manager->persist($attackAction);

        $extinguishAction = new Action();
        $extinguishAction
            ->setName(ActionEnum::EXTINGUISH)
            ->setType([])
            ->setScope(ActionScopeEnum::SELF)
            ->setInjuryRate(0)
            ->setDirtyRate(25)
        ;

        $manager->persist($extinguishAction);

        $tryKubeAction = new Action();
        $tryKubeAction
            ->setName(ActionEnum::TRY_THE_KUBE)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($tryKubeAction);

        $openSpaceCapsuleAction = new Action();
        $openSpaceCapsuleAction
            ->setName(ActionEnum::OPEN)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($openSpaceCapsuleAction);

        $injectSerumAction = new Action();
        $injectSerumAction
            ->setName(ActionEnum::CURE)
            ->setType([])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
        ;

        $manager->persist($injectSerumAction);

        $bandageAction = new Action();
        $bandageAction
            ->setName(ActionEnum::USE_BANDAGE)
            ->setType([])
            ->setScope(ActionScopeEnum::SELF)
        ;

        $manager->persist($bandageAction);

        $expressCookAction = new Action();
        $expressCookAction
            ->setName(ActionEnum::EXPRESS_COOK)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
        ;

        $manager->persist($expressCookAction);

        $cookAction = new Action();
        $cookAction
            ->setName(ActionEnum::COOK)
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
        ;

        $manager->persist($cookAction);

        $selfHealAction = new Action();
        $selfHealAction
            ->setName(ActionEnum::SELF_HEAL)
            ->setType([])
            ->setScope(ActionScopeEnum::SELF)
        ;

        $manager->persist($selfHealAction);

        $healAction = new Action();
        $healAction
            ->setName(ActionEnum::HEAL)
            ->setType([])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
        ;

        $manager->persist($healAction);

        $ultraHealAction = new Action();
        $ultraHealAction
            ->setName(ActionEnum::ULTRAHEAL)
            ->setType([])
            ->setScope(ActionScopeEnum::SELF)
        ;

        $manager->persist($ultraHealAction);

        $writeAction = new Action();
        $writeAction
            ->setName(ActionEnum::WRITE)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($writeAction);

        $hyperfreezAction = new Action();
        $hyperfreezAction
            ->setName(ActionEnum::HYPERFREEZE)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($hyperfreezAction);

        $gagAction = new Action();
        $gagAction
            ->setName(ActionEnum::GAG)
            ->setType([])
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
        ;

        $manager->persist($gagAction);

        $showerAction = new Action();
        $showerAction
            ->setName(ActionEnum::SHOWER)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($showerAction);

        $fuelInjectAction = new Action();
        $fuelInjectAction
            ->setName(ActionEnum::INSERT_FUEL)
            ->setType([])
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
        ;

        $manager->persist($fuelInjectAction);

        $retrieveFuelAction = new Action();
        $retrieveFuelAction
            ->setName(ActionEnum::RETRIEVE_FUEL)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($retrieveFuelAction);

        $oxygenInjectAction = new Action();
        $oxygenInjectAction
            ->setName(ActionEnum::INSERT_OXYGEN)
            ->setType([])
            ->setScope(ActionScopeEnum::ROOM)
            ->setTarget(GameItem::class)
        ;

        $manager->persist($oxygenInjectAction);

        $retrieveOxygenAction = new Action();
        $retrieveOxygenAction
            ->setName(ActionEnum::RETRIEVE_OXYGEN)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($retrieveOxygenAction);

        $lieDownActon = new Action();
        $lieDownActon
            ->setName(ActionEnum::LIE_DOWN)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($lieDownActon);

        $coffeeAction = new Action();
        $coffeeAction
            ->setName(ActionEnum::COFFEE)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(50)
        ;

        $manager->persist($coffeeAction);

        $transplantAction = new Action();
        $transplantAction
            ->setName(ActionEnum::TRANSPLANT)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        $manager->persist($transplantAction);

        $treatPlantAction = new Action();
        $treatPlantAction
            ->setName(ActionEnum::TREAT_PLANT)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(50)
        ;

        $manager->persist($treatPlantAction);

        $waterPlantAction = new Action();
        $waterPlantAction
            ->setName(ActionEnum::WATER_PLANT)
            ->setType([])
            ->setScope(ActionScopeEnum::CURRENT)
            ->setDirtyRate(50)
        ;

        $manager->persist($waterPlantAction);

        $manager->flush();

        $this->addReference(self::MOVE_DEFAULT, $moveAction);
        $this->addReference(self::SEARCH_DEFAULT, $searchAction);
        $this->addReference(self::HIT_DEFAULT, $hitAction);
        $this->addReference(self::HIDE_DEFAULT, $hideAction);
        $this->addReference(self::DEFAULT_TAKE, $takeItemAction);
        $this->addReference(self::DEFAULT_DROP, $dropItemAction);
        $this->addReference(self::RATION_CONSUME, $rationConsumeAction);
        $this->addReference(self::DRUG_CONSUME, $drugConsumeAction);
        $this->addReference(self::BUILD_DEFAULT, $buildAction);
        $this->addReference(self::READ_DEFAULT, $readAction);
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
        $this->addReference(self::WRITE_DEFAULT, $writeAction);
        $this->addReference(self::HYPERFREEZ_DEFAULT, $hyperfreezAction);
        $this->addReference(self::GAG_DEFAULT, $gagAction);
        $this->addReference(self::SHOWER_DEFAULT, $showerAction);
        $this->addReference(self::FUEL_INJECT, $fuelInjectAction);
        $this->addReference(self::FUEL_RETRIEVE, $retrieveFuelAction);
        $this->addReference(self::OXYGEN_INJECT, $oxygenInjectAction);
        $this->addReference(self::OXYGEN_RETRIEVE, $retrieveOxygenAction);
        $this->addReference(self::LIE_DOWN, $lieDownActon);
        $this->addReference(self::COFFEE_DEFAULT, $coffeeAction);
        $this->addReference(self::TRANSPLANT_DEFAULT, $transplantAction);
        $this->addReference(self::TREAT_PLANT, $treatPlantAction);
        $this->addReference(self::WATER_PLANT, $waterPlantAction);
    }
}
