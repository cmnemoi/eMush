<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractActionTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    protected EventDispatcherInterface $eventDispatcher;

    /** @var ActionServiceInterface|Mockery\Mock */
    protected ActionServiceInterface $actionService;

    /** @var ValidatorInterface|Mockery\Mock */
    protected ValidatorInterface $validator;

    protected AbstractAction $action;
    protected Action $actionEntity;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof ActionEvent &&
                $event->getAction() === $this->actionEntity
            )
            ->times(3)
        ;

        $this->actionService = Mockery::mock(ActionServiceInterface::class);
        $this->actionService->shouldReceive('canPlayerDoAction')->andReturn(true);

        $this->validator = Mockery::mock(ValidatorInterface::class);
        $this->validator->shouldReceive('validate')->andReturn(new ConstraintViolationList());
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    protected function createActionEntity(string $name, int $actionPointCost = 0, int $movementPoint = 0): Action
    {
        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost($actionPointCost)
            ->setMovementPointCost($movementPoint)
        ;
        $action = new Action();
        $action
            ->setName($name)
            ->setActionCost($actionCost);

        return $action;
    }

    protected function createPlayer(Daedalus $daedalus, Place $room, array $skills = []): Player
    {
        $gameConfig = new GameConfig();
        $gameConfig->setMaxHealthPoint(16);

        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setGameConfig($gameConfig)
        ;

        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setDaedalus($daedalus)
            ->setPlace($room)
            ->setSkills($skills)
            ->setGameStatus(GameStatusEnum::CURRENT)
            ->setCharacterConfig($characterConfig)
        ;

        return $player;
    }
}
