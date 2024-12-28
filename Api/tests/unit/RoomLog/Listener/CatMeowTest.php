<?php

declare(strict_types=1);

namespace Mush\tests\unit\RoomLog\Listener;

use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\FakeD100RollService as FakeD100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Listener\ActionSubscriber;
use Mush\RoomLog\Repository\InMemoryRoomLogRepository;
use Mush\RoomLog\Service\RoomLogService;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CatMeowTest extends TestCase
{
    private ActionSubscriber $actionSubscriber;
    private RoomLogService $roomLogService;
    private FakeD100Roll $catMeowRoll;
    private InMemoryRoomLogRepository $roomLogRepository;
    private Player $player;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->catMeowRoll = new FakeD100Roll(isSuccessful: true);
        $this->roomLogRepository = new InMemoryRoomLogRepository();
        $translationService = $this->createStub(TranslationServiceInterface::class);

        $this->roomLogService = new RoomLogService(
            new FakeD100Roll(),
            new FakeGetRandomIntegerService(result: 0),
            $this->roomLogRepository,
            $translationService,
        );

        $this->actionSubscriber = new ActionSubscriber(
            $this->catMeowRoll,
            $this->roomLogService,
            $translationService,
        );

        $daedalus = DaedalusFactory::createDaedalus();
        $this->player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::RALUCA, $daedalus);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->roomLogRepository->clear();
    }

    /**
     * @dataProvider provideCatShouldMeowBasedOnVisibilityCases
     */
    public function testCatShouldMeowBasedOnVisibility(string $visibility, bool $shouldMeow): void
    {
        $this->givenSchrodingerInPlayerInventory();
        $this->whenActionOccursWithVisibility($visibility);

        if ($shouldMeow) {
            $this->thenCatShouldMeow();
        } else {
            $this->thenCatShouldNotMeow();
        }
    }

    public function testCatShouldNotMeowWhenPlayerEntersDeloggedRoom(): void
    {
        $this->givenSchrodingerInPlayerInventory();
        $this->givenPlayerRoomIsDelogged();

        $this->whenPlayerEntersRoom();

        $this->thenCatLogShouldBeHidden();
    }

    public function testCatShouldNotMeowWhenPlayerExitsDeloggedRoom(): void
    {
        $this->givenSchrodingerInPlayerInventory();
        $this->givenPlayerRoomIsDelogged();

        $this->whenPlayerExitsRoom();

        $this->thenCatLogShouldBeHidden();
    }

    /**
     * Test cases for cat meowing behavior based on action visibility.
     */
    public static function provideCatShouldMeowBasedOnVisibilityCases(): iterable
    {
        return [
            'PUBLIC visibility' => [VisibilityEnum::PUBLIC, true],
            'PRIVATE visibility' => [VisibilityEnum::PRIVATE, false],
            'SECRET visibility' => [VisibilityEnum::SECRET, false],
            'HIDDEN visibility' => [VisibilityEnum::HIDDEN, false],
            'REVEALED visibility' => [VisibilityEnum::REVEALED, true],
            'COVERT visibility' => [VisibilityEnum::COVERT, false],
        ];
    }

    private function givenSchrodingerInPlayerInventory(): void
    {
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::SCHRODINGER, $this->player);
    }

    private function givenPlayerRoomIsDelogged(): void
    {
        StatusFactory::createStatusByNameForHolder(PlaceStatusEnum::DELOGGED->toString(), $this->player->getPlace());
    }

    private function whenActionOccursWithVisibility(string $visibility): void
    {
        $actionResult = new ActionEvent(
            actionConfig: $this->searchActionConfig(),
            actionProvider: $this->player,
            player: $this->player,
            tags: $this->searchActionConfig()->getActionTags(),
        );
        $result = new Success();
        $result->setVisibility($visibility);
        $actionResult->setActionResult($result);

        $this->actionSubscriber->onResultAction($actionResult);
    }

    private function whenPlayerEntersRoom(): void
    {
        $door = Door::createFromRooms(
            $this->player->getPlace(),
            Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $this->player->getDaedalus()),
        );

        $actionResult = new ActionEvent(
            actionConfig: $this->moveActionConfig(),
            actionProvider: $door,
            player: $this->player,
            tags: $this->moveActionConfig()->getActionTags(),
            actionTarget: $door,
        );
        $result = new Success();
        $result->setVisibility(VisibilityEnum::PUBLIC);
        $actionResult->setActionResult($result);

        $this->actionSubscriber->onPostAction($actionResult);
    }

    private function whenPlayerExitsRoom(): void
    {
        $door = Door::createFromRooms(
            $this->player->getPlace(),
            Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $this->player->getDaedalus()),
        );

        $actionResult = new ActionEvent(
            actionConfig: $this->moveActionConfig(),
            actionProvider: $door,
            player: $this->player,
            tags: $this->moveActionConfig()->getActionTags(),
            actionTarget: $door,
        );
        $result = new Success();
        $result->setVisibility(VisibilityEnum::PUBLIC);
        $actionResult->setActionResult($result);

        $this->actionSubscriber->onPreAction($actionResult);
    }

    private function thenCatShouldMeow(): void
    {
        $catMeowLog = $this->roomLogRepository->findOneByLogKey(LogEnum::CAT_MEOW);
        self::assertNotNull($catMeowLog);
    }

    private function thenCatShouldNotMeow(): void
    {
        $logId = $this->roomLogRepository->findOneByLogKey(LogEnum::CAT_MEOW)?->getId();
        self::assertNull($logId);
    }

    private function thenCatLogShouldBeHidden(): void
    {
        $catMeowLog = $this->roomLogRepository->findOneByLogKey(LogEnum::CAT_MEOW);
        self::assertEquals(VisibilityEnum::HIDDEN, $catMeowLog->getVisibility(), 'Cat log should be hidden');
    }

    private function moveActionConfig(): ActionConfig
    {
        return ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::MOVE));
    }

    private function searchActionConfig(): ActionConfig
    {
        return ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SEARCH));
    }
}
