<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Coffee;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
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
    private Action $coffeeActionConfig;
    private Coffee $coffeeAction;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->coffeeActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::COFFEE]);
        $this->coffeeAction = $I->grabService(Coffee::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testCanReach(FunctionalTester $I): void
    {
        $room1 = new Place();
        $room2 = new Place();

        $player = $this->createPlayer(new Daedalus(), $room1);

        $gameEquipment = $this->createEquipment('coffee_machine', $room2);

        $coffeeActionEntity = new Action();
        $coffeeActionEntity
            ->setActionName(ActionEnum::COFFEE);

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$coffeeActionEntity]));

        $this->coffeeAction->loadParameters($coffeeActionEntity, $player, $gameEquipment);

        $I->assertFalse($this->coffeeAction->isVisible());

        $gameEquipment->setHolder($room1);

        $I->assertTrue($this->coffeeAction->isVisible());
    }

    public function testHasAction(FunctionalTester $I): void
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = $this->createEquipment('coffee_machine', $room);

        $coffeeActionEntity = new Action();
        $coffeeActionEntity->setActionName(ActionEnum::COFFEE);

        $this->coffeeAction->loadParameters($coffeeActionEntity, $player, $gameEquipment);

        $I->assertFalse($this->coffeeAction->isVisible());

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$coffeeActionEntity]));

        $I->assertTrue($this->coffeeAction->isVisible());
    }

    public function testBroken(FunctionalTester $I): void
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = $this->createEquipment('coffee_machine', $room);

        $coffeeActionEntity = new Action();
        $coffeeActionEntity->setActionName(ActionEnum::COFFEE);

        $this->coffeeAction->loadParameters($coffeeActionEntity, $player, $gameEquipment);

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$coffeeActionEntity]));

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

        $gameEquipment = $this->createEquipment('coffee_machine', $room);

        $coffeeActionEntity = new Action();
        $coffeeActionEntity->setActionName(ActionEnum::COFFEE);

        $this->coffeeAction->loadParameters($coffeeActionEntity, $player, $gameEquipment);

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$coffeeActionEntity]));

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::HEAVY)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategies([ActionEnum::COFFEE])
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
        $this->coffeeAction->loadParameters($this->coffeeActionConfig, $this->chun, $coffeeMachine);
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
        $this->coffeeAction->loadParameters($this->coffeeActionConfig, $this->chun, $coffeeMachine);
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
        $this->coffeeAction->loadParameters($this->coffeeActionConfig, $this->chun, $coffeeMachine);
        $this->coffeeAction->execute();

        // then Chun should not be able to execute the coffee action again
        $this->coffeeAction->loadParameters($this->coffeeActionConfig, $this->chun, $coffeeMachine);
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::CYCLE_LIMIT,
            actual: $this->coffeeAction->cannotExecuteReason()
        );
    }

    public function shouldNotBeExecutableAfterOneCycleIfPilgredIsNotCompleted(FunctionalTester $I): void
    {
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
        $this->coffeeAction->loadParameters($this->coffeeActionConfig, $this->chun, $coffeeMachine);
        $this->coffeeAction->execute();

        // when a cycle passes
        $daedalusEvent = new DaedalusCycleEvent(
            $this->daedalus,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Chun should not be able to execute the coffee action again
        $this->coffeeAction->loadParameters($this->coffeeActionConfig, $this->chun, $coffeeMachine);
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAILY_LIMIT,
            actual: $this->coffeeAction->cannotExecuteReason()
        );
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

    private function createEquipment(string $name, Place $place): GameEquipment
    {
        $gameEquipment = new GameEquipment($place);
        $equipment = new EquipmentConfig();
        $equipment->setEquipmentName($name);
        $gameEquipment
            ->setEquipment($equipment)
            ->setName($name);

        return $gameEquipment;
    }
}
