<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractActionTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    protected EventDispatcherInterface $eventDispatcher;

    protected AbstractAction $action;
    protected Action $actionEntity;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->eventDispatcher->shouldReceive('dispatch');
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
        $characterConfig = new CharacterConfig();
        $characterConfig->setName('character name');

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
