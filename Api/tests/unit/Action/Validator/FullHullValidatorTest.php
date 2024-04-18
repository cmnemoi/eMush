<?php

namespace Mush\Tests\unit\Action\Validator;

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

/**
 * @internal
 */
final class FullHullValidatorTest extends TestCase
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

    public function testValid()
    {
        $this->constraint->target = GameVariableLevel::DAEDALUS;
        $this->constraint->checkMode = GameVariableLevel::IS_MAX;
        $this->constraint->variableName = DaedalusVariableEnum::HULL;

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setMaxHull(100)
            ->setInitOxygen(1)
            ->setInitShield(1)
            ->setInitHull(99)
            ->setInitFuel(1);

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = new Player();
        $player
            ->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValid()
    {
        $this->constraint->target = GameVariableLevel::DAEDALUS;
        $this->constraint->checkMode = GameVariableLevel::IS_MAX;
        $this->constraint->variableName = DaedalusVariableEnum::HULL;

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setMaxHull(100)
            ->setInitOxygen(1)
            ->setInitShield(1)
            ->setInitHull(100)
            ->setInitFuel(1);

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = new Player();
        $player
            ->setDaedalus($daedalus);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ]);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
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
