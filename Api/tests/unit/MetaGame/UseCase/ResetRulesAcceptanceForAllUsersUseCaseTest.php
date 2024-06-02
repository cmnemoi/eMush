<?php

declare(strict_types=1);

namespace Mush\tests\functional\MetaGame\UseCase;

use Mush\MetaGame\UseCase\ResetRulesAcceptanceForAllUsersUseCase;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;
use Mush\User\Repository\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ResetRulesAcceptanceForAllUsersUseCaseTest extends TestCase
{
    public function testResetRulesAcceptanceForAllUsers(): void
    {
        $userRepository = new InMemoryUserRepository();

        // given some users
        for ($i = 0; $i < 10; ++$i) {
            $userRepository->save(UserFactory::createUser());
        }

        // given those users have accepted the rules
        foreach ($userRepository->findAll() as $user) {
            $user->acceptRules();
        }

        // when the rules are reset for all users
        $useCase = new ResetRulesAcceptanceForAllUsersUseCase($userRepository);
        $useCase->execute();

        // then the rules are not accepted by any user
        foreach ($userRepository->findAll() as $user) {
            self::assertFalse($user->hasAcceptedRules());
        }
    }
}
