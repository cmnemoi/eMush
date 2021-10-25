<?php

namespace Mush\Test\Action\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\MechanicValidator;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class MechanicValidatorTest extends TestCase
{
    private MechanicValidator $validator;
    private Mechanic $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new MechanicValidator();
        $this->constraint = new Mechanic();
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
        $documentMechanic = new Document();

        $itemConfig = new ItemConfig();
        $itemConfig->setMechanics(new ArrayCollection([$documentMechanic]));

        $target = new GameItem();
        $target->setEquipment($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
            ])
        ;

        $this->constraint->mechanic = EquipmentMechanicEnum::DOCUMENT;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $itemConfig = new ItemConfig();

        $target = new GameItem();
        $target->setEquipment($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
            ])
        ;

        $this->constraint->mechanic = EquipmentMechanicEnum::FRUIT;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $documentMechanic = new Document();
        $itemConfig->setMechanics(new ArrayCollection([$documentMechanic]));

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
