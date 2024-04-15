<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\Write;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\ContentStatusConfig;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class WriteTest extends AbstractActionTest
{
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;
    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);
        $this->actionEntity = $this->createActionEntity(ActionEnum::WRITE);

        $this->action = new Write(
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
    public function after()
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $item = new ItemConfig();

        $gameItem = new GameItem(new Place());
        $gameItem->setEquipment($item);
        $gameItem->setName(ItemEnum::POST_IT);

        $item->setEquipmentName(ItemEnum::POST_IT);

        $player = $this->createPlayer($daedalus, $room);

        $blockOfPostIt = new ItemConfig();
        $blockOfPostIt->setActions(new ArrayCollection([$this->actionEntity]));

        $gameBlockOfPostIt = new GameItem($room);
        $gameBlockOfPostIt->setEquipment($blockOfPostIt)->setName(ToolItemEnum::BLOCK_OF_POST_IT)->setHolder($room);

        $statusConfig = new ContentStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::DOCUMENT_CONTENT);

        $status = new ContentStatus($gameItem, $statusConfig);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once()->andReturn($status);

        $this->action->loadParameters($this->actionEntity, $player, $gameBlockOfPostIt, ['content' => 'test content']);

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertSame($status->getContent(), 'test content');
    }
}
