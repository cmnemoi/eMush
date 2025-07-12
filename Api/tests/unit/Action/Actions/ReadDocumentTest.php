<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\ReadDocument;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\ContentStatusConfig;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class ReadDocumentTest extends AbstractActionTest
{
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;
    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createActionEntity(ActionEnum::READ_DOCUMENT);

        $this->actionHandler = new ReadDocument(
            $this->eventService,
            $this->actionService,
            $this->validator,
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
        $daedalus = new Daedalus();
        $room = new Place();

        $documentMechanic = new Document();

        $item = new ItemConfig();
        $item->setEquipmentName(ItemEnum::POST_IT);
        $item->setMechanics(new ArrayCollection([$documentMechanic]));

        $gameItem = new GameItem(new Place());
        $gameItem->setEquipment($item);
        $gameItem->setName(ItemEnum::POST_IT);

        $player = $this->createPlayer($daedalus, $room);

        $statusConfig = new ContentStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::DOCUMENT_CONTENT);

        $status = new ContentStatus($gameItem, $statusConfig);
        $status->setContent('test content');

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertSame('test content', $result->getContent());
    }
}
