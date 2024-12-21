<?php

declare(strict_types=1);

namespace Mush\tests\unit\RoomLog\Listener;

use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\FakeD100RollService as FakeD100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService as FakeGetRandomInteger;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Listener\ActionSubscriber;
use Mush\RoomLog\Repository\InMemoryRoomLogRepository;
use Mush\RoomLog\Service\RoomLogService;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ActionSubscriberTest extends TestCase
{
    private ActionSubscriber $actionSubscriber;
    private FakeGetRandomInteger $getRandomInteger;
    private InMemoryRoomLogRepository $roomLogRepository;
    private RoomLogService $roomLogService;
    private FakeD100Roll $roomLogServiceD100Roll;
    private FakeD100Roll $d100Roll;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->d100Roll = new FakeD100Roll();
        $this->getRandomInteger = new FakeGetRandomInteger(result: 0);
        $this->roomLogRepository = new InMemoryRoomLogRepository();
        $this->roomLogServiceD100Roll = new FakeD100Roll();
        $translationService = $this->createStub(TranslationServiceInterface::class);

        $this->roomLogService = new RoomLogService(
            $this->roomLogServiceD100Roll,
            $this->getRandomInteger,
            $this->roomLogRepository,
            $translationService,
        );

        $this->actionSubscriber = new ActionSubscriber(
            $this->d100Roll,
            $this->roomLogService,
            $translationService,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->roomLogRepository->clear();
    }

    public function testObservantShouldPrintNoticedSomethingLogAfterAnAction(): void
    {
        // given player is observant
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ELEESHA, $daedalus);
        Skill::createByNameForPlayer(SkillEnum::OBSERVANT, $player);

        // given a witness to reveal the log
        $playerWitness = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);

        // given unnoticed action log
        $log = $this->roomLogService->createLog(
            logKey: ActionLogEnum::SUICIDE_SUCCESS,
            place: $player->getPlace(),
            visibility: VisibilityEnum::SECRET,
            type: 'action_log',
            player: $player,
        );

        // when player performs an action
        $this->whenPlayerPerformsAction($player);

        // then noticed something log should be created
        $roomLog = $this->roomLogRepository->findByPlayerAndLogKey($player, LogEnum::OBSERVANT_NOTICED_SOMETHING);
        self::assertNotNull($roomLog);
    }

    public function testObservantShouldNotNoticeSameLogTwice(): void
    {
        // given player is observant
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ELEESHA, $daedalus);
        Skill::createByNameForPlayer(SkillEnum::OBSERVANT, $player);

        // given a witness to reveal the log
        $playerWitness = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);

        // given an unoticed action log
        $this->roomLogService->createLog(
            logKey: ActionLogEnum::SUICIDE_SUCCESS,
            place: $player->getPlace(),
            visibility: VisibilityEnum::SECRET,
            type: 'action_log',
            player: $player,
        );

        // given player performs the same action
        $this->whenPlayerPerformsAction($player);

        // when player performs the same action
        $this->whenPlayerPerformsAction($player);

        // then I should see only one noticed something log
        $noticedLogs = $this->roomLogRepository->findAllByPlayerAndLogKey($player, LogEnum::OBSERVANT_NOTICED_SOMETHING);
        self::assertCount(1, $noticedLogs);
    }

    private function whenPlayerPerformsAction(Player $player): void
    {
        $actionEvent = new ActionEvent(
            actionConfig: $this->actionConfig(),
            actionProvider: $player,
            player: $player,
            tags: $this->actionConfig()->getActionTags()
        );
        $actionEvent->setActionResult(new Success());
        $this->actionSubscriber->onPostAction($actionEvent);
    }

    private function actionConfig(): ActionConfig
    {
        return ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SUICIDE));
    }
}
