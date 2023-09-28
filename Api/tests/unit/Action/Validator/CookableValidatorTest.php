<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\Cookable;
use Mush\Action\Validator\CookableValidator;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class CookableValidatorTest extends TestCase
{
    private CookableValidator $validator;
    private Cookable $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new CookableValidator();
        $this->constraint = new Cookable();
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
        $itemConfig = new ItemConfig();
        $itemConfig->setEquipmentName(GameRationEnum::STANDARD_RATION);

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

        $itemConfig->setEquipmentName('toto');

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::FROZEN);
        $chargeStatus = new Status($target, $statusConfig);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $itemConfig = new ItemConfig();
        $itemConfig->setEquipmentName('toto');

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

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::HAZARDOUS);
        $chargeStatus = new Status($target, $statusConfig);

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
