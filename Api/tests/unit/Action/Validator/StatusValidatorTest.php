<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\HasStatusValidator;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class StatusValidatorTest extends TestCase
{
    private HasStatusValidator $validator;
    private HasStatus $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new HasStatusValidator();
        $this->constraint = new HasStatus();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testValidParameter()
    {
        $this->constraint->target = HasStatus::PARAMETER;

        $target = new GameItem(new Place());

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
            ])
        ;

        $this->constraint->status = EquipmentStatusEnum::BROKEN;
        $this->constraint->contain = false;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($target, $brokenConfig);
        $this->constraint->contain = true;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidParameter()
    {
        $this->constraint->target = HasStatus::PARAMETER;

        $target = new GameItem(new Place());

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
            ])
        ;

        $this->constraint->status = EquipmentStatusEnum::BROKEN;
        $this->constraint->contain = true;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($target, $brokenConfig);
        $this->constraint->contain = true;
        $this->constraint->status = EquipmentStatusEnum::FROZEN;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidForPlayer()
    {
        $this->constraint->target = HasStatus::PLAYER;

        $player = new Player();

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->constraint->status = EquipmentStatusEnum::BROKEN;
        $this->constraint->contain = false;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($player, $brokenConfig);
        $this->constraint->contain = true;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForPlayer()
    {
        $this->constraint->target = HasStatus::PLAYER;

        $player = new Player();

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->constraint->status = EquipmentStatusEnum::BROKEN;
        $this->constraint->contain = true;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($player, $brokenConfig);
        $this->constraint->contain = true;
        $this->constraint->status = EquipmentStatusEnum::FROZEN;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidForPlayerRoom()
    {
        $this->constraint->target = HasStatus::PLAYER_ROOM;

        $player = new Player();

        $room = new Place();
        $player->setPlace($room);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->constraint->status = EquipmentStatusEnum::BROKEN;
        $this->constraint->contain = false;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($room, $brokenConfig);
        $this->constraint->contain = true;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForPlayerRoom()
    {
        $this->constraint->target = HasStatus::PLAYER_ROOM;

        $player = new Player();

        $room = new Place();
        $player->setPlace($room);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->constraint->status = EquipmentStatusEnum::BROKEN;
        $this->constraint->contain = true;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($room, $brokenConfig);

        $this->constraint->contain = true;
        $this->constraint->status = EquipmentStatusEnum::FROZEN;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testInverseSide()
    {
        $this->constraint->target = HasStatus::PLAYER;

        $player = new Player();

        $target = new GameEquipment(new Place());

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $target,
            ])
        ;

        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($target, $brokenConfig);
        $status->setTarget($player);
        $this->constraint->contain = true;
        $this->constraint->status = EquipmentStatusEnum::BROKEN;
        $this->constraint->ownerSide = false;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->constraint->ownerSide = true;

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
