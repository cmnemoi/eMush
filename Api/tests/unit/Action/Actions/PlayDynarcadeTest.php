<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\PlayDynarcade;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

/**
 * @internal
 */
final class PlayDynarcadeTest extends AbstractActionTest
{
    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::PLAY_ARCADE);
        $this->actionConfig->setOutputQuantity(2);

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->actionHandler = new PlayDynarcade(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecuteFail()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item)->setName('dynarcarde');

        $item->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();
        $this->actionService->shouldIgnoreMissing();

        $expectedPlayerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            -1,
            $this->actionHandler->getActionConfig()->getActionTags(),
            new \DateTime()
        );
        $expectedPlayerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);

        $this->eventService->shouldReceive('callEvent')
            ->withArgs([\Mockery::on(static function (PlayerVariableEvent $event) use ($expectedPlayerModifierEvent) {
                return $event->getAuthor() === $expectedPlayerModifierEvent->getAuthor()
                     && $event->getVariableName() === $expectedPlayerModifierEvent->getVariableName()
                     && $event->getRoundedQuantity() === $expectedPlayerModifierEvent->getRoundedQuantity()
                     && $event->getTags() === $expectedPlayerModifierEvent->getTags()
                     && $event->getVisibility() === $expectedPlayerModifierEvent->getVisibility();
            }), VariableEventInterface::CHANGE_VARIABLE])
            ->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item)->setName('dynarcarde');

        $item->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::OUTPUT_QUANTITY)
            ->andReturn(2)
            ->once();
        $this->actionService->shouldIgnoreMissing();

        $expectedPlayerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            2,
            $this->actionHandler->getActionConfig()->getActionTags(),
            new \DateTime()
        );
        $expectedPlayerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);

        $this->eventService->shouldReceive('callEvent')
            ->withArgs([\Mockery::on(static function (PlayerVariableEvent $event) use ($expectedPlayerModifierEvent) {
                return $event->getAuthor() === $expectedPlayerModifierEvent->getAuthor()
                     && $event->getVariableName() === $expectedPlayerModifierEvent->getVariableName()
                     && $event->getRoundedQuantity() === $expectedPlayerModifierEvent->getRoundedQuantity()
                     && $event->getTags() === $expectedPlayerModifierEvent->getTags()
                     && $event->getVisibility() === $expectedPlayerModifierEvent->getVisibility();
            }), VariableEventInterface::CHANGE_VARIABLE])
            ->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
