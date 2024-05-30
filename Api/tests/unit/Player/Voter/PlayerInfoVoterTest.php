<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Voter;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerInfoRepository;
use Mush\Player\Voter\PlayerInfoVoter;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;
use Mush\User\Factory\UserFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @internal
 */
final class PlayerInfoVoterTest extends TestCase
{
    private PlayerInfoVoter $voter;

    private InMemoryPlayerInfoRepository $playerInfoRepository;

    protected function setUp(): void
    {
        $this->playerInfoRepository = new InMemoryPlayerInfoRepository();

        $this->voter = new PlayerInfoVoter($this->playerInfoRepository);
    }

    public function testModeratorShouldNotSeeAPlayerInTheirOwnDaedalus(): void
    {
        // given a Daedalus
        $daedalus = DaedalusFactory::createDaedalus();

        // given a player in the Daedalus
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        $this->playerInfoRepository->save($player->getPlayerInfo());

        // given the user behind the player is a moderator
        $moderator = $player->getUser();
        $moderator->setRoles([RoleEnum::MODERATOR]);

        // given another player in the Daedalus
        $anotherPlayer = PlayerFactory::createPlayerWithDaedalus($daedalus);

        $this->playerInfoRepository->save($anotherPlayer->getPlayerInfo());

        // when the moderator tries to see the other player
        $result = $this->voter->vote(
            token: $this->getTokenForUser($moderator),
            subject: $anotherPlayer->getPlayerInfo(),
            attributes: [PlayerInfoVoter::PLAYER_INFO_VIEW]
        );

        // then the moderator should not be able to see the other player
        self::assertEquals(PlayerInfoVoter::ACCESS_DENIED, $result);
    }

    public function testModeratorShouldAPlayerInAnotherDaedalus(): void
    {
        // given a player in the Daedalus
        $player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());

        $this->playerInfoRepository->save($player->getPlayerInfo());

        // given the user behind the player is a moderator
        $moderator = $player->getUser();
        $moderator->setRoles([RoleEnum::MODERATOR]);

        // given another player in another Daedalus
        $anotherPlayer = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());

        $this->playerInfoRepository->save($anotherPlayer->getPlayerInfo());

        // when the moderator tries to see the other player
        $result = $this->voter->vote(
            token: $this->getTokenForUser($moderator),
            subject: $anotherPlayer->getPlayerInfo(),
            attributes: [PlayerInfoVoter::PLAYER_INFO_VIEW]
        );

        // then the moderator should be able to see the other player
        self::assertEquals(PlayerInfoVoter::ACCESS_GRANTED, $result);
    }

    public function testModeratorShouldSeeAPlayerIfNotPlaying(): void
    {
        // given a player in the Daedalus
        $player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());

        $this->playerInfoRepository->save($player->getPlayerInfo());

        // given a moderator (not playing)
        $moderator = UserFactory::createModerator();

        // when the moderator tries to see the other player
        $result = $this->voter->vote(
            token: $this->getTokenForUser($moderator),
            subject: $player->getPlayerInfo(),
            attributes: [PlayerInfoVoter::PLAYER_INFO_VIEW]
        );

        // then the moderator should be able to see the other player
        self::assertEquals(PlayerInfoVoter::ACCESS_GRANTED, $result);
    }

    private function getTokenForUser(User $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, 'password', $user->getRoles());
    }
}
