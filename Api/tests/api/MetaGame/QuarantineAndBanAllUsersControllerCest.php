<?php

declare(strict_types=1);

namespace Mush\Tests\api\MetaGame;

use Mush\Tests\ApiTester;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;
use Mush\User\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class QuarantineAndBanAllUsersControllerCest
{
    private User $moderator;
    private User $user1;
    private User $user2;
    private UserRepositoryInterface $userRepository;

    public function _before(ApiTester $I): void
    {
        $this->userRepository = $I->grabService(UserRepositoryInterface::class);
        $this->user1 = $I->loginUser(RoleEnum::USER);
        $this->user2 = $I->loginUser(RoleEnum::USER);
        $this->moderator = $I->loginUser(RoleEnum::MODERATOR);
    }

    public function shouldBanAllUsersWhenPostingToEndpoint(ApiTester $I): void
    {
        $this->givenUsersAreNotBanned();

        $this->whenSendingBanAllUsersRequest($I);

        $this->thenAllUsersShouldBeBanned();
        $this->thenResponseShouldBeSuccessful($I);
    }

    private function givenUsersAreNotBanned(): void
    {
        \assert(!$this->user1->isBanned(), 'User1 should not be banned initially');
        \assert(!$this->user2->isBanned(), 'User2 should not be banned initially');
    }

    private function whenSendingBanAllUsersRequest(ApiTester $I): void
    {
        $I->sendPostRequest('moderation/ban-all-users', [
            'userUuids' => [
                $this->user1->getUserIdentifier(),
                $this->user2->getUserIdentifier(),
            ],
            'reason' => 'Test ban reason',
            'message' => 'Test ban message',
        ]);
    }

    private function thenAllUsersShouldBeBanned(): void
    {
        $user1 = $this->userRepository->findOneByIdOrThrow($this->user1->getId());
        $user2 = $this->userRepository->findOneByIdOrThrow($this->user2->getId());

        \assert($user1->isBanned(), 'User1 should be banned');
        \assert($user2->isBanned(), 'User2 should be banned');
    }

    private function thenResponseShouldBeSuccessful(ApiTester $I): void
    {
        $I->seeResponseCodeIs(Response::HTTP_OK);
    }
}
