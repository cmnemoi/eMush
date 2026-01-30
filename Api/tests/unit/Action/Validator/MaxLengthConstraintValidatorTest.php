<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\MaxLengthConstraint;
use Mush\Action\Validator\MaxLengthConstraintValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @internal
 */
final class MaxLengthConstraintValidatorTest extends TestCase
{
    private MaxLengthConstraintValidator $validator;
    private MaxLengthConstraint $constraint;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->validator = new MaxLengthConstraintValidator();
        $this->constraint = new MaxLengthConstraint();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testValidWhenLessThanLimit()
    {
        $this->constraint->parameterName = 'announcement';
        $this->constraint->maxLength = 4096;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameters' => ['announcement' => 'This is a valid announcement'],
            ]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
    }

    public function testValidWhenEqualLimit()
    {
        $this->constraint->parameterName = 'content';
        $this->constraint->maxLength = 100;

        $action = \Mockery::mock(AbstractAction::class);
        $action->shouldReceive(['getParameters' => ['content' => str_repeat('a', 100)]]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
    }

    public function testValidWhenParameterNameDoesNotExist()
    {
        $this->constraint->parameterName = 'nonexistent';
        $this->constraint->maxLength = 100;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameters' => ['otherParam' => 'some value'],
            ]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
    }

    public function testValidWhenParametersIsNull()
    {
        $this->constraint->parameterName = 'announcement';
        $this->constraint->maxLength = 100;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameters' => null,
            ]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
    }

    public function testNotValidWhenTextExceedsLimit()
    {
        $this->constraint->parameterName = 'announcement';
        $this->constraint->maxLength = 10;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameters' => ['announcement' => 'This text is way too long for the limit'],
            ]);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
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

        // @var ExecutionContext $context
        $this->validator->initialize($context);

        return $this->validator;
    }
}
