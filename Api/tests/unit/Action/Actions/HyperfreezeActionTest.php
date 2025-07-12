<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\Hyperfreeze;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class HyperfreezeActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::HYPERFREEZE, 1);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->actionHandler = new Hyperfreeze(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService,
            $this->statusService
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testExecuteFruit()
    {
        // fruit
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $room->setDaedalus($player->getDaedalus());

        $rationType = new Ration();
        $rationType->setIsPerishable(true);

        $gameRation = new GameItem($room);
        $ration = new ItemConfig();
        $ration
            ->setMechanics(new ArrayCollection([$rationType]))
            ->setEquipmentName('fruit');
        $gameRation
            ->setEquipment($ration)
            ->setName('fruit');

        $gameSuperfreezer = new GameItem($room);
        $superfreezer = new ItemConfig();
        $superfreezer->setEquipmentName(ToolItemEnum::SUPERFREEZER);
        $gameSuperfreezer
            ->setEquipment($superfreezer)
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setHolder($room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameRation);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('createStatusFromName')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(2, $room->getEquipments());
        self::assertCount(0, $player->getEquipments());
        self::assertSame($gameRation->getName(), $room->getEquipments()->first()->getName());
        self::assertCount(0, $player->getStatuses());
    }

    public function testExecuteSteak()
    {
        // Alien Steak
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $rationType = new Ration();
        $rationType->setIsPerishable(true);

        $gameRation = new GameItem($room);
        $ration = new ItemConfig();
        $ration
            ->setMechanics(new ArrayCollection([$rationType]))
            ->setEquipmentName(GameRationEnum::ALIEN_STEAK);
        $gameRation
            ->setEquipment($ration)
            ->setName(GameRationEnum::ALIEN_STEAK);

        $gameSuperfreezer = new GameItem($room);
        $superfreezer = new ItemConfig();
        $superfreezer->setEquipmentName(ToolItemEnum::SUPERFREEZER);
        $gameSuperfreezer
            ->setEquipment($superfreezer)
            ->setName(ToolItemEnum::SUPERFREEZER);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameRation);

        $gameStandardRation = new GameItem(new Place());
        $standardRation = new ItemConfig();
        $standardRation
            ->setEquipmentName(GameRationEnum::STANDARD_RATION);
        $gameStandardRation
            ->setEquipment($standardRation)
            ->setName(GameRationEnum::STANDARD_RATION);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('transformGameEquipmentToEquipmentWithName')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(2, $room->getEquipments());
        self::assertCount(0, $gameSuperfreezer->getStatuses());
        self::assertCount(0, $player->getStatuses());
    }
}
