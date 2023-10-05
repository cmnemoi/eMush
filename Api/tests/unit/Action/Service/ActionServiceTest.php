<?php

namespace Mush\Tests\unit\Action\Service;

use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Action\Repository\ActionRepository;
use Mush\Action\Service\ActionService;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActionServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;
    /** @var ActionRepository|Mockery\Mock */
    private ActionRepository $actionRepository;

    /** @var ValidatorInterface|Mockery\Mock */
    protected ValidatorInterface $validator;

    /** @var ActionServiceInterface|Mockery\Mock */
    protected ActionServiceInterface $actionService;

    private ActionServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->actionRepository = \Mockery::mock(ActionRepository::class);

        $this->actionService = \Mockery::mock(ActionServiceInterface::class);
        $this->validator = \Mockery::mock(ValidatorInterface::class);

        $this->service = new ActionService(
            $this->eventService,
            $this->actionRepository
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testApplyCostToPlayerVariousPoints()
    {
        // mixed cost
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(1, 3, 5);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::ACTION_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 1
            ))
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::MORAL_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 5
            ))
            ->once()
        ;

        $movementApplyEvent = new ActionVariableEvent(
            $action,
            PlayerVariableEnum::MOVEMENT_POINT,
            $action->getGameVariables()->getValueByName(PlayerVariableEnum::MOVEMENT_POINT),
            $player,
            null
        );

        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn($movementApplyEvent)
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::MOVEMENT_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 3
            ))
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')
            ->with($movementApplyEvent, ActionVariableEvent::APPLY_COST)
            ->once()
        ;

        $result = $this->service->applyCostToPlayer($player, $action, null);

        $this->assertEquals($player, $result);
    }

    public function testApplyCostToPlayerSingleMovementConversion()
    {
        // mixed cost
        $player = $this->createPlayer(5, 0, 5);
        $action = $this->createAction(0, 1, 0);

        $convertActionToMovement = $this->createAction(0, 1, 0);
        $convertActionToMovement->setActionName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT);
        $convertActionToMovement->getGameVariables()->setValuesByName(['value' => 1, 'min_value' => 0, 'max_value' => null], PlayerVariableEnum::ACTION_POINT);
        $convertActionToMovement->getGameVariables()->setValuesByName(['value' => -2, 'min_value' => null, 'max_value' => 0], PlayerVariableEnum::MOVEMENT_POINT);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::ACTION_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 0
            ))
            ->once()
        ;
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::MORAL_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 0
            ))
            ->once()
        ;

        $movementApplyEvent = new ActionVariableEvent(
            $action,
            PlayerVariableEnum::MOVEMENT_POINT,
            1,
            $player,
            null
        );

        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn($movementApplyEvent)
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::MOVEMENT_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 1
            ))
            ->once()
        ;

        // Start conversion
        $this->actionRepository->shouldReceive('findOneBy')
            ->with(['actionName' => ActionEnum::CONVERT_ACTION_TO_MOVEMENT])
            ->andReturn($convertActionToMovement)
            ->once()
        ;
        $movementConversionEvent = new ActionVariableEvent(
            $convertActionToMovement,
            PlayerVariableEnum::MOVEMENT_POINT,
            -2,
            $player,
            null
        );
        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn($movementConversionEvent)
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::MOVEMENT_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $convertActionToMovement
                && $actionEvent->getRoundedQuantity() === -2
                && in_array(ActionEnum::CONVERT_ACTION_TO_MOVEMENT, $actionEvent->getTags())
            ))
            ->once()
        ;

        $actionConversionEvent = new ActionVariableEvent(
            $convertActionToMovement,
            PlayerVariableEnum::ACTION_POINT,
            1,
            $player,
            null
        );
        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn($actionConversionEvent)
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::ACTION_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $convertActionToMovement
                && $actionEvent->getRoundedQuantity() === 1
                && in_array(ActionEnum::CONVERT_ACTION_TO_MOVEMENT, $actionEvent->getTags())
            ))
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')
            ->with($movementConversionEvent, ActionVariableEvent::APPLY_COST)
            ->once()
        ;
        $this->eventService->shouldReceive('callEvent')
            ->with($actionConversionEvent, ActionVariableEvent::APPLY_COST)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')
            ->with($movementApplyEvent, ActionVariableEvent::APPLY_COST)
            ->once()
        ;

        $result = $this->service->applyCostToPlayer($player, $action, null);

        $this->assertEquals($player, $result);
    }

    public function testApplyCostToPlayerTwoMovementConversion()
    {
        // mixed cost
        $player = $this->createPlayer(5, 0, 5);
        $action = $this->createAction(0, 1, 0);

        $convertActionToMovement = $this->createAction(0, 1, 0);
        $convertActionToMovement->setActionName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT);
        $convertActionToMovement->getGameVariables()->setValuesByName(['value' => 1, 'min_value' => 0, 'max_value' => null], PlayerVariableEnum::ACTION_POINT);
        $convertActionToMovement->getGameVariables()->setValuesByName(['value' => -2, 'min_value' => null, 'max_value' => 0], PlayerVariableEnum::MOVEMENT_POINT);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::ACTION_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 0
            ))
            ->once()
        ;
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::MORAL_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 0
            ))
            ->once()
        ;

        $movementApplyEvent = new ActionVariableEvent(
            $action,
            PlayerVariableEnum::MOVEMENT_POINT,
            2,
            $player,
            null
        );

        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn($movementApplyEvent)
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::MOVEMENT_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 1
            ))
            ->once()
        ;

        // Start conversion
        $this->actionRepository->shouldReceive('findOneBy')
            ->with(['actionName' => ActionEnum::CONVERT_ACTION_TO_MOVEMENT])
            ->andReturn($convertActionToMovement)
            ->once()
        ;
        $movementConversionEvent = new ActionVariableEvent(
            $convertActionToMovement,
            PlayerVariableEnum::MOVEMENT_POINT,
            -1,
            $player,
            null
        );
        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn($movementConversionEvent)
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::MOVEMENT_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $convertActionToMovement
                && $actionEvent->getRoundedQuantity() === -2
                && in_array(ActionEnum::CONVERT_ACTION_TO_MOVEMENT, $actionEvent->getTags())
            ))
            ->once()
        ;

        $actionConversionEvent = new ActionVariableEvent(
            $convertActionToMovement,
            PlayerVariableEnum::ACTION_POINT,
            1,
            $player,
            null
        );
        $this->eventService->shouldReceive('computeEventModifications')
            ->andReturn($actionConversionEvent)
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::ACTION_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $convertActionToMovement
                && $actionEvent->getRoundedQuantity() === 1
                && in_array(ActionEnum::CONVERT_ACTION_TO_MOVEMENT, $actionEvent->getTags())
            ))
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')
            ->with($movementConversionEvent, ActionVariableEvent::APPLY_COST)
            ->twice()
        ;
        $this->eventService->shouldReceive('callEvent')
            ->with($actionConversionEvent, ActionVariableEvent::APPLY_COST)
            ->twice()
        ;

        $this->eventService->shouldReceive('callEvent')
            ->with($movementApplyEvent, ActionVariableEvent::APPLY_COST)
            ->once()
        ;

        $result = $this->service->applyCostToPlayer($player, $action, null);

        $this->assertEquals($player, $result);
    }

    public function testGetActionModifiedActionVariablePercentage()
    {
        $player = $this->createPlayer(5, 5, 5);

        $action = $this->createAction(0, 1, 0, 20);

        $actionModifiedEvent = new ActionVariableEvent(
            $action,
            ActionVariableEnum::PERCENTAGE_SUCCESS,
            30,
            $player,
            null
        );
        $this->eventService->shouldReceive('computeEventModifications')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::ROLL_ACTION_PERCENTAGE
                && $actionEvent->getVariableName() === ActionVariableEnum::PERCENTAGE_SUCCESS
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 20
            ))
            ->andReturn($actionModifiedEvent)
            ->once()
        ;

        $result = $this->service->getActionModifiedActionVariable(
            $player,
            $action,
            null,
            ActionVariableEnum::PERCENTAGE_SUCCESS
        );
        $this->assertEquals(30, $result);

        // more than 99%
        $actionModifiedEvent = new ActionVariableEvent(
            $action,
            ActionVariableEnum::PERCENTAGE_SUCCESS,
            234,
            $player,
            null
        );
        $this->eventService->shouldReceive('computeEventModifications')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::ROLL_ACTION_PERCENTAGE
                && $actionEvent->getVariableName() === ActionVariableEnum::PERCENTAGE_SUCCESS
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 20
            ))
            ->andReturn($actionModifiedEvent)
            ->once()
        ;

        $result = $this->service->getActionModifiedActionVariable(
            $player,
            $action,
            null,
            ActionVariableEnum::PERCENTAGE_SUCCESS
        );

        $this->assertEquals(99, $result);
    }

    public function testGetActionModifiedActionVariableMovementPoints()
    {
        $player = $this->createPlayer(5, 5, 5);

        $action = $this->createAction(0, 2, 0);

        $actionModifiedEvent = new ActionVariableEvent(
            $action,
            PlayerVariableEnum::ACTION_POINT,
            3,
            $player,
            null
        );
        $this->eventService->shouldReceive('computeEventModifications')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::MOVEMENT_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 2
            ))
            ->andReturn($actionModifiedEvent)
            ->once()
        ;

        $this->assertEquals(3, $this->service->getActionModifiedActionVariable(
            $player,
            $action,
            null,
            PlayerVariableEnum::MOVEMENT_POINT
        ));

        // reduce cost bellow 0
        $player = $this->createPlayer(5, 5, 5);
        $action = $this->createAction(0, 0, 0, 20);

        $actionModifiedEvent = new ActionVariableEvent(
            $action,
            PlayerVariableEnum::MOVEMENT_POINT,
            -1,
            $player,
            null
        );
        $this->eventService->shouldReceive('computeEventModifications')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::MOVEMENT_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 0
            ))
            ->andReturn($actionModifiedEvent)
            ->once()
        ;

        $this->assertEquals(0, $this->service->getActionModifiedActionVariable(
            $player,
            $action,
            null,
            PlayerVariableEnum::MOVEMENT_POINT
        ));
    }

    public function testGetActionModifiedActionVariableActionPointsNoConversion()
    {
        $player = $this->createPlayer(5, 5, 5);

        $action = $this->createAction(0, 5, 0);

        $actionModifiedEvent = new ActionVariableEvent(
            $action,
            PlayerVariableEnum::ACTION_POINT,
            1,
            $player,
            null
        );
        $this->eventService->shouldReceive('computeEventModifications')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::ACTION_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 0
            ))
            ->andReturn($actionModifiedEvent)
            ->once()
        ;

        // Now check if action points are needed for a conversion event
        $actionModifiedEvent = new ActionVariableEvent(
            $action,
            PlayerVariableEnum::MOVEMENT_POINT,
            0,
            $player,
            null
        );

        $this->assertEquals(1, $this->service->getActionModifiedActionVariable(
            $player,
            $action,
            null,
            PlayerVariableEnum::ACTION_POINT
        ));
    }

    public function testGetActionModifiedActionVariableActionPointsWithConversion()
    {
        $player = $this->createPlayer(5, 5, 5);

        $action = $this->createAction(0, 5, 0);

        $convertActionToMovement = $this->createAction(0, 1, 0);
        $convertActionToMovement->setActionName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT);
        $convertActionToMovement->getGameVariables()->setValuesByName(['value' => 1, 'min_value' => 0, 'max_value' => null], PlayerVariableEnum::ACTION_POINT);
        $convertActionToMovement->getGameVariables()->setValuesByName(['value' => -2, 'min_value' => null, 'max_value' => 0], PlayerVariableEnum::MOVEMENT_POINT);

        $actionModifiedEvent = new ActionVariableEvent(
            $action,
            PlayerVariableEnum::ACTION_POINT,
            1,
            $player,
            null
        );
        $this->eventService->shouldReceive('computeEventModifications')
            ->withArgs(fn (ActionVariableEvent $actionEvent, string $eventName) => (
                $eventName === ActionVariableEvent::APPLY_COST
                && $actionEvent->getVariableName() === PlayerVariableEnum::ACTION_POINT
                && $actionEvent->getAuthor() === $player
                && $actionEvent->getAction() === $action
                && $actionEvent->getRoundedQuantity() === 0
            ))
            ->andReturn($actionModifiedEvent)
            ->once()
        ;

        $this->eventService->shouldReceive('callEvent')
            ->never()
        ;

        $this->assertEquals(1, $this->service->getActionModifiedActionVariable(
            $player,
            $action,
            null,
            PlayerVariableEnum::ACTION_POINT
        ));
    }

    private function createPlayer(int $actionPoint, int $movementPoint, int $moralPoint): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setInitActionPoint($actionPoint)
            ->setMaxActionPoint(12)
            ->setInitMoralPoint($moralPoint)
            ->setMaxMoralPoint(12)
            ->setMaxMovementPoint(12)
            ->setInitMovementPoint($movementPoint)
        ;
        $daedalus = new Daedalus();
        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
            ->setDaedalus($daedalus)
        ;

        return $player;
    }

    private function createAction(int $actionPointCost, int $movementPointCost, int $moralPointCost, int $successRate = 100): Action
    {
        $action = new Action();

        $action
            ->setActionCost($actionPointCost)
            ->setMoralCost($moralPointCost)
            ->setMovementCost($movementPointCost)
            ->setSuccessRate($successRate)
            ->setActionName(ActionEnum::TAKE)
        ;

        return $action;
    }
}
