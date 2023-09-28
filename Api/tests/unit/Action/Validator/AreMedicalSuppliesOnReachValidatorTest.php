<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\AreMedicalSuppliesOnReach;
use Mush\Action\Validator\AreMedicalSuppliesOnReachValidator;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class AreMedicalSuppliesOnReachValidatorTest extends TestCase
{
    private AreMedicalSuppliesOnReachValidator $validator;
    private AreMedicalSuppliesOnReach $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new AreMedicalSuppliesOnReachValidator();
        $this->constraint = new AreMedicalSuppliesOnReach();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testValidInMedlab()
    {
        $medlab = new Place();
        $medlab->setName(RoomEnum::MEDLAB);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setPlace($medlab);

        $targetPlayerConfig = new CharacterConfig();
        $target = new Player();
        $target->setPlace($medlab);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testValidWithMedikit()
    {
        $room = new Place();
        $room->setName(RoomEnum::BRAVO_BAY);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setPlace($room);

        $equipment = new GameItem($player);
        $equipment->setName(ToolItemEnum::MEDIKIT);
        $player->addEquipment($equipment);

        $targetPlayerConfig = new CharacterConfig();
        $target = new Player();
        $target->setPlace($room);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function notValidWithMedikitInRoom()
    {
        $room = new Place();
        $room->setName(RoomEnum::BRAVO_BAY);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setPlace($room);

        $equipment = new GameItem($room);
        $equipment->setName(ToolItemEnum::MEDIKIT);
        $room->addEquipment($equipment);

        $targetPlayerConfig = new CharacterConfig();
        $target = new Player();
        $target->setPlace($room);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValid()
    {
        $medlab = new Place();
        $medlab->setName(RoomEnum::MEDLAB);

        $laboratory = new Place();
        $laboratory->setName(RoomEnum::LABORATORY);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setPlace($laboratory);

        $targetPlayerConfig = new CharacterConfig();
        $target = new Player();
        $target->setPlace($medlab);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint, 'visibility');
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
