<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\ExtinguishManually;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\DataFixtures\DaedalusConfigFixtures;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExtinguishManuallyActionCest
{
    private ExtinguishManually $extinguishManually;
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->extinguishManually = $I->grabService(ExtinguishManually::class);
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testExtinguishManually(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, DaedalusConfigFixtures::class, LocalizationConfigFixtures::class]);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig
            ->setName(StatusEnum::ATTEMPT)
            ->setVisibility(VisibilityEnum::HIDDEN)
        ;
        $I->haveInRepository($attemptConfig);
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(StatusEnum::FIRE)
        ;
        $I->haveInRepository($statusConfig);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$attemptConfig, $statusConfig]))
            ->setDaedalusConfig($daedalusConfig)
        ;
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setGameStatus(GameStatusEnum::CURRENT)
            ->setNeron($neron)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $statusEvent = new StatusEvent(StatusEnum::FIRE, $room, EventEnum::NEW_CYCLE, new \DateTime());

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::EXTINGUISH_MANUALLY)
            ->setScope(ActionScopeEnum::SELF)
            ->setActionCost($actionCost)
            ->setSuccessRate(101)
        ;
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        $characterConfig
            ->setActions(new ArrayCollection([$action]));

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 10,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $this->extinguishManually->loadParameters($action, $player);

        $I->assertTrue($this->extinguishManually->isVisible());
        $I->assertNull($this->extinguishManually->cannotExecuteReason());

        $this->extinguishManually->execute();

        $I->assertEquals(9, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::EXTINGUISH_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertFalse($this->extinguishManually->isVisible());
    }
}
