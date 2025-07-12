<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\Coffee;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class CoffeeActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->statusService = \Mockery::mock(StatusServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->createActionEntity(ActionEnum::BUILD);

        $this->actionHandler = new Coffee(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();

        $gameCoffeeMachine = new GameEquipment($room);
        $coffeeMachine = new EquipmentConfig();
        $coffeeMachine->setEquipmentName(EquipmentEnum::COFFEE_MACHINE);
        $gameCoffeeMachine
            ->setEquipment($coffeeMachine)
            ->setName(EquipmentEnum::COFFEE_MACHINE);

        $coffeeMachine->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameCoffeeMachine);

        $gameCoffee = new GameItem(new Place());
        $coffee = new ItemConfig();
        $coffee
            ->setEquipmentName(GameRationEnum::COFFEE);
        $gameCoffee
            ->setEquipment($coffee)
            ->setName(GameRationEnum::COFFEE);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertCount(0, $player->getStatuses());
        self::assertSame(10, $player->getActionPoint());
    }
}
