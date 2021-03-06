<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\ReachValidator;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class ReachValidatorTest extends TestCase
{
    private ReachValidator $validator;
    private Reach $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new ReachValidator();
        $this->constraint = new Reach();
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testValidForPlayer()
    {
        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);
        $target = new Player();
        $target->setPlace($room);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $target,
            ])
        ;

        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidForEquipment()
    {
        $this->initValidator();

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);
        $target = new GameItem();
        $target->setPlace($room);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $target,
            ])
        ;

        $this->validator->validate($action, $this->constraint);

        $target->setPlace(null);
        $target->setPlayer($player);

        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForPlayer()
    {
        $this->initValidator($this->constraint->message);

        $player = new Player();
        $player->setPlace(new Place());
        $target = new Player();
        $target->setPlace(new Place());

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $target,
            ])
        ;

        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidForEquipment()
    {
        $this->initValidator();

        $player = new Player();
        $player->setPlace(new Place());
        $target = new GameItem();
        $target->setPlace(new Place());

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $target,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $target->setPlace(null);
        $target->setPlayer(new Player());

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
