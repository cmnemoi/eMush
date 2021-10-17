<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasEquipmentValidator;
use Mush\Equipment\Entity\Config\GameItem;
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
        Mockery::close();
    }

    public function testValidForEquipment()
    {
        $this->constraint->reach = ReachEnum::ROOM;
        $this->constraint->equipment = EquipmentEnum::CAMERA_EQUIPMENT;
        $this->constraint->contains = true;

        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);
        $target = new GameItem();
        $target->setPlace($room);

        $gameEquipment = new GameItem();
        $gameEquipment->setName(EquipmentEnum::CAMERA_EQUIPMENT)->setPlace($room);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->validator->validate($action, $this->constraint);

        $gameEquipment->setPlace(null);
        $gameEquipment->setPlayer($player);

        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForEquipment()
    {
        $this->constraint->reach = ReachEnum::ROOM;
        $this->constraint->equipment = EquipmentEnum::CAMERA_EQUIPMENT;
        $this->constraint->contains = true;

        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);
        $target = new GameItem();
        $target->setPlace($room);

        $gameEquipment = new GameItem();
        $gameEquipment->setName(ItemEnum::CAMERA_ITEM)->setPlace($room);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    protected function initValidator(?string $expectedMessage = null)
    {
        $builder = Mockery::mock(ConstraintViolationBuilder::class);
        $context = Mockery::mock(ExecutionContext::class);

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
