<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\ReadBook;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Game\Enum\SkillEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;

class ReadBookActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::READ_BOOK, 2);

        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->action = new ReadBook(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $book = new Book();
        $book->setSkill(SkillEnum::PILOT);
        $item->setMechanics(new ArrayCollection([$book]));
        $gameItem
            ->setEquipment($item)
            ->setHolder($room)
            ->setName('name')
        ;

        $this->eventService->shouldReceive('callEvent');

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getEquipments());
        $this->assertContains(SkillEnum::PILOT, $player->getSkills());
    }
}
