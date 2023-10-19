<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\ReachValidator;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class ReachValidatorTest extends TestCase
{
    private ReachValidator $validator;
    private Reach $constraint;

    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->validator = new ReachValidator($this->gameEquipmentService);
        $this->constraint = new Reach();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testValidForPlayer()
    {
        $this->constraint->reach = ReachEnum::ROOM;
        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player
            ->setPlace($room)
        ;

        $target = new Player();
        $playerInfo = new PlayerInfo(
            $target,
            new User(),
            new CharacterConfig()
        );
        $target
            ->setPlace($room)
            ->setPlayerInfo($playerInfo)
        ;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getTarget' => $target,
            ])
        ;

        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidForEquipment()
    {
        $this->constraint->reach = ReachEnum::ROOM;
        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $targetConfig = new ItemConfig();
        $target = new GameItem($room);
        $target->setEquipment($targetConfig);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getTarget' => $target,
            ])
        ;

        $this->validator->validate($action, $this->constraint);

        $target->setHolder($player);

        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForPlayer()
    {
        $this->constraint->reach = ReachEnum::ROOM;
        $this->initValidator($this->constraint->message);

        $player = new Player();
        $player->setPlace(new Place());
        $target = new Player();

        $playerInfo = new PlayerInfo(
            $target,
            new User(),
            new CharacterConfig()
        );
        $target
            ->setPlace(new Place())
            ->setPlayerInfo($playerInfo)
        ;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getTarget' => $target,
            ])
        ;

        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForEquipment()
    {
        $this->constraint->reach = ReachEnum::ROOM;

        $this->initValidator();

        $player = new Player();
        $player->setPlace(new Place());

        $itemConfig = new ItemConfig();
        $target = new GameItem(new Place());
        $target->setEquipment($itemConfig);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getTarget' => $target,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $target->setHolder(new Player());

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidForInventory()
    {
        $this->constraint->reach = ReachEnum::INVENTORY;

        $gameItem = new GameItem(new Place());

        $player = new Player();
        $player->addEquipment($gameItem);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getTarget' => $gameItem,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForInventory()
    {
        $this->constraint->reach = ReachEnum::INVENTORY;

        $gameItem = new GameItem(new Place());

        $player = new Player();

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getTarget' => $gameItem,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidForShelve()
    {
        $this->constraint->reach = ReachEnum::SHELVE;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $gameItem = new GameItem($room);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getTarget' => $gameItem,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForShelve()
    {
        $this->constraint->reach = ReachEnum::SHELVE;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $gameItem = new GameItem(new Place());

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getTarget' => $gameItem,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
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
