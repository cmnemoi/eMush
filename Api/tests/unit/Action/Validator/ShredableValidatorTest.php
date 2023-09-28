<?php

namespace Mush\Tests\unit\Action\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\Shredable;
use Mush\Action\Validator\ShredableValidator;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Place\Entity\Place;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class ShredableValidatorTest extends TestCase
{
    private ShredableValidator $validator;
    private Shredable $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new ShredableValidator();
        $this->constraint = new Shredable();
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
        $documentMechanic = new Document();
        $documentMechanic->setCanShred(true);

        $itemConfig = new ItemConfig();
        $itemConfig->setMechanics(new ArrayCollection([$documentMechanic]));

        $target = new GameItem(new Place());
        $target->setEquipment($itemConfig);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getSupport' => $target,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $itemConfig = new ItemConfig();

        $target = new GameItem(new Place());
        $target->setEquipment($itemConfig);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getSupport' => $target,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $documentMechanic = new Document();
        $documentMechanic->setCanShred(false);
        $itemConfig->setMechanics(new ArrayCollection([$documentMechanic]));

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
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
