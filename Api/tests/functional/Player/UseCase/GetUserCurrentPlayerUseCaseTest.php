<?php

declare(strict_types=1);

namespace Mush\tests\unit\Player\UseCase;

use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerInfoRepository;
use Mush\Player\UseCase\GetUserCurrentPlayerUseCase;
use Mush\User\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GetUserCurrentPlayerUseCaseTest extends TestCase
{
    private InMemoryPlayerInfoRepository $playerInfoRepository;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->playerInfoRepository = new InMemoryPlayerInfoRepository();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->playerInfoRepository->clear();
    }

    public function testShouldThrowIfUserDoesNotHavePlayerInGame(): void
    {
        // arrange
        $user = UserFactory::createUser();

        // assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("User {$user->getUsername()} is not ingame.");

        // act
        $getUserPlayerUseCase = new GetUserCurrentPlayerUseCase($this->playerInfoRepository);
        $getUserPlayerUseCase->execute($user);
    }

    public function testShouldReturnCurrentUserPlayerWhenUserHasPlayedMultipleGames(): void
    {
        // given a user
        $user = UserFactory::createUser();

        // given user has a player in a game
        $alivePlayer = PlayerFactory::createPlayerForUser($user);

        // given user already had a dead player
        $deadPlayer = PlayerFactory::createPlayerForUser($user);
        $deadPlayer->getPlayerInfo()->setGameStatus(GameStatusEnum::CLOSED);

        // save players
        $this->playerInfoRepository->save($alivePlayer->getPlayerInfo());
        $this->playerInfoRepository->save($deadPlayer->getPlayerInfo());

        // when I get the current player for the user
        $getUserPlayerUseCase = new GetUserCurrentPlayerUseCase($this->playerInfoRepository);
        $result = $getUserPlayerUseCase->execute($user);

        // then I should get the alive player
        self::assertSame($alivePlayer, $result);
    }
}
