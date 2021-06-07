<?php

namespace Mush\Tests\functional\RoomLog\Repository;

use App\Tests\FunctionalTester;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Repository\RoomLogRepository;

class RoomLogRepositoryCest
{
    private RoomLogRepository $repository;

    public function _before(FunctionalTester $I)
    {
        $this->repository = $I->grabService(RoomLogRepository::class);
    }

    public function testRoomLogVisibility(FunctionalTester $I)
    {
        $place = $I->have(Place::class);

        $player = $I->have(Player::class, [
            'place' => $place,
        ]);

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

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertCount(1, $logs);

        $roomLog->setVisibility(VisibilityEnum::PRIVATE);

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertEmpty($logs);

        $roomLog->setPlayer($player);

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertCount(1, $logs);
    }

    public function testRoomLogPlace(FunctionalTester $I)
    {
        $place = $I->have(Place::class);

        $player = $I->have(Player::class, [
            'place' => $place,
        ]);

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

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertEmpty($logs);

        $roomLog->setPlace($place);

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertCount(1, $logs);
    }

    public function testRoomLogAge(FunctionalTester $I)
    {
        $place = $I->have(Place::class);

        $player = $I->have(Player::class, [
            'place' => $place,
        ]);

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

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertCount(1, $logs);

        $roomLog->setDate(new \DateTime('-25 hour'));

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertEmpty($logs);

        $roomLog->setDate(new \DateTime('-23 hour'));

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertCount(1, $logs);
    }
}
