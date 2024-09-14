<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\ReadBook;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class ReadBookActionTest extends AbstractActionTest
{
    private AddSkillToPlayerService $addSkillToPlayerService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::READ_BOOK, 2);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);

        $this->actionHandler = new ReadBook(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->createStub(AddSkillToPlayerService::class),
            $this->createStub(RoomLogServiceInterface::class),
            $this->createStub(StatusServiceInterface::class),
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
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $book = new Book();
        $book->setSkill(SkillEnum::PILOT);
        $item->setMechanics(new ArrayCollection([$book]));
        $gameItem
            ->setEquipment($item)
            ->setName('name');

        $this->eventService->shouldReceive('callEvent');

        $player = $this->createPlayer($daedalus, $room);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertEmpty($player->getEquipments());
    }
}
