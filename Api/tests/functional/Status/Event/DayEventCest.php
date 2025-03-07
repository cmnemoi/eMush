<?php

namespace Mush\Tests\functional\Status\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Listener\StatusCycleSubscriber;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class DayEventCest
{
    private StatusCycleSubscriber $cycleSubscriber;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(StatusCycleSubscriber::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    // tests
    public function testChargeStatusDaySubscriber(FunctionalTester $I)
    {
        // Day Increment
        $daedalus = new Daedalus();
        $time = new \DateTime();
        $characterConfig = $I->have(CharacterConfig::class);
        $user = $I->have(User::class);

        $daedalus->setCycle(1);

        $statusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => 'decomposing']);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setMaxCharge(1)
            ->setAutoRemove(false)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
            ->buildName(GameConfigEnum::TEST)
            ->setStartCharge(0);
        $I->haveInRepository($statusConfig);

        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $player,
            [],
            new \DateTime()
        );

        $dayEvent = new StatusCycleEvent($status, $player, [EventEnum::NEW_DAY], $time);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(1, $status->getCharge());

        // Day decrement
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::FROZEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setMaxCharge(1)
            ->setAutoRemove(false)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->buildName(GameConfigEnum::TEST)
            ->setStartCharge(1);
        $I->haveInRepository($statusConfig);

        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $player,
            [],
            new \DateTime()
        );

        $dayEvent = new StatusCycleEvent($status, $player, [EventEnum::NEW_DAY], $time);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(0, $status->getCharge());

        // Day reset
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::DECOMPOSING)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setMaxCharge(5)
            ->setAutoRemove(false)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_RESET)
            ->buildName(GameConfigEnum::TEST)
            ->setStartCharge(1);
        $I->haveInRepository($statusConfig);

        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $player,
            [],
            new \DateTime()
        );

        $dayEvent = new StatusCycleEvent($status, $player, [EventEnum::NEW_DAY], $time);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(5, $status->getCharge());

        // Specialist point increment
        /** @var ChargeStatusConfig $statusConfig */
        $statusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => SkillPointsEnum::SHOOTER_POINTS]);

        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromConfig(
            $statusConfig,
            $player,
            [],
            new \DateTime()
        );
        $this->statusService->updateCharge($status, -2, [], new \DateTime());
        $I->assertEquals(2, $status->getCharge());

        $dayEvent = new StatusCycleEvent($status, $player, [EventEnum::NEW_DAY], $time);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(4, $status->getCharge());
    }
}
