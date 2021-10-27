<?php

namespace Mush\Test\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\FullHealth;
use Mush\Action\Validator\FullHealthValidator;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerVariableServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class FullHealthValidatorTest extends TestCase
{
    private FullHealthValidator $validator;
    private FullHealth $constraint;

    /** @var PlayerVariableServiceInterface|Mockery\Mock */
    private PlayerVariableServiceInterface $gearToolService;

    /**
     * @before
     */
    public function before()
    {
        $this->playerVariableService = Mockery::mock(PlayerVariableServiceInterface::class);

        $this->validator = new FullHealthValidator($this->playerVariableService);
        $this->constraint = new FullHealth();
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
        $gameConfig = new GameConfig();
        $gameConfig->setMaxHealthPoint(16);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $player = new Player();
        $player
            ->setHealthPoint(5)
            ->setDaedalus($daedalus)
        ;

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $player,
            ])
        ;

        $this->playerVariableService->shouldReceive('getMaxPlayerVariable')
            ->with($player, PlayerVariableEnum::HEALTH_POINT)
            ->andReturn(12)
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValid()
    {
        $this->constraint->target = FullHealth::PARAMETER;

        $gameConfig = new GameConfig();
        $gameConfig->setMaxHealthPoint(16);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $player = new Player();
        $player
            ->setHealthPoint(12)
            ->setDaedalus($daedalus)
        ;

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getParameter' => $player,
            ]);

        $this->playerVariableService->shouldReceive('getMaxPlayerVariable')
            ->with($player, PlayerVariableEnum::HEALTH_POINT)
            ->andReturn(12)
        ;

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
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
