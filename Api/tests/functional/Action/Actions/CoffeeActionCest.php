<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Coffee;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class CoffeeActionCest extends AbstractFunctionalTest
{
    private ActionConfig $coffeeActionConfig;
    private Coffee $coffeeAction;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->coffeeActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::COFFEE]);
        $this->coffeeAction = $I->grabService(Coffee::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testCanReach(FunctionalTester $I): void
    {
        $room1 = new Place();
        $room2 = new Place();

        $player = $this->createPlayer(new Daedalus(), $room1);

        $gameEquipment = $this->createCoffeeMachine($room2);

        $coffeeActionEntity = new ActionConfig();
        $coffeeActionEntity
            ->setActionName(ActionEnum::COFFEE)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setRange(ActionRangeEnum::SELF);

        $gameEquipment->getEquipment()->setActionConfigs(new ArrayCollection([$coffeeActionEntity]));

        $this->coffeeAction->loadParameters(
            actionConfig: $coffeeActionEntity,
            actionProvider: $gameEquipment,
            player: $player,
            target: $gameEquipment
        );

        $I->assertFalse($this->coffeeAction->isVisible());

        $gameEquipment->setHolder($room1);

        $I->assertTrue($this->coffeeAction->isVisible());
    }

    public function testBroken(FunctionalTester $I): void
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = $this->createCoffeeMachine($room);

        $coffeeActionEntity = new ActionConfig();
        $coffeeActionEntity
            ->setActionName(ActionEnum::COFFEE)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setRange(ActionRangeEnum::SELF);

        $this->coffeeAction->loadParameters(
            actionConfig: $coffeeActionEntity,
            actionProvider: $gameEquipment,
            player: $player,
            target: $gameEquipment
        );
        $gameEquipment->getEquipment()->setActionConfigs(new ArrayCollection([$coffeeActionEntity]));

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC);
        new Status($gameEquipment, $statusConfig);

        $I->assertEquals(ActionImpossibleCauseEnum::BROKEN_EQUIPMENT, $this->coffeeAction->cannotExecuteReason());
    }

    public function testNotCharged(FunctionalTester $I): void
    {
        $pilgred = ProjectFactory::createPilgredProject();
        $daedalus = $pilgred->getDaedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = $this->createCoffeeMachine($room);

        $coffeeActionEntity = new ActionConfig();
        $coffeeActionEntity
            ->setActionName(ActionEnum::COFFEE)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setRange(ActionRangeEnum::SELF);

        $this->coffeeAction->loadParameters(
            actionConfig: $coffeeActionEntity,
            actionProvider: $gameEquipment,
            player: $player,
            target: $gameEquipment
        );
        $gameEquipment->getEquipment()->setActionConfigs(new ArrayCollection([$coffeeActionEntity]));

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::HEAVY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategies([ActionEnum::COFFEE->value])
            ->setChargeStrategy(ChargeStrategyTypeEnum::COFFEE_MACHINE_CHARGE_INCREMENT);

        $chargeStatus = new ChargeStatus($gameEquipment, $statusConfig);
        $chargeStatus
            ->setCharge(0);

        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAILY_LIMIT,
            actual: $this->coffeeAction->cannotExecuteReason(),
        );
    }

    public function shouldGiveCoffeeAtEachCycleIfPilgredIsCompleted(FunctionalTester $I): void
    {
        // given I have a coffee machine in Chun's room
        $coffeeMachine = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COFFEE_MACHINE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given PILGRED is completed
        $this->daedalus->getPilgred()->makeProgress(100);

        // given Chun executes the coffee action
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $coffeeMachine,
            player: $this->chun,
            target: $coffeeMachine
        );
        $this->coffeeAction->execute();

        // when a cycle passes
        $statusEvent = new StatusCycleEvent(
            $coffeeMachine->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES),
            $coffeeMachine,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($statusEvent, StatusCycleEvent::STATUS_NEW_CYCLE);

        // then Chun should be able to execute the coffee action again
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $coffeeMachine,
            player: $this->chun,
            target: $coffeeMachine
        );
        $I->assertNull($this->coffeeAction->cannotExecuteReason());
    }

    public function shouldNotBeExecutableTwiceACycleIfPilgredIsCompleted(FunctionalTester $I): void
    {
        // given I have a coffee machine in Chun's room
        $coffeeMachine = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COFFEE_MACHINE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given PILGRED is completed
        $this->daedalus->getPilgred()->makeProgress(100);

        // when Chun executes the coffee action
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $coffeeMachine,
            player: $this->chun,
            target: $coffeeMachine
        );
        $this->coffeeAction->execute();

        // then Chun should not be able to execute the coffee action again
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $coffeeMachine,
            player: $this->chun,
            target: $coffeeMachine
        );
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::CYCLE_LIMIT,
            actual: $this->coffeeAction->cannotExecuteReason()
        );
    }

    public function shouldNotBeExecutableAfterOneCycleIfPilgredIsNotCompleted(FunctionalTester $I): void
    {
        // given Daedalus is Day 0 so coffee machine cannot break and make the test fail
        $this->daedalus->setDay(0);

        // given I have a coffee machine in Chun's room
        $coffeeMachine = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COFFEE_MACHINE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given PILGRED is not completed
        $this->daedalus->getPilgred()->makeProgress(0);

        // given Chun executes the coffee action
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $coffeeMachine,
            player: $this->chun,
            target: $coffeeMachine
        );
        $this->coffeeAction->execute();

        // when a cycle passes
        $daedalusEvent = new DaedalusCycleEvent(
            $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Chun should not be able to execute the coffee action again
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $coffeeMachine,
            player: $this->chun,
            target: $coffeeMachine
        );
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAILY_LIMIT,
            actual: $this->coffeeAction->cannotExecuteReason()
        );
    }

    public function shouldBeExecutableAtCycleFourWithFissionCoffeeRoasterProject(FunctionalTester $I): void
    {
        // given Daedalus is Day 0 so coffee machine cannot break and make the test fail
        $this->daedalus->setDay(0);

        // given Daedalus is at cycle 3
        $this->daedalus->setCycle(3);

        // given I have a coffee machine in Chun's room
        $coffeeMachine = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COFFEE_MACHINE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // given Fission Coffee Roaster project is completed
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::FISSION_COFFEE_ROASTER),
            author: $this->chun,
            I: $I
        );

        // given Chun executes the coffee action
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $coffeeMachine,
            player: $this->chun,
            target: $coffeeMachine
        );
        $this->coffeeAction->execute();

        // when a cycle passes
        $statusEvent = new DaedalusCycleEvent(
            $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($statusEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(4, $this->daedalus->getCycle());

        // then Chun should be able to execute the coffee action again
        $this->coffeeAction->loadParameters(
            actionConfig: $this->coffeeActionConfig,
            actionProvider: $coffeeMachine,
            player: $this->chun,
            target: $coffeeMachine
        );
        $I->assertNull($this->coffeeAction->cannotExecuteReason());
    }

    private function createPlayer(Daedalus $daedalus, Place $room): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setInitActionPoint(10)
            ->setInitMovementPoint(10)
            ->setInitMoralPoint(10);

        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
            ->setDaedalus($daedalus)
            ->setPlace($room);

        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);
        $player->setPlayerInfo($playerInfo);

        return $player;
    }

    private function createCoffeeMachine(Place $place): GameEquipment
    {
        $tool = new Tool();
        $tool->setActions([$this->coffeeActionConfig])->setName('tool_coffee_test');

        $gameEquipment = new GameEquipment($place);
        $equipment = new EquipmentConfig();
        $equipment->setEquipmentName(EquipmentEnum::COFFEE_MACHINE)->setMechanics([$tool]);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName(EquipmentEnum::COFFEE_MACHINE);

        return $gameEquipment;
    }
}
