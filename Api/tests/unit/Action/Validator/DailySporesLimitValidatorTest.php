<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\DailySporesLimit;
use Mush\Action\Validator\DailySporesLimitValidator;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class DailySporesLimitValidatorTest extends TestCase
{
    private DailySporesLimitValidator $validator;
    private DailySporesLimit $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new DailySporesLimitValidator();
        $this->constraint = new DailySporesLimit();
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testValidForDaedalus()
    {
        $daedalus = new Daedalus();
        $daedalus->setSpores(1);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $this->constraint->target = DailySporesLimit::DAEDALUS;

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

    public function testNotValidForDaedalus()
    {
        $daedalus = new Daedalus();
        $daedalus->setSpores(0);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $this->constraint->target = DailySporesLimit::DAEDALUS;

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidForPlayer()
    {
        $daedalus = new Daedalus();
        $daedalus->setSpores(1);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setName(PlayerStatusEnum::MUSH);
        $mushStatus = new ChargeStatus($player, $mushConfig);
        $mushStatus->setCharge(1);

        $this->constraint->target = DailySporesLimit::PLAYER;

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

    public function testNotValidForPlayer()
    {
        $daedalus = new Daedalus();
        $daedalus->setSpores(0);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $this->constraint->target = DailySporesLimit::PLAYER;

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setName(PlayerStatusEnum::MUSH);
        $mushStatus = new ChargeStatus($player, $mushConfig);
        $mushStatus->setCharge(0);

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
