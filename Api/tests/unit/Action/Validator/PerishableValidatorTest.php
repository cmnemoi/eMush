<?php

namespace Mush\Tests\unit\Action\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\Perishable;
use Mush\Action\Validator\PerishableValidator;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Place\Entity\Place;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @internal
 */
final class PerishableValidatorTest extends TestCase
{
    private PerishableValidator $validator;
    private Perishable $constraint;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->validator = new PerishableValidator();
        $this->constraint = new Perishable();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testValid()
    {
        $rationMechanic = new Fruit();
        $rationMechanic->setIsPerishable(true);

        $itemConfig = new ItemConfig();
        $itemConfig->setMechanics(new ArrayCollection([$rationMechanic]));

        $target = new GameItem(new Place());
        $target->setEquipment($itemConfig);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
            ]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
    }

    public function testNotValid()
    {
        $itemConfig = new ItemConfig();

        $target = new GameItem(new Place());
        $target->setEquipment($itemConfig);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
            ]);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $rationMechanic = new Fruit();
        $rationMechanic->setIsPerishable(false);
        $itemConfig->setMechanics(new ArrayCollection([$rationMechanic]));

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
