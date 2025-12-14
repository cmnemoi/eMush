<?php

namespace Mush\Tests\unit\Daedalus\Validator;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Validator\FullDaedalus;
use Mush\Daedalus\Validator\FullDaedalusValidator;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @internal
 */
final class FullDaedalusTest extends TestCase
{
    private FullDaedalusValidator $validator;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->validator = new FullDaedalusValidator();
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
        $constraint = new FullDaedalus();
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->getDaedalusConfig()->setPlayerCount(1);
        $this->initValidator();

        $this->validator->validate($daedalus, $constraint);

        self::assertTrue(true);
    }

    public function testNotValid()
    {
        $constraint = new FullDaedalus();
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->getDaedalusConfig()->setPlayerCount(1);
        $this->initValidator('This daedalus is full');

        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        $this->validator->validate($daedalus, $constraint);

        self::assertTrue(true);
    }

    protected function initValidator(?string $expectedMessage = null)
    {
        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $context = \Mockery::mock(ExecutionContext::class);

        if ($expectedMessage) {
            $builder->shouldReceive('addViolation')->andReturn($builder)->once();
            $builder->shouldReceive('setCode')->andReturn($builder)->once();
            $context->shouldReceive('buildViolation')->with($expectedMessage)->andReturn($builder)->once();
        } else {
            $context->shouldReceive('buildViolation')->never();
        }

        // @var ExecutionContext $context
        $this->validator->initialize($context);

        return $this->validator;
    }
}
