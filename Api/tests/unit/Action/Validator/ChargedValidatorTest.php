<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Validator\Charged;
use Mush\Action\Validator\ChargedValidator;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class ChargedValidatorTest extends TestCase
{
    private ChargedValidator $validator;
    private Charged $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new ChargedValidator();
        $this->constraint = new Charged();
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
        $itemConfig->setIsBreakable(true);

        $target = new GameItem();
        $target->setEquipment($itemConfig);

        $chargeStatus = new ChargeStatus($target, PlayerStatusEnum::EUREKA_MOMENT);
        $chargeStatus
            ->setCharge(1)
            ->setDischargeStrategy(ActionEnum::EXPRESS_COOK)
        ;

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
            ])
        ;
        $action
            ->shouldReceive([
                'getActionName' => ActionEnum::EXPRESS_COOK,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $itemConfig = new ItemConfig();
        $itemConfig->setIsBreakable(false);

        $target = new GameItem();
        $target->setEquipment($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $target,
            ])
        ;
        $action
            ->shouldReceive([
                'getActionName' => ActionEnum::EXPRESS_COOK,
            ])
        ;

        $chargeStatus = new ChargeStatus($target, PlayerStatusEnum::EUREKA_MOMENT);
        $chargeStatus
            ->setCharge(0)
            ->setDischargeStrategy(ActionEnum::EXPRESS_COOK)
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
