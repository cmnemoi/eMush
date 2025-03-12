<?php

namespace Mush\Tests\functional\RoomLog\Repository;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
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
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class RoomLogRepositoryCest extends AbstractFunctionalTest
{
    private RoomLogRepository $repository;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
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

        $daedalus->setDay(1);
        $daedalus->setCycle(1);

        $roomLog = new RoomLog();
        $roomLog
            ->setPlace($place->getName())
            ->setDaedalusInfo($daedalusInfo)
            ->setLog('someLog')
            ->setType('type')
            ->setCreatedAt(new \DateTime())
            ->setDay(1)
            ->setCycle(1)
            ->setVisibility(VisibilityEnum::PUBLIC);

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertCount(1, $logs);

        $roomLog->setVisibility(VisibilityEnum::PRIVATE);

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertEmpty($logs);

        $roomLog->setPlayerInfo($playerInfo);

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($player);

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

        $daedalus->setDay(1);
        $daedalus->setCycle(1);

        $roomLog = new RoomLog();
        $roomLog
            ->setLog('someLog')
            ->setPlace('test')
            ->setType('type')
            ->setCreatedAt(new \DateTime())
            ->setDay(1)
            ->setCycle(1)
            ->setVisibility(VisibilityEnum::PUBLIC);

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertEmpty($logs);

        $roomLog
            ->setDaedalusInfo($daedalusInfo)
            ->setPlace($place->getName());

        $I->haveInRepository($roomLog);

        $logs = $this->repository->getPlayerRoomLog($player);

        $I->assertCount(1, $logs);
    }

    public function testPlayerCanSeeCurrentCycleRoomLogs(FunctionalTester $I)
    {
        // Given a player and a daedalus at day 2, cycle 2
        $entities = $this->createTestEntities($I);
        $entities['daedalus']->setDay(2);
        $entities['daedalus']->setCycle(2);

        // And a room log from the current cycle
        $this->createRoomLog($I, $entities, 2, 2);

        // When I get the player's room logs
        $logs = $this->repository->getPlayerRoomLog($entities['player']);

        // Then I should see the log
        $I->assertCount(1, $logs);
    }

    public function testPlayerCannotSeeOldRoomLogs(FunctionalTester $I)
    {
        // Given a player and a daedalus at day 2, cycle 2
        $entities = $this->createTestEntities($I);
        $entities['daedalus']->setDay(2);
        $entities['daedalus']->setCycle(2);

        // And a room log from a much older cycle (day 1, cycle 1)
        $this->createRoomLog($I, $entities, 1, 1);

        // When I get the player's room logs
        $logs = $this->repository->getPlayerRoomLog($entities['player']);

        // Then I should not see the log
        $I->assertEmpty($logs);
    }

    public function testPlayerCanSeePreviousCycleRoomLogs(FunctionalTester $I)
    {
        // Given a player and a daedalus at day 2, cycle 2
        $entities = $this->createTestEntities($I);
        $entities['daedalus']->setDay(2);
        $entities['daedalus']->setCycle(2);

        // And a room log from the previous cycle (day 2, cycle 1)
        $this->createRoomLog($I, $entities, 2, 1);

        // When I get the player's room logs
        $logs = $this->repository->getPlayerRoomLog($entities['player']);

        // Then I should see the log
        $I->assertCount(1, $logs);
    }

    public function testTrackerCanSeeOldRoomLogs(FunctionalTester $I)
    {
        // Given a player with the tracker skill and a daedalus at day 2, cycle 8
        $entities = $this->createTestEntities($I);
        $daedalus = $entities['daedalus'];
        $daedalus->setDay(2);
        $daedalus->setCycle(8);

        // Add tracker skill to player
        $player = $entities['player'];
        $trackerConfig = $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::TRACKER]);
        $gameConfig = $daedalus->getGameConfig();
        $gameConfig->addSkillConfig($trackerConfig);
        $I->haveInRepository($gameConfig);
        $this->addSkillToPlayer(SkillEnum::TRACKER, $I, $player);

        // And a room log from a much older cycle (day 1, cycle 1)
        $this->createRoomLog($I, $entities, 1, 1);

        // When I get the player's room logs
        $logs = $this->repository->getPlayerRoomLog($player);

        // Then I should see the log because trackers can see logs from up to 16 cycles ago
        $I->assertCount(1, $logs);
    }

    /**
     * @return array{daedalus: Daedalus, daedalusInfo: DaedalusInfo, place: Place, player: Player}
     */
    private function createTestEntities(FunctionalTester $I): array
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

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        $publicChannel = new Channel();
        $publicChannel
            ->setScope(ChannelScopeEnum::PUBLIC)
            ->setDaedalus($daedalusInfo);
        $I->haveInRepository($publicChannel);

        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        return [
            'daedalus' => $daedalus,
            'daedalusInfo' => $daedalusInfo,
            'place' => $place,
            'player' => $player,
        ];
    }

    private function createRoomLog(FunctionalTester $I, array $entities, int $day, int $cycle): RoomLog
    {
        $roomLog = new RoomLog();
        $roomLog
            ->setLog('someLog')
            ->setType('type')
            ->setCreatedAt(new \DateTime())
            ->setDay($day)
            ->setCycle($cycle)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setPlace($entities['place']->getName())
            ->setDaedalusInfo($entities['daedalusInfo']);

        $I->haveInRepository($roomLog);

        return $roomLog;
    }
}
