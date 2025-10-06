<?php

declare(strict_types=1);

namespace Mush\Tests\unit\MetaGame\Command;

use Mush\MetaGame\Command\QuarantineAndBanAllUsersCommand;
use Mush\MetaGame\Command\QuarantineAndBanAllUsersCommandHandler;
use Mush\MetaGame\TestDoubles\FakeModerationService;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Tests\unit\TestDoubles\Service\FakeTokenService;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;
use Mush\User\Repository\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class QuarantineAndBanAllUsersCommandHandlerTest extends TestCase
{
    private InMemoryUserRepository $userRepository;
    private InMemoryPlayerRepository $playerRepository;
    private FakeModerationService $moderationService;
    private User $author;
    private User $userToBan1;
    private User $userToBan2;
    private Player $player;

    public function testShouldBanUsersWithSpecifiedDuration(): void
    {
        $this->givenTwoUsersAndAnAuthor();

        $duration = new \DateInterval('P7D'); // 7 days
        $this->whenBanningUsersWithDuration($duration);

        $this->thenUsersShouldBeBannedWithDuration($duration);
    }

    public function testShouldBanUsersPermanentlyWhenNoDurationSpecified(): void
    {
        $this->givenTwoUsersAndAnAuthor();

        $this->whenBanningUsersWithoutDuration();

        $this->thenUsersShouldBeBannedUntil9999();
    }

    public function testShouldQuarantinePlayersIfTheyExist(): void
    {
        $this->givenTwoUsersAndAnAuthor();
        $this->givenOneUserHasAnActivePlayer();

        $this->whenBanningUsersWithoutDuration();

        $this->thenPlayerShouldBeQuarantined();
    }

    private function givenTwoUsersAndAnAuthor(): void
    {
        $this->userRepository = new InMemoryUserRepository();
        $this->playerRepository = new InMemoryPlayerRepository();
        $this->moderationService = new FakeModerationService();

        $this->author = UserFactory::createModerator();
        $this->userToBan1 = UserFactory::createUser();
        $this->userToBan2 = UserFactory::createUser();

        $this->userRepository->save($this->author);
        $this->userRepository->save($this->userToBan1);
        $this->userRepository->save($this->userToBan2);
    }

    private function givenOneUserHasAnActivePlayer(): void
    {
        $this->player = PlayerFactory::createPlayer();
        (new \ReflectionClass($this->player->getPlayerInfo()))->getProperty('user')->setValue(
            $this->player->getPlayerInfo(),
            $this->userToBan1
        );
        $this->playerRepository->save($this->player);
    }

    private function whenBanningUsersWithDuration(\DateInterval $duration): void
    {
        $command = new QuarantineAndBanAllUsersCommand(
            userUuids: [$this->userToBan1->getUserId(), $this->userToBan2->getUserId()],
            reason: 'Test ban reason',
            message: 'Test ban message',
            startingDate: new \DateTime(),
            duration: $duration
        );

        $handler = new QuarantineAndBanAllUsersCommandHandler(
            moderationService: $this->moderationService,
            token: new FakeTokenService($this->author),
            userRepository: $this->userRepository,
            playerRepository: $this->playerRepository,
        );
        ($handler)($command);
    }

    private function whenBanningUsersWithoutDuration(): void
    {
        $command = new QuarantineAndBanAllUsersCommand(
            userUuids: [$this->userToBan1->getUserId(), $this->userToBan2->getUserId()],
            reason: 'Test ban reason',
            message: 'Test ban message',
            startingDate: new \DateTime(),
            duration: null
        );

        $handler = new QuarantineAndBanAllUsersCommandHandler(
            moderationService: $this->moderationService,
            token: new FakeTokenService($this->author),
            userRepository: $this->userRepository,
            playerRepository: $this->playerRepository,
        );
        ($handler)($command);
    }

    private function thenUsersShouldBeBannedWithDuration(\DateInterval $duration): void
    {
        self::assertCount(2, $this->moderationService->bannedUsers, 'Two users should be banned');

        $expectedEndDate = (clone new \DateTime())->add($duration);
        foreach ($this->moderationService->bannedUsers as $banData) {
            self::assertEqualsWithDelta(
                $expectedEndDate->getTimestamp(),
                $banData['duration']->getTimestamp(),
                5,
                'Ban should have the correct duration'
            );
        }
    }

    private function thenUsersShouldBeBannedUntil9999(): void
    {
        self::assertCount(2, $this->moderationService->bannedUsers, 'Two users should be banned');

        foreach ($this->moderationService->bannedUsers as $banData) {
            self::assertNull($banData['duration'], 'Ban should have no duration (permanent)');
        }
    }

    private function thenPlayerShouldBeQuarantined(): void
    {
        self::assertCount(1, $this->moderationService->quarantinedPlayers, 'One player should be quarantined');
        self::assertSame($this->player, $this->moderationService->quarantinedPlayers[0]['player']);
    }
}
