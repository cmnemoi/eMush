<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\EmptyOperationalBedInRoom;
use Mush\Action\Validator\EmptyOperationalBedInRoomValidator;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class EmptyOperationalBedInRoomValidatorTest extends TestCase
{
    private EmptyOperationalBedInRoomValidator $validator;
    private EmptyOperationalBedInRoom $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new EmptyOperationalBedInRoomValidator();
        $this->constraint = new EmptyOperationalBedInRoom();
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
        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $gameEquipment = new GameItem($room);
        $gameEquipment->setName(EquipmentEnum::BED);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValidBedNotEmpty()
    {
        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $gameEquipment = new GameItem($room);
        $gameEquipment->setName(EquipmentEnum::BED);

        $statusConfig = new StatusConfig();
        $statusConfig->setVisibility(VisibilityEnum::PUBLIC)->setStatusName(PlayerStatusEnum::LYING_DOWN);
        $lyingDownStatus = new Status($player, $statusConfig);
        $lyingDownStatus
            ->setTarget($gameEquipment)
        ;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint, 'execute');
    }

    public function testNotValidNoBed()
    {
        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

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
