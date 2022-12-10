<?php

namespace Mush\Test\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\Oxygen;
use Mush\Action\Validator\OxygenValidator;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class OxygenValidatorTest extends TestCase
{
    private OxygenValidator $validator;
    private Oxygen $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new OxygenValidator();
        $this->constraint = new Oxygen();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testValidRetrieve()
    {
        $this->constraint->retrieve = true;

        $daedalus = new Daedalus();
        $daedalus->setOxygen(10);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidRetrieve()
    {
        $this->constraint->retrieve = true;

        $daedalus = new Daedalus();
        $daedalus->setOxygen(0);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidInsert()
    {
        $this->constraint->retrieve = false;

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxOxygen(12);

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $daedalus->setOxygen(10);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidInsert()
    {
        $this->constraint->retrieve = false;

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxOxygen(12);

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $daedalus->setOxygen(12);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
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

        /* @var ExecutionContext $context */
        $this->validator->initialize($context);

        return $this->validator;
    }
}
