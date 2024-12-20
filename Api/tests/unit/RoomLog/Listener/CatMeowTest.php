<?php

declare(strict_types=1);

namespace Mush\tests\unit\RoomLog\Listener;

use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\FakeD100RollService as FakeD100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\RoomLog\Enum\ActionLogEnum;
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

    public function testCatShouldMeowOnPublicActionLog(): void
    {   
        // given schrodinger is in player inventory
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::SCHRODINGER, $this->player);

        // given an action event for public action
        $actionResult = new ActionEvent(
            actionConfig: $this->actionConfig(),
            actionProvider: $this->player,
            player: $this->player,
            tags: $this->actionConfig()->getActionTags(),
        );
        $result = new Success();
        $result->setVisibility(VisibilityEnum::PUBLIC);
        $actionResult->setActionResult($result);

        // when a listen for result action event
        $this->actionSubscriber->onResultAction($actionResult);

        // then cat should meow
        $catMeowLog = $this->roomLogRepository->findOneByLogKey(LogEnum::CAT_MEOW);
        self::assertNotNull($catMeowLog);
    }

    public function testCatShouldNotMeowOnPrivateActionLog(): void
    {
        // given schrodinger is in player inventory
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::SCHRODINGER, $this->player);

        // given an action event for public action
        $actionResult = new ActionEvent(
            actionConfig: $this->actionConfig(),
            actionProvider: $this->player,
            player: $this->player,
            tags: $this->actionConfig()->getActionTags(),
        );
        $result = new Success();
        $result->setVisibility(VisibilityEnum::PRIVATE);
        $actionResult->setActionResult($result);

        // when a listen for result action event
        $this->actionSubscriber->onResultAction($actionResult);
        $logId = $this->roomLogRepository->findOneByLogKey(LogEnum::CAT_MEOW)?->getId();

        // then cat should not meow
        self::assertNull($logId);
    }

    public function testCatShouldNotMeowOnSecretActionLog(): void
    {
        // given schrodinger is in player inventory
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::SCHRODINGER, $this->player);

        // given an action event for public action
        $actionResult = new ActionEvent(
            actionConfig: $this->actionConfig(),
            actionProvider: $this->player,
            player: $this->player,
            tags: $this->actionConfig()->getActionTags(),
        );
        $result = new Success();
        $result->setVisibility(VisibilityEnum::SECRET);
        $actionResult->setActionResult($result);

        // when a listen for result action event
        $this->actionSubscriber->onResultAction($actionResult);
        $logId = $this->roomLogRepository->findOneByLogKey(LogEnum::CAT_MEOW)?->getId();

        // then cat should not meow
        self::assertNull($logId);
    }

    public function testCatShouldNotMeowOnHiddenActionLog(): void
    {
        // given schrodinger is in player inventory
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::SCHRODINGER, $this->player);

        // given an action event for public action
        $actionResult = new ActionEvent(
            actionConfig: $this->actionConfig(),
            actionProvider: $this->player,
            player: $this->player,
            tags: $this->actionConfig()->getActionTags(),
        );
        $result = new Success();
        $result->setVisibility(VisibilityEnum::HIDDEN);
        $actionResult->setActionResult($result);

        // when a listen for result action event
        $this->actionSubscriber->onResultAction($actionResult);
        $logId = $this->roomLogRepository->findOneByLogKey(LogEnum::CAT_MEOW)?->getId();

        // then cat should not meow
        self::assertNull($logId);
    }

    private function actionConfig(): ActionConfig
    {
        return ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SEARCH));
    }
}
