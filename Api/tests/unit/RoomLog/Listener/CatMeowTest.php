<?php

declare(strict_types=1);

namespace Mush\tests\unit\RoomLog\Listener;

use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\FakeD100RollService as FakeD100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Listener\ActionSubscriber;
use Mush\RoomLog\Repository\InMemoryRoomLogRepository;
use Mush\RoomLog\Service\RoomLogService;
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

    private function whenActionOccursWithVisibility(string $visibility): void
    {
        $actionResult = new ActionEvent(
            actionConfig: $this->actionConfig(),
            actionProvider: $this->player,
            player: $this->player,
            tags: $this->actionConfig()->getActionTags(),
        );
        $result = new Success();
        $result->setVisibility($visibility);
        $actionResult->setActionResult($result);

        $this->actionSubscriber->onResultAction($actionResult);
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

    private function actionConfig(): ActionConfig
    {
        return ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SEARCH));
    }
}