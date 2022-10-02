<?php

namespace functional\Player\Event;

use App\Tests\FunctionalTester;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use Mush\Game\Service\EventServiceInterface;

class PlayerEventCest
{
    private EventServiceInterface $eventServiceService;

    public function _before(FunctionalTester $I)
    {
        $this->eventServiceService = $I->grabService(EventServiceInterface::class);
    }

    public function testDispatchPlayerDeath(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['language' => LanguageEnum::FRENCH]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
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
            'user' => $user,
            'characterConfig' => $characterConfig,
        ]);

        $playerEvent = new PlayerEvent($player, EndCauseEnum::CLUMSINESS, new \DateTime());
        $playerEvent->setVisibility(VisibilityEnum::PUBLIC);

        $this->eventServiceService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);

        $I->assertEquals(GameStatusEnum::FINISHED, $player->getGameStatus());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => LogEnum::DEATH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testDispatchInfection(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'user' => $user]);

        $mushStatusConfig = new ChargeStatusConfig();
        $mushStatusConfig
            ->setName(PlayerStatusEnum::MUSH)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($mushStatusConfig);

        $sporeConfig = new ChargeStatusConfig();
        $sporeConfig->setName(PlayerStatusEnum::SPORES)->setVisibility(VisibilityEnum::MUSH);
        $I->haveInRepository($sporeConfig);
        $sporeStatus = new ChargeStatus($player, $sporeConfig);
        $sporeStatus
            ->setCharge(0)
        ;
        $I->haveInRepository($sporeStatus);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setGameConfig($gameConfig)
            ->setName(DiseaseEnum::FUNGIC_INFECTION)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setName(DiseaseCauseEnum::INFECTION)
            ->setDiseases([
                DiseaseEnum::FUNGIC_INFECTION => 1,
            ])
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseCause);

        $playerEvent = new PlayerEvent($player, ActionEnum::INFECT, new \DateTime());

        $this->eventServiceService->callEvent($playerEvent, PlayerEvent::INFECTION_PLAYER);

        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(1, $player->getStatuses()->first()->getCharge());
        $I->assertEquals($room, $player->getPlace());

        $this->eventServiceService->callEvent($playerEvent, PlayerEvent::INFECTION_PLAYER);

        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());
        $I->assertEquals($room, $player->getPlace());

        $this->eventServiceService->callEvent($playerEvent, PlayerEvent::INFECTION_PLAYER);

        $I->assertCount(2, $player->getStatuses());
        $I->assertEquals($room, $player->getPlace());
    }

    public function testDispatchConversion(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'user' => $user,
            'moralPoint' => 8,
        ]);

        $mushStatusConfig = new ChargeStatusConfig();
        $mushStatusConfig
            ->setName(PlayerStatusEnum::MUSH)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($mushStatusConfig);

        $sporeConfig = new ChargeStatusConfig();
        $sporeConfig->setName(PlayerStatusEnum::SPORES)->setVisibility(VisibilityEnum::MUSH);
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
