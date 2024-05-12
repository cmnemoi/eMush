<?php

namespace Mush\Tests\unit\Action\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Validator\HasAction;
use Mush\Action\Validator\HasActionValidator;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @internal
 */
final class ParameterHasActionValidatorTest extends TestCase
{
    private HasActionValidator $validator;
    private HasAction $constraint;

    /** @var GearToolServiceInterface|Mockery\Mock */
    private GearToolServiceInterface $gearToolService;

    /**
     * @before
     */
    public function before()
    {
        $this->gearToolService = \Mockery::mock(GearToolServiceInterface::class);

        $this->validator = new HasActionValidator($this->gearToolService);
        $this->constraint = new HasAction();
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
        $actionConfig = new ActionConfig();
        $actionConfig
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setActionName(ActionEnum::ANATHEMA);

        $player = new Player();
        $characterConfig = new CharacterConfig();
        $characterConfig->setActionConfigs([$actionConfig]);
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);

        $place = new Place();

        $player->setPlace($place);

        $itemConfig = new ItemConfig();
        $itemConfig->setActionConfigs(new ArrayCollection([$actionConfig]));

        $gameItem = new GameItem($place);
        $gameItem->setEquipment($itemConfig);
        $gameItem->setName('gameItem');

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getActionConfig' => $actionConfig,
                'getActionProvider' => $player,
                'getTarget' => $gameItem,
                'getPlayer' => $player,
            ]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
    }

    public function testNotValid()
    {
        $actionConfig = new ActionConfig();
        $actionConfig
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setActionName(ActionEnum::ANATHEMA);

        $player = new Player();
        $characterConfig = new CharacterConfig();
        $characterConfig->setActionConfigs([$actionConfig]);
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);

        $place = new Place();

        $player->setPlace($place);

        $itemConfig = new ItemConfig();
        $itemConfig->setActionConfigs(new ArrayCollection([$actionConfig]));
        $gameItem = new GameItem($place);
        $gameItem->setEquipment($itemConfig);
        $gameItem->setName('gameItem');

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getActionConfig' => $actionConfig,
                'getActionProvider' => $player,
                'getTarget' => $gameItem,
                'getPlayer' => $player,
            ]);

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
    }

    public function testValidTool()
    {
        $place = new Place();

        $actionConfig = new ActionConfig();
        $actionConfig
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setRange(ActionRangeEnum::ROOM)
            ->setActionName(ActionEnum::ANATHEMA);

        $player = new Player();
        $player->setPlace($place);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $toolConfig = new ItemConfig();
        $toolConfig->setActionConfigs([$actionConfig]);
        $gameTool = new GameItem($place);
        $gameTool->setEquipment($toolConfig);

        $itemConfig = new ItemConfig();
        $itemConfig->setActionConfigs(new ArrayCollection([$actionConfig]));
        $gameItem = new GameItem($place);
        $gameItem->setEquipment($itemConfig);
        $gameItem->setName('gameItem');

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getActionConfig' => $actionConfig,
                'getActionProvider' => $gameTool,
                'getTarget' => $gameItem,
                'getActionName' => 'some_name',
                'getPlayer' => $player,
            ]);

        $this->gearToolService->shouldReceive('getUsedTool')->andReturn(new GameEquipment(new Place()));

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        self::assertTrue(true);
    }

    public function testNotValidTool()
    {
        $place = new Place();

        $actionConfig = new ActionConfig();
        $actionConfig
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setRange(ActionRangeEnum::SHELF)
            ->setActionName(ActionEnum::ANATHEMA);

        $player = new Player();
        $player->setPlace($place);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $player2 = new Player();
        $player2->setPlace($place);
        $playerInfo2 = new PlayerInfo($player2, new User(), new CharacterConfig());

        $toolConfig = new ItemConfig();
        $toolConfig->setActionConfigs([$actionConfig]);
        $gameTool = new GameItem($player2);
        $gameTool->setEquipment($toolConfig);

        $itemConfig = new ItemConfig();
        $itemConfig->setActionConfigs(new ArrayCollection([$actionConfig]));
        $gameItem = new GameItem($place);
        $gameItem->setEquipment($itemConfig);
        $gameItem->setName('gameItem');

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getActionConfig' => $actionConfig,
                'getActionProvider' => $gameTool,
                'getTarget' => $gameItem,
                'getActionName' => 'some_name',
                'getPlayer' => $player,
            ]);

        $this->gearToolService->shouldReceive('getUsedTool')->andReturn(null);

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
