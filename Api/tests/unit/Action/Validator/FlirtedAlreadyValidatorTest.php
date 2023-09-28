<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\FlirtedAlready;
use Mush\Action\Validator\FlirtedAlreadyValidator;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class FlirtedAlreadyValidatorTest extends TestCase
{
    private FlirtedAlreadyValidator $validator;
    private FlirtedAlready $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new FlirtedAlreadyValidator();
        $this->constraint = new FlirtedAlready();
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

        $target = new Player();

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getSupport' => $target,
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testTargetInitiatorValid()
    {
        // Target player is expected to have flirted with player
        // This case is needed to be able to do the thing with target
        $this->constraint->initiator = false;
        $this->constraint->expectedValue = true;

        $player = new Player();

        $target = new Player();

        $target->addFlirt($player);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getSupport' => $target,
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValid()
    {
        $player = new Player();

        $target = new Player();

        $player->addFlirt($target);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getSupport' => $target,
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
