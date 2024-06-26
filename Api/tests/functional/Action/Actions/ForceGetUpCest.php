<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Flirt;
use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class ForceGetUpCest extends AbstractFunctionalTest
{
    private Hit $hitAction;
    private ActionConfig $hitActionConfig;

    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->hitAction = $I->grabService(Hit::class);
        $this->hitActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::HIT]);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testForceGetUp(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$statusConfig]))
            ->setDaedalusConfig($daedalusConfig);

        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::HIT)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setRange(ActionRangeEnum::PLAYER)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'name' => 'testForceGetUp',
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
            ->setHealthPoint(6);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, ['actionConfigs' => new ArrayCollection([$action])]);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player2->setPlayerVariables($characterConfig);
        $player2
            ->setActionPoint(2)
            ->setHealthPoint(6);
        $playerInfo2 = new PlayerInfo($player2, $user, $characterConfig2);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        $lyingDownStatus = new Status($player, $statusConfig);
        $I->haveInRepository($lyingDownStatus);

        $this->hitAction->loadParameters(
            actionConfig: $action,
            actionProvider: $player2,
            player: $player2,
            target: $player
        );

        $I->assertTrue($this->hitAction->isVisible());
        $I->assertNull($this->hitAction->cannotExecuteReason());

        $this->hitAction->execute();

        $I->assertCount(0, $player->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => LogEnum::FORCE_GET_UP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function shouldNotGivePlayerThreeActionPointsAtCycleChangeAfterForceGetUp(FunctionalTester $I): void
    {
        // given Chun has the Lying Down status
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given Chun has 1 AP
        $this->chun->setActionPoint(1);

        // given KT hits Chun so she gets up
        $this->hitAction->loadParameters(
            actionConfig: $this->hitActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun
        );
        $this->hitAction->execute();

        // when the cycle changes
        $playerCycleEvent = new PlayerCycleEvent($this->chun, [], new \DateTime());
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then Chun should have 2 AP (1 AP + 1 from cycle change)
        $I->assertEquals(2, $this->chun->getActionPoint());
    }

    public function shouldNotHappenIfTheActionIsNotOnTheList(FunctionalTester $I): void
    {
        // given Chun has the Lying Down status
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given Chun has 1 AP
        $this->chun->setActionPoint(1);

        // when KT flirts with Chun
        $flirtAction = $I->grabService(Flirt::class);
        $flirtActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::FLIRT]);
        $flirtAction->loadParameters(
            actionConfig: $flirtActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun
        );
        $flirtAction->execute();

        // then Chun should still have her Lying Down status
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::LYING_DOWN));
    }
}
