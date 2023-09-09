<?php

namespace Mush\Test\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasEquipmentValidator;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class HasEquipmentValidatorTest extends TestCase
{
    private HasEquipmentValidator $validator;
    private HasEquipment $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new HasEquipmentValidator();
        $this->constraint = new HasEquipment();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testValidForEquipment()
    {
        $this->constraint->reach = ReachEnum::ROOM;
        $this->constraint->equipments = [EquipmentEnum::CAMERA_EQUIPMENT];
        $this->constraint->contains = true;

        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);
        $target = new GameItem($room);
        $target->setName('target');

        $gameEquipment = new GameItem($room);
        $gameEquipment->setName(EquipmentEnum::CAMERA_EQUIPMENT);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForEquipment()
    {
        $this->constraint->reach = ReachEnum::ROOM;
        $this->constraint->equipments = [EquipmentEnum::CAMERA_EQUIPMENT];
        $this->constraint->contains = true;

        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);
        $target = new GameItem($room);
        $target->setName('target');

        $gameEquipment = new GameItem($room);
        $gameEquipment->setName(ItemEnum::CAMERA_ITEM);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    public function testValidForAnyEquipment()
    {
        $this->constraint->reach = ReachEnum::ROOM;
        $this->constraint->equipments = [EquipmentEnum::CAMERA_EQUIPMENT, EquipmentEnum::ANTENNA];
        $this->constraint->contains = true;
        $this->constraint->all = false;

        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);
        $target = new GameItem($room);
        $target->setName('target');

        $gameEquipment = new GameItem($room);
        $gameEquipment->setName(EquipmentEnum::CAMERA_EQUIPMENT);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidForAllEquipment()
    {
        $this->constraint->reach = ReachEnum::ROOM;
        $this->constraint->equipments = [EquipmentEnum::CAMERA_EQUIPMENT, EquipmentEnum::ANTENNA];
        $this->constraint->contains = true;

        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);
        $target = new GameItem($room);
        $target->setName('target');

        $gameEquipment = new GameItem($room);
        $gameEquipment->setName(EquipmentEnum::CAMERA_EQUIPMENT);
        $gameEquipment2 = new GameItem($room);
        $gameEquipment2->setName(EquipmentEnum::ANTENNA);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForAllEquipment()
    {
        $this->constraint->reach = ReachEnum::ROOM;
        $this->constraint->equipments = [EquipmentEnum::CAMERA_EQUIPMENT, EquipmentEnum::ANTENNA];
        $this->constraint->contains = true;

        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);
        $target = new GameItem($room);
        $target->setName('target');

        $gameEquipment = new GameItem($room);
        $gameEquipment->setName(EquipmentEnum::CAMERA_EQUIPMENT);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValidForTargetParameter()
    {
        $this->constraint->reach = ReachEnum::INVENTORY;
        $this->constraint->equipments = [ItemEnum::ITRACKIE];
        $this->constraint->contains = false;
        $this->constraint->target = HasEquipment::PARAMETER;

        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);
        $player2 = new Player();
        $player2->setPlace($room);

        $gameEquipment = new GameItem($player2);
        $gameEquipment->setName(ItemEnum::ITRACKIE);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $player2,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
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
