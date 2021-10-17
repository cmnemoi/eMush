<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\MushSpore;
use Mush\Action\Validator\MushSporeValidator;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class MushSporeValidatorTest extends TestCase
{
    private MushSporeValidator $validator;
    private MushSpore $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new MushSporeValidator();
        $this->constraint = new MushSpore();
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

        $player = new Player();

        $chargeStatus = new ChargeStatus($player);
        $chargeStatus
            ->setName(PlayerStatusEnum::SPORES)
            ->setCharge(1)
        ;

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $itemConfig = new ItemConfig();
        $itemConfig->setIsBreakable(true);

        $player = new Player();

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $chargeStatus = new ChargeStatus($player);
        $chargeStatus
            ->setName(PlayerStatusEnum::SPORES)
            ->setCharge(0)
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
