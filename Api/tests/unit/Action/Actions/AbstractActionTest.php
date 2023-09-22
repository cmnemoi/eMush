<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractActionTest extends TestCase
{
    protected Mockery\Mock|EventServiceInterface $eventService;

    protected ActionServiceInterface|Mockery\Mock $actionService;

    protected ValidatorInterface|Mockery\Mock $validator;

    protected AbstractAction $action;
    protected Action $actionEntity;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof ActionEvent
                && $event->getAction() === $this->actionEntity
            )
            ->times(3)
        ;

        $this->actionService = \Mockery::mock(ActionServiceInterface::class);
        $this->actionService->shouldReceive('canPlayerDoAction')->andReturn(true);

        $this->validator = \Mockery::mock(ValidatorInterface::class);
        $this->validator->shouldReceive('validate')->andReturn(new ConstraintViolationList());
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    protected function createActionEntity(string $name, int $actionPointCost = 0, int $movementPoint = 0): Action
    {
        $action = new Action();
        $action
            ->setActionCost($actionPointCost)
            ->setMovementCost($movementPoint)
            ->setActionName($name)
        ;

        return $action;
    }

    protected function createPlayer(Daedalus $daedalus, Place $room, array $skills = []): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setMaxHealthPoint(16)
            ->setMaxItemInInventory(3)
            ->setInitActionPoint(10)
            ->setInitMovementPoint(10)
            ->setInitMoralPoint(10)
        ;

        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
            ->setDaedalus($daedalus)
            ->setPlace($room)
            ->setSkills($skills)
        ;

        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );

        $player->setPlayerInfo($playerInfo);

        return $player;
    }
}
