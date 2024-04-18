<?php

namespace Mush\Tests\functional\User\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;
use Mush\User\Service\UserService;

class UserServiceCest
{
    private UserService $userService;

    public function _before(FunctionalTester $I)
    {
        $this->userService = $I->grabService(UserService::class);
    }

    public function testPersist(FunctionalTester $I)
    {
        $user = new User();
        $user
            ->setUserId('userId')
            ->setUsername('Breut');

        $this->userService->persist($user);

        $I->seeInRepository(User::class, ['username' => 'Breut']);
    }

    public function testFindById(FunctionalTester $I)
    {
        /** @var User $user1 */
        $user1 = $I->have(User::class, ['username' => 'Breut', 'userId' => 'userId1']);

        /** @var User $user2 */
        $user2 = $I->have(User::class, ['username' => 'Evian', 'userId' => 'userId2']);

        $result = $this->userService->findById($user1->getId());
        $I->assertEquals($user1, $result);
        $I->assertEquals('userId1', $result->getUserId());
        $I->assertEquals('Breut', $result->getUsername());

        $result = $this->userService->findById($user2->getId() + 100);
        $I->assertNull($result);
    }

    public function testFindByUserId(FunctionalTester $I)
    {
        /** @var User $user1 */
        $user1 = $I->have(User::class, ['username' => 'Breut', 'userId' => 'userId1']);

        /** @var User $user2 */
        $user2 = $I->have(User::class, ['username' => 'Evian', 'userId' => 'userId2']);

        $result = $this->userService->findUserByUserId('userId1');
        $I->assertEquals($user1, $result);
        $I->assertEquals('userId1', $result->getUserId());
        $I->assertEquals('Breut', $result->getUsername());

        $result = $this->userService->findUserByUserId($user2->getId());
        $I->assertNull($result);
    }

    public function testCreateUser(FunctionalTester $I)
    {
        $this->userService->createUser('userId1', 'Breut');

        $I->seeInRepository(User::class, ['username' => 'Breut']);
    }

    public function testFindByUserDaedaluses(FunctionalTester $I)
    {
        /** @var User $user1 */
        $user1 = $I->have(User::class, ['username' => 'Breut', 'userId' => 'userId1']);

        /** @var User $user2 */
        $user2 = $I->have(User::class, ['username' => 'Evian', 'userId' => 'userId2']);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var Daedalus $daedalus1 */
        $daedalus1 = $I->have(Daedalus::class);
        $daedalusInfo1 = new DaedalusInfo($daedalus1, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo1);

        /** @var Daedalus $daedalus2 */
        $daedalus2 = $I->have(Daedalus::class, ['name' => 'daedalus_test_2']);
        $daedalusInfo2 = new DaedalusInfo($daedalus2, $gameConfig, $localizationConfig);
        $daedalusInfo2->setName('daedalus_test_2');
        $I->haveInRepository($daedalusInfo2);

        // Create players
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus1,
        ]);
        $player->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($player, $user1, $characterConfig);
        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, [
            'daedalus' => $daedalus2,
        ]);
        $player2->setPlayerVariables($characterConfig);
        $playerInfo2 = new PlayerInfo($player2, $user1, $characterConfig);
        $I->haveInRepository($playerInfo2);
        $player2->setPlayerInfo($playerInfo2);
        $I->refreshEntities($player2);

        $result = $this->userService->findUserDaedaluses($user1);
        $I->assertCount(2, $result);

        $result = $this->userService->findUserDaedaluses($user2);
        $I->assertEmpty($result);
    }
}
