<?php

namespace Mush\Test\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\ActionPoint;
use Mush\Action\Validator\ActionPointValidator;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class ActionPointValidatorTest extends TestCase
{
    private ActionPointValidator $validator;
    private ActionPoint $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new ActionPointValidator();
        $this->constraint = new ActionPoint();
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
        $player = new Player();
        $player
            ->setActionPoint(5)
            ->setMovementPoint(5)
            ->setMoralPoint(5)
        ;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ]);

        $action->shouldReceive('getActionPointCost')->andReturn(1);
        $action->shouldReceive('getMoralPointCost')->andReturn(1);
        $action->shouldReceive('getMovementPointCost')->andReturn(1);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValid()
    {
        $player = new Player();
        $player
            ->setActionPoint(5)
            ->setMovementPoint(5)
            ->setMoralPoint(5)
        ;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ]);

        $action->shouldReceive('getActionPointCost')->andReturn(6);
        $action->shouldReceive('getMoralPointCost')->andReturn(1);
        $action->shouldReceive('getMovementPointCost')->andReturn(1);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    public function testWithMovementPointConversion()
    {
        $player = new Player();
        $player
            ->setActionPoint(5)
            ->setMovementPoint(0)
        ;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $action->shouldReceive('getActionPointCost')->andReturn(1);
        $action->shouldReceive('getMoralPointCost')->andReturn(0);
        $action->shouldReceive('getMovementPointCost')->andReturn(1);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $player->setActionPoint(0);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    protected function initValidator(?string $expectedMessage = null)
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
