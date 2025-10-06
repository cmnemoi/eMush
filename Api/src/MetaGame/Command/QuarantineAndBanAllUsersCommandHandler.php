<?php

declare(strict_types=1);

namespace Mush\MetaGame\Command;

use Mush\MetaGame\Service\ModerationServiceInterface;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\User\Entity\User;
use Mush\User\Repository\UserRepositoryInterface;
use Mush\User\Service\TokenServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class QuarantineAndBanAllUsersCommandHandler
{
    public function __construct(
        private ModerationServiceInterface $moderationService,
        private PlayerRepositoryInterface $playerRepository,
        private UserRepositoryInterface $userRepository,
        private TokenServiceInterface $token,
    ) {}

    public function __invoke(QuarantineAndBanAllUsersCommand $command): void
    {
        $author = $this->userRepository->findOneByIdOrThrow($this->token->toUserId());
        $users = $this->userRepository->findByUuids($command->userUuids);

        foreach ($users as $user) {
            $this->banUser($user, $author, $command);
            $this->quarantinePlayerIfExists($user, $author, $command);
        }
    }

    private function banUser(
        User $user,
        User $author,
        QuarantineAndBanAllUsersCommand $command
    ): void {
        $this->moderationService->banUser(
            user: $user,
            author: $author,
            reason: $command->reason,
            message: $command->message,
            startingDate: $command->startingDate,
            duration: $command->duration,
        );
    }

    private function quarantinePlayerIfExists(
        User $user,
        User $author,
        QuarantineAndBanAllUsersCommand $command
    ): void {
        $player = $this->playerRepository->findByUser($user);
        if (!$player) {
            return;
        }

        $this->moderationService->quarantinePlayer(
            player: $player,
            author: $author,
            reason: $command->reason,
            message: $command->message,
        );
    }
}
