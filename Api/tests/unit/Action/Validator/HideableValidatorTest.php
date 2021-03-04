<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\Hideable;
use Mush\Action\Validator\HideableValidator;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class HideableValidatorTest extends TestCase
{
    private HideableValidator $validator;
    private Hideable $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new HideableValidator();
        $this->constraint = new Hideable();
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testValid()
    {
        $itemConfig = new ItemConfig();
        $itemConfig->setIsHideable(true);

        $target = new GameItem();
        $target->setEquipment($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $itemConfig = new ItemConfig();
        $itemConfig->setIsHideable(false);

        $target = new GameItem();
        $target->setEquipment($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
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
