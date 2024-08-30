<?php

declare(strict_types=1);

namespace Mush\tests\unit\RoomLog\Listener;

use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\FakeD100RollService;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Listener\ActionSubscriber;
use Mush\RoomLog\Service\FakeRoomLogService;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ActionSubscriberTest extends TestCase
{
    private ActionSubscriber $actionSubscriber;

    private FakeRoomLogService $roomLogService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->roomLogService = new FakeRoomLogService(
            new FakeD100RollService(isSuccessful: true),
        );

        $this->actionSubscriber = new ActionSubscriber(
            new FakeD100RollService(isSuccessful: true),
            $this->roomLogService,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->roomLogService->clear();
    }

    public function testObservantShouldPrintNoticedSomethingLogAfterAnAction(): void
    {
        // given player is observant
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ELEESHA, $daedalus);
        Skill::createByNameForPlayer(SkillEnum::OBSERVANT, $player);

        // given a witness to reveal the log
        $playerWitness = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);

        // when player performs an action
        $this->whenPlayerPerformsAction($player);

        // then noticed something log should be created
        $roomLog = $this->roomLogService->findByPlayerAndLogKey($player, LogEnum::OBSERVANT_NOTICED_SOMETHING);
        self::assertNotNull($roomLog);
    }

    private function whenPlayerPerformsAction(Player $player): void
    {
        $this->roomLogService->createLog(
            logKey: ActionLogEnum::SUICIDE_SUCCESS,
            place: $player->getPlace(),
            visibility: VisibilityEnum::SECRET,
            type: 'action_log',
            player: $player,
        );

        $actionEvent = new ActionEvent(
            actionConfig: $this->actionConfig(),
            actionProvider: $this->createStub(ActionProviderInterface::class),
            player: $player,
        );
        $actionEvent->setActionResult(new Success());
        $this->actionSubscriber->onPostAction($actionEvent);
    }

    private function actionConfig(): ActionConfig
    {
        return ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::SUICIDE));
    }
}
