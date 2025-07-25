<?php

namespace Mush\Tests\unit\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlayerCanAffordPoints;
use Mush\Action\Validator\PlayerCanAffordPointsValidator;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @internal
 */
final class ActionPointValidatorTest extends TestCase
{
    private PlayerCanAffordPointsValidator $validator;
    private PlayerCanAffordPoints $constraint;

    /** @var ActionServiceInterface|Mockery\Mock */
    private ActionServiceInterface $actionService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->actionService = \Mockery::mock(ActionServiceInterface::class);

        $this->validator = new PlayerCanAffordPointsValidator($this->actionService);
        $this->constraint = new PlayerCanAffordPoints();
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
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setInitActionPoint(5)
            ->setMaxActionPoint(12)
            ->setInitMoralPoint(5)
            ->setMaxMoralPoint(12)
            ->setMaxMovementPoint(12)
            ->setInitMovementPoint(5);
        $player = PlayerFactory::createPlayer();
        $player
            ->setPlayerVariables($characterConfig);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getActionConfig' => new ActionConfig(),
                'getTarget' => null,
                'getActionProvider' => PlayerFactory::createPlayer(),
                'getTags' => [],
            ]);

        $this->actionService->shouldReceive('playerCanAffordPoints')->andReturn(true);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValid()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setInitActionPoint(5)
            ->setMaxActionPoint(12)
            ->setInitMoralPoint(5)
            ->setMaxMoralPoint(12)
            ->setMaxMovementPoint(12)
            ->setInitMovementPoint(5);
        $player = PlayerFactory::createPlayer();
        $player
            ->setPlayerVariables($characterConfig);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getActionConfig' => new ActionConfig(),
                'getTarget' => null,
                'getActionProvider' => PlayerFactory::createPlayer(),
                'getTags' => [],
            ]);

        $this->actionService->shouldReceive('playerCanAffordPoints')->andReturn(false);

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
