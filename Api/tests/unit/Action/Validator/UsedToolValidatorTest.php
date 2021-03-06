<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\UsedTool;
use Mush\Action\Validator\UsedToolValidator;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class UsedToolValidatorTest extends TestCase
{
    private UsedToolValidator $validator;
    private UsedTool $constraint;

    /** @var GearToolServiceInterface | Mockery\Mock */
    private GearToolServiceInterface $gearToolService;

    /**
     * @before
     */
    public function before()
    {
        $this->gearToolService = Mockery::mock(GearToolServiceInterface::class);

        $this->validator = new UsedToolValidator($this->gearToolService);
        $this->constraint = new UsedTool();
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
        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getActionName' => 'some_name',
                'getPlayer' => new Player(),
            ])
        ;

        $this->gearToolService->shouldReceive('getUsedTool')->andReturn(new GameEquipment());

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getActionName' => 'some_name',
                'getPlayer' => new Player(),
            ])
        ;

        $this->gearToolService->shouldReceive('getUsedTool')->andReturn(null);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
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
