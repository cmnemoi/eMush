<?php

namespace Mush\Tests\functional\Disease\Listener;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Listener\PlayerSubscriber;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;

class PlayerSubscriberCest
{
    private PlayerSubscriber $playerSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->playerSubscriber = $I->grabService(PlayerSubscriber::class);
    }

    public function testOnCycleDisease(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $room = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);

        $characterConfig = $I->have(CharacterConfig::class, [
            'gameConfig' => $gameConfig,
        ]);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('disease')
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::CYCLE)
            ->setDiseases(['disease' => 1])
            ->setDiseasesRate(100)
        ;
        $I->haveInRepository($diseaseCauseConfig);

        $cycleDiseaseEvent = new PlayerEvent($player, PlayerEvent::CYCLE_DISEASE, new DateTime());

        $this->playerSubscriber->onCycleDisease($cycleDiseaseEvent);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
        ]);
    }

    public function testOnDeathPlayer(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $room = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);

        $characterConfig = $I->have(CharacterConfig::class, [
            'gameConfig' => $gameConfig,
        ]);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        $otherPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('disease')
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::TRAUMA)
            ->setDiseases(['disease' => 1])
            ->setDiseasesRate(100)
        ;
        $I->haveInRepository($diseaseCauseConfig);

        $deathPlayerEvent = new PlayerEvent($otherPlayer, PlayerEvent::DEATH_PLAYER, new DateTime());

        $this->playerSubscriber->onDeathPlayer($deathPlayerEvent);

        $I->seeInRepository(RoomLog::class, [
            'player' => $player,
            'place' => $room,
            'log' => LogEnum::TRAUMA_DISEASE,
        ]);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
        ]);
    }

    public function testOnInfectionPlayer(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $daedalus = $I->have(Daedalus::class, [
            'gameConfig' => $gameConfig,
        ]);

        $room = $I->have(Place::class, [
            'daedalus' => $daedalus,
        ]);

        $characterConfig = $I->have(CharacterConfig::class, [
            'gameConfig' => $gameConfig,
        ]);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        $mushPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setName(PlayerStatusEnum::MUSH);
        $I->haveInRepository($mushConfig);

        $mushStatus = new ChargeStatus($mushPlayer, $mushConfig);
        $I->haveInRepository($mushStatus);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('disease')
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseConfig);

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setGameConfig($gameConfig)
            ->setName(DiseaseCauseEnum::INFECTION)
            ->setDiseases(['disease' => 1])
            ->setDiseasesRate(100)
        ;
        $I->haveInRepository($diseaseCauseConfig);

        $infectionPlayerEvent = new PlayerEvent($player, PlayerEvent::INFECTION_PLAYER, new DateTime());

        $this->playerSubscriber->onInfectionPlayer($infectionPlayerEvent);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
        ]);
    }

    public function testOnNewPlayer(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $daedalus = $I->have(Daedalus::class, [
             'gameConfig' => $gameConfig,
         ]);

        $room = $I->have(Place::class, [
             'daedalus' => $daedalus,
         ]);

        $characterConfig = $I->have(CharacterConfig::class, [
             'gameConfig' => $gameConfig,
             'initDiseases' => ['disease'],
         ]);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setName('disease')
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($diseaseConfig);

        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'characterConfig' => $characterConfig,
            'place' => $room,
        ]);

        $newPlayerEvent = new PlayerEvent($player, PlayerEvent::NEW_PLAYER, new DateTime());

        $this->playerSubscriber->onNewPlayer($newPlayerEvent);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $player,
            'diseaseConfig' => $diseaseConfig,
        ]);
    }
}
