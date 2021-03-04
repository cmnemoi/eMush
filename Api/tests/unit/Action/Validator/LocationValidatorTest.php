<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\Location;
use Mush\Action\Validator\LocationValidator;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class LocationValidatorTest extends TestCase
{
    private LocationValidator $validator;
    private Location $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new LocationValidator();
        $this->constraint = new Location();
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testValidForInventory()
    {
        $this->constraint->location = ReachEnum::INVENTORY;

        $gameItem = new GameItem();

        $player = new Player();
        $player->addItem($gameItem);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $gameItem,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForInventory()
    {
        $this->constraint->location = ReachEnum::INVENTORY;

        $gameItem = new GameItem();

        $player = new Player();

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $gameItem,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidForShelve()
    {
        $this->constraint->location = ReachEnum::SHELVE;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $gameItem = new GameItem();
        $gameItem->setPlace($room);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $gameItem,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForShelve()
    {
        $this->constraint->location = ReachEnum::SHELVE;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $gameItem = new GameItem();
        $gameItem->setPlace(new Place());

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $gameItem,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
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
