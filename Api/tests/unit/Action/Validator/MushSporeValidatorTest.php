<?php

namespace Mush\Test\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\GameVariableLevelValidator;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class MushSporeValidatorTest extends TestCase
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
        $this->constraint->target = GameVariableLevel::PLAYER;
        $this->constraint->checkMode = GameVariableLevel::IS_MIN;
        $this->constraint->variableName = DaedalusVariableEnum::SPORE;

        $player = new Player();
        $player->setPlayerVariables(new CharacterConfig());
        $player->setSpores(1);

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

    public function testNotValid()
    {
        $this->constraint->target = GameVariableLevel::PLAYER;
        $this->constraint->checkMode = GameVariableLevel::IS_MIN;
        $this->constraint->variableName = DaedalusVariableEnum::SPORE;

        $player = new Player();
        $player->setPlayerVariables(new CharacterConfig());
        $player->setSpores(0);

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
