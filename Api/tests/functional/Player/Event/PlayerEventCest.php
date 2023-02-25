<?php

namespace functional\Player\Event;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerEventCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testDispatchPlayerDeath(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::DEMORALIZED)
            ->setVisibility(VisibilityEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$statusConfig])]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 89,
            'filledAt' => new \DateTime(),
        ]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $playerEvent = new PlayerEvent($player, EndCauseEnum::CLUMSINESS, new \DateTime());
        $playerEvent->setVisibility(VisibilityEnum::PUBLIC);

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);

        $I->assertEquals(GameStatusEnum::FINISHED, $playerInfo->getGameStatus());
        $closedPlayer = $playerInfo->getClosedPlayer();

        $I->assertEquals($closedPlayer->getEndCause(), EndCauseEnum::CLUMSINESS);
        $I->assertEquals($closedPlayer->getMessage(), null);
        $I->assertEquals($closedPlayer->getCycleDeath(), 5);
        $I->assertEquals($closedPlayer->getDayDeath(), 89);
        $I->assertEquals($closedPlayer->getClosedDaedalus(), $daedalusInfo->getClosedDaedalus());
        $I->assertFalse($closedPlayer->isMush());

        $I->dontSeeInRepository(Status::class);
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => LogEnum::DEATH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testDispatchPlayerDeathMush(FunctionalTester $I)
    {
        $mushConfig = new StatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$mushConfig])]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 89,
            'filledAt' => new \DateTime(),
        ]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $status = new Status($player, $mushConfig);
        $I->haveInRepository($status);

        $playerEvent = new PlayerEvent($player, EndCauseEnum::CLUMSINESS, new \DateTime());
        $playerEvent->setVisibility(VisibilityEnum::PUBLIC);

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);

        $I->assertEquals(GameStatusEnum::FINISHED, $playerInfo->getGameStatus());
        $closedPlayer = $playerInfo->getClosedPlayer();

        $I->assertEquals($closedPlayer->getEndCause(), EndCauseEnum::CLUMSINESS);
        $I->assertEquals($closedPlayer->getMessage(), null);
        $I->assertEquals($closedPlayer->getCycleDeath(), 5);
        $I->assertEquals($closedPlayer->getDayDeath(), 89);
        $I->assertEquals($closedPlayer->getClosedDaedalus(), $daedalusInfo->getClosedDaedalus());
        $I->assertTrue($closedPlayer->isMush());

        $I->dontSeeInRepository(Status::class);
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => LogEnum::DEATH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testDispatchInfection(FunctionalTester $I)
    {
        $mushStatusConfig = new ChargeStatusConfig();
        $mushStatusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushStatusConfig);

        $sporeConfig = new ChargeStatusConfig();
        $sporeConfig
            ->setStatusName(PlayerStatusEnum::SPORES)
            ->setVisibility(VisibilityEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($sporeConfig);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FUNGIC_INFECTION)
                ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::INFECTION)
            ->setDiseases([
                DiseaseEnum::FUNGIC_INFECTION => 1,
            ])
            ->buildName(GameConfigENum::TEST)
        ;
        $I->haveInRepository($diseaseCause);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$mushStatusConfig, $sporeConfig]),
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCause]),
            'diseaseConfig' => new ArrayCollection([$diseaseConfig]),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $I->haveInRepository($sporeConfig);
        $sporeStatus = new ChargeStatus($player, $sporeConfig);
        $sporeStatus
            ->setCharge(0)
        ;
        $I->haveInRepository($sporeStatus);

        $playerEvent = new PlayerEvent($player, ActionEnum::INFECT, new \DateTime());

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(1, $player->getStatuses()->first()->getCharge());
        $I->assertEquals($room, $player->getPlace());

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());
        $I->assertEquals($room, $player->getPlace());

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        $I->assertCount(2, $player->getStatuses());
        $I->assertEquals($room, $player->getPlace());
    }

    public function testDispatchConversion(FunctionalTester $I)
    {
        $mushStatusConfig = new ChargeStatusConfig();
        $mushStatusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($mushStatusConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$mushStatusConfig]),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player->setMoralPoint(8);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $sporeConfig = new ChargeStatusConfig();
        $sporeConfig
            ->setStatusName(PlayerStatusEnum::SPORES)
            ->setVisibility(VisibilityEnum::MUSH)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($sporeConfig);
        $sporeStatus = new ChargeStatus($player, $sporeConfig);
        $sporeStatus
            ->setCharge(3)
        ;
        $I->haveInRepository($sporeStatus);

        $playerEvent = new PlayerEvent($player, ActionEnum::INFECT, new \DateTime());

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CONVERSION_PLAYER);

        $I->assertCount(2, $player->getStatuses());
        $I->assertEquals(0, $player->getStatuses()->first()->getCharge());
        $I->assertEquals($room, $player->getPlace());
        $I->assertEquals(12, $player->getMoralPoint());
    }
}
