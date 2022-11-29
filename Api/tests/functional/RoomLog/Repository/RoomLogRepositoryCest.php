<?php

namespace Mush\Tests\functional\RoomLog\Repository;

use App\Tests\FunctionalTester;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Repository\RoomLogRepository;
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
        /** @var Place $place */
        $place = $I->have(Place::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $place,
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
            ->setPlace($place)
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
        /** @var Place $place */
        $place = $I->have(Place::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $place,
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
        ;

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($playerInfo);

        $I->assertEmpty($logs);

        $roomLog->setPlace($place);

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($playerInfo);

        $I->assertCount(1, $logs);
    }

    public function testRoomLogAge(FunctionalTester $I)
    {
        /** @var Place $place */
        $place = $I->have(Place::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'place' => $place,
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
            ->setPlace($place)
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
