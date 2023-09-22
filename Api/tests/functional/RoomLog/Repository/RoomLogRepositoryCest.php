<?php

namespace Mush\Tests\functional\RoomLog\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Repository\RoomLogRepository;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class RoomLogRepositoryCest
{
    private RoomLogRepository $repository;

    public function _before(FunctionalTester $I)
    {
        $this->repository = $I->grabService(RoomLogRepository::class);
    }

    public function testRoomLogVisibility(FunctionalTester $I)
    {
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $place */
        $place = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $place,
            'daedalus' => $daedalus,
        ]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $roomLog = new RoomLog();
        $roomLog
            ->setPlace($place->getName())
            ->setDaedalusInfo($daedalusInfo)
            ->setLog('someLog')
            ->setType('type')
            ->setDate(new \DateTime())
            ->setDay(1)
            ->setCycle(1)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($playerInfo);

        $I->assertCount(1, $logs);

        $roomLog->setVisibility(VisibilityEnum::PRIVATE);

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($playerInfo);

        $I->assertEmpty($logs);

        $roomLog->setPlayerInfo($playerInfo);

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($playerInfo);

        $I->assertCount(1, $logs);
    }

    public function testRoomLogPlace(FunctionalTester $I)
    {
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $place */
        $place = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $place,
            'daedalus' => $daedalus,
        ]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $roomLog = new RoomLog();
        $roomLog
            ->setLog('someLog')
            ->setPlace('test')
            ->setType('type')
            ->setDate(new \DateTime())
            ->setDay(1)
            ->setCycle(1)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($playerInfo);

        $I->assertEmpty($logs);

        $roomLog
            ->setDaedalusInfo($daedalusInfo)
            ->setPlace($place->getName())
        ;

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($playerInfo);

        $I->assertCount(1, $logs);
    }

    public function testRoomLogAge(FunctionalTester $I)
    {
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $place */
        $place = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $place,
            'daedalus' => $daedalus,
        ]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $roomLog = new RoomLog();
        $roomLog
            ->setLog('someLog')
            ->setType('type')
            ->setDate(new \DateTime())
            ->setDay(1)
            ->setCycle(1)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setPlace($place->getName())
            ->setDaedalusInfo($daedalusInfo)
        ;

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($playerInfo);

        $I->assertCount(1, $logs);

        $roomLog->setDate(new \DateTime('-25 hour'));

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($playerInfo);

        $I->assertEmpty($logs);

        $roomLog->setDate(new \DateTime('-23 hour'));

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($playerInfo);

        $I->assertCount(1, $logs);
    }
}
