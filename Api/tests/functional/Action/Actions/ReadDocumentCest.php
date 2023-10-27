<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ReadDocument;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class ReadDocumentCest extends AbstractFunctionalTest
{
    private Action $readDocumentActionConfig;
    private ReadDocument $readDocumentAction;

    private StatusServiceInterface $statusService;

    private Place $room;
    private GameItem $postIt;
    private ContentStatus $contentStatus;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->readDocumentActionConfig = $I->grabEntityFromRepository(Action::class, [
            'name' => ActionEnum::READ_DOCUMENT,
        ]);
        $this->readDocumentAction = $I->grabService(ReadDocument::class);

        $this->room = $this->daedalus->getPlaces()->first();
        $this->player->changePlace($this->room);

        // given a post it in the room
        $postItConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => ItemEnum::POST_IT]);
        $this->postIt = new GameItem($this->room);
        $this->postIt
            ->setName(ItemEnum::POST_IT)
            ->setEquipment($postItConfig)
        ;
        $I->haveInRepository($this->postIt);

        // given something written on the post it
        $this->contentStatus = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::DOCUMENT_CONTENT,
            $this->postIt,
            [],
            new \DateTime(),
        );
        $this->contentStatus->setContent('test content');
    }

    public function testReadDocumentActionSucceedsWithContent(FunctionalTester $I): void
    {
        $result = $this->whenPlayerReadsDocument($this->postIt);

        $I->assertInstanceOf(Success::class, $result);
        $I->assertEquals($result->getContent(), 'test content');
    }

    public function testReadDocumentActionCreatesActionLogAndContentLog(FunctionalTester $I): void
    {
        $this->whenPlayerReadsDocument($this->postIt);

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => ActionLogEnum::READ_DOCUMENT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => ActionLogEnum::READ_CONTENT,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testReadDocumentActionCreatesActionLogOnlyWhenNoContent(FunctionalTester $I): void
    {
        $this->contentStatus->setContent(null);

        $this->whenPlayerReadsDocument($this->postIt);

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => ActionLogEnum::READ_DOCUMENT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $this->room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => ActionLogEnum::READ_CONTENT,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    private function whenPlayerReadsDocument(GameItem $postIt): Success
    {
        $this->readDocumentAction->loadParameters($this->readDocumentActionConfig, $this->player, $postIt);

        return $this->readDocumentAction->execute();
    }
}
