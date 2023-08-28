<?php

namespace Mush\Test\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\GameVariableLevelValidator;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class OxygenValidatorTest extends TestCase
{
    private GameVariableLevelValidator $validator;
    private GameVariableLevel $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new GameVariableLevelValidator();
        $this->constraint = new GameVariableLevel();
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
        $this->constraint->target = GameVariableLevel::DAEDALUS;
        $this->constraint->checkMode = GameVariableLevel::IS_MIN;
        $this->constraint->variableName = DaedalusVariableEnum::OXYGEN;

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setInitOxygen(10)
            ->setInitShield(1)
            ->setInitHull(1)
            ->setInitFuel(1)
        ;

        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

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
        $this->constraint->target = GameVariableLevel::DAEDALUS;
        $this->constraint->checkMode = GameVariableLevel::IS_MIN;
        $this->constraint->variableName = DaedalusVariableEnum::OXYGEN;

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setInitOxygen(0)
            ->setInitShield(1)
            ->setInitHull(1)
            ->setInitFuel(1)
        ;

        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

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
        $this->constraint->target = GameVariableLevel::DAEDALUS;
        $this->constraint->checkMode = GameVariableLevel::IS_MAX;
        $this->constraint->variableName = DaedalusVariableEnum::OXYGEN;

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setInitOxygen(10)
            ->setInitShield(1)
            ->setInitHull(1)
            ->setInitFuel(1)
            ->setMaxOxygen(12)
        ;

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $daedalus->setDaedalusVariables($daedalusConfig);

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
        $this->constraint->target = GameVariableLevel::DAEDALUS;
        $this->constraint->checkMode = GameVariableLevel::IS_MAX;
        $this->constraint->variableName = DaedalusVariableEnum::OXYGEN;

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setMaxOxygen(12)
            ->setInitOxygen(12)
            ->setInitShield(1)
            ->setInitHull(1)
            ->setInitFuel(1)
        ;

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $daedalus->setDaedalusVariables($daedalusConfig);

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
