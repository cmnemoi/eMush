<?php

declare(strict_types=1);

namespace Mush\MetaGame\UseCase;

use Mush\User\Repository\UserRepositoryInterface;

final class ResetRulesAcceptanceForAllUsersUseCase
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function execute(): void
    {
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $user->refuseRules();
            $this->userRepository->save($user);
        }
    }
}
