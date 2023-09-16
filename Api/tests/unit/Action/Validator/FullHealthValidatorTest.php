<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\GameVariableLevelValidator;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerVariableServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class FullHealthValidatorTest extends TestCase
{
    private GameVariableLevelValidator $validator;
    private GameVariableLevel $constraint;

    /** @var PlayerVariableServiceInterface|Mockery\Mock */
    private PlayerVariableServiceInterface $gearToolService;

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
        $this->constraint->target = GameVariableLevel::TARGET_PLAYER;
        $this->constraint->checkMode = GameVariableLevel::IS_MAX;
        $this->constraint->variableName = PlayerVariableEnum::HEALTH_POINT;

        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setMaxHealthPoint(12)
            ->setInitHealthPoint(5)
        ;

        $daedalus = new Daedalus();
        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
            ->setDaedalus($daedalus)
        ;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValid()
    {
        $this->constraint->target = GameVariableLevel::TARGET_PLAYER;
        $this->constraint->checkMode = GameVariableLevel::IS_MAX;
        $this->constraint->variableName = PlayerVariableEnum::HEALTH_POINT;

        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setMaxHealthPoint(12)
            ->setInitHealthPoint(12)
        ;

        $daedalus = new Daedalus();
        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
            ->setDaedalus($daedalus)
        ;

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $player,
            ]);

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
