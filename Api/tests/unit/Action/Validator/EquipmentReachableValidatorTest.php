<?php

namespace Mush\Test\Action\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\EquipmentReachable;
use Mush\Action\Validator\EquipmentReachableValidator;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class EquipmentReachableValidatorTest extends TestCase
{
    private EquipmentReachableValidator $validator;
    private EquipmentReachable $constraint;

    /** @var GearToolServiceInterface|Mockery\Mock */
    private GearToolServiceInterface $gearToolService;

    /**
     * @before
     */
    public function before()
    {
        $this->gearToolService = \Mockery::mock(GearToolServiceInterface::class);

        $this->validator = new EquipmentReachableValidator($this->gearToolService);
        $this->constraint = new EquipmentReachable();
        $this->constraint->name = 'some_name';
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
        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => new Player(),
            ])
        ;

        $collection = new ArrayCollection([new GameEquipment(new Place())]);

        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn($collection);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => new Player(),
            ])
        ;

        $collection = new ArrayCollection();

        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn($collection);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
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
