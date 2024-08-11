<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\DailySporesLimit;
use Mush\Action\Validator\DailySporesLimitValidator;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @internal
 */
final class DailySporesLimitValidatorTest extends TestCase
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
        \Mockery::close();
    }

    public function testNotValidForPlayer()
    {
        // given a player
        $player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());

        // given this player has infected once
        $mushStatus = StatusFactory::createChargeStatusFromStatusName(PlayerStatusEnum::MUSH, $player);
        $mushStatus->setCharge(1);

        $this->constraint->target = DailySporesLimit::PLAYER;

        // when I validate player spore limit
        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ]);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        // then I should have a violation : player cannot infect more than once a day
        self::assertTrue(true);
    }

    public function testValidForPlayer()
    {
        $player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());

        // given this player has not infected yet
        $mushStatus = StatusFactory::createChargeStatusFromStatusName(PlayerStatusEnum::MUSH, $player);
        $mushStatus->setCharge(0);

        // when I validate player spore limit
        $this->constraint->target = DailySporesLimit::PLAYER;
        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        // then I should not have a violation : player can still infect today
        self::assertFalse(false);
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
