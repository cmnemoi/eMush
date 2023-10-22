<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\NumberPlayersAliveInRoom;
use Mush\Action\Validator\NumberPlayersAliveInRoomValidator;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class NumberPlayersInRoomValidatorTest extends TestCase
{
    private NumberPlayersAliveInRoomValidator $validator;
    private NumberPlayersAliveInRoom $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new NumberPlayersAliveInRoomValidator();
        $this->constraint = new NumberPlayersAliveInRoom();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testValid()
    {
        $this->constraint->mode = 'greater_than';
        $this->constraint->number = 3;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $playerInfo = new PlayerInfo($player, \Mockery::mock(User::class), \Mockery::mock(CharacterConfig::class));
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);

        $player2 = new Player();
        $player2->setPlace($room);

        $player2Info = new PlayerInfo($player2, \Mockery::mock(User::class), \Mockery::mock(CharacterConfig::class));
        $player2Info->setGameStatus(GameStatusEnum::CURRENT);

        $player3 = new Player();
        $player3->setPlace($room);

        $player3Info = new PlayerInfo($player3, \Mockery::mock(User::class), \Mockery::mock(CharacterConfig::class));
        $player3Info->setGameStatus(GameStatusEnum::CURRENT);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testTooFewPeople()
    {
        $this->constraint->mode = 'less_than';
        $this->constraint->number = 3;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $playerInfo = new PlayerInfo($player, \Mockery::mock(User::class), \Mockery::mock(CharacterConfig::class));
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint, 'execute');
    }

    public function testTooManyPeople()
    {
        $this->constraint->mode = 'greater_than';
        $this->constraint->number = 1;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $playerInfo = new PlayerInfo($player, \Mockery::mock(User::class), \Mockery::mock(CharacterConfig::class));
        $playerInfo->setGameStatus(GameStatusEnum::CURRENT);

        $player2 = new Player();
        $player2->setPlace($room);

        $player2Info = new PlayerInfo($player2, \Mockery::mock(User::class), \Mockery::mock(CharacterConfig::class));
        $player2Info->setGameStatus(GameStatusEnum::CURRENT);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint, 'execute');
    }

    protected function initValidator(string $expectedMessage = null)
    {
        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $context = \Mockery::mock(ExecutionContext::class);

        if ($expectedMessage) {
            $builder->shouldReceive('addViolation')->andReturn($builder)->once();
            $context->shouldReceive('buildViolation')->with($expectedMessage)->andReturn($builder)->once();
        } else {
            $context->shouldReceive('buildViolation')->never();
        }

        /* @var ExecutionContext $context */
        $this->validator->initialize($context);

        return $this->validator;
    }
}
