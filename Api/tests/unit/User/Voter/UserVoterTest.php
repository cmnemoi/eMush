<?php

namespace Mush\Tests\unit\User\Voter;

use Mockery;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;
use Mush\User\Factory\UserFactory;
use Mush\User\Voter\UserVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @internal
 */
final class UserVoterTest extends TestCase
{
    private Voter $voter;

    /** @var Mockery\MockInterface|RoleHierarchyInterface */
    private RoleHierarchyInterface $roleHierarchy;

    protected function setUp(): void
    {
        $this->roleHierarchy = \Mockery::mock(RoleHierarchyInterface::class);

        $this->voter = new UserVoter($this->roleHierarchy);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testCanEditRole()
    {
        $loggedUser = new User();
        $loggedUser->setRoles([RoleEnum::ADMIN]);
        $editedUser = new User();
        $editedUser->setRoles([RoleEnum::ADMIN]);

        $this->roleHierarchy
            ->shouldReceive('getReachableRoleNames')
            ->andReturn([RoleEnum::MODERATOR, RoleEnum::ADMIN])
            ->once();

        $this->testVote(UserVoter::EDIT_USER_ROLE, $editedUser, $loggedUser, Voter::ACCESS_GRANTED);

        $loggedUser->setRoles([RoleEnum::MODERATOR]);
        $this->roleHierarchy
            ->shouldReceive('getReachableRoleNames')
            ->andReturn([RoleEnum::MODERATOR])
            ->once();
        $this->testVote(UserVoter::EDIT_USER_ROLE, $editedUser, $loggedUser, Voter::ACCESS_DENIED);
    }

    public function testIsNotBanned(): void
    {
        $user = new User();
        $this->banUser($user);

        self::assertTrue($user->isBanned());

        $this->testVote(UserVoter::IS_NOT_BANNED, $user, $user, Voter::ACCESS_DENIED);
    }

    public function testUserInGame(): void
    {
        $user = new User();
        $user->startGame();

        $this->testVote(UserVoter::USER_IN_GAME, $user, $user, Voter::ACCESS_GRANTED);
    }

    public function testUserNotInGame(): void
    {
        $user = new User();

        $this->testVote(UserVoter::NOT_IN_GAME, $user, $user, Voter::ACCESS_GRANTED);
    }

    public function testHasAcceptedRules(): void
    {
        $user = new User();

        $this->testVote(UserVoter::HAS_ACCEPTED_RULES, $user, $user, Voter::ACCESS_DENIED);
    }

    public function testIsConnected(): void
    {
        $user = new User();

        $this->testVote(UserVoter::IS_CONNECTED, $user, $user, Voter::ACCESS_GRANTED);
    }

    public function testShouldGiveAccessToRequestUser(): void
    {
        $user = UserFactory::createUser();

        $this->testVote(UserVoter::IS_REQUEST_USER, $user, $user, Voter::ACCESS_GRANTED);
    }

    public function testShouldDenyAccessToNotRequestUser(): void
    {
        $user = UserFactory::createUser();
        $otherUser = UserFactory::createUser();

        $this->testVote(UserVoter::IS_REQUEST_USER, $otherUser, $user, Voter::ACCESS_DENIED);
    }

    private function banUser(User $user): void
    {
        $sanction = new ModerationSanction($user, new \DateTime());
        $sanction->setModerationAction(ModerationSanctionEnum::BAN_USER);
        $sanction->setReason(ModerationSanctionEnum::MULTI_ACCOUNT);
        $sanction->setEndDate(new \DateTime('+1 day'));
        $user->addModerationSanction($sanction);
    }

    private function testVote(
        string $attribute,
        User $editedUser,
        User $user,
        $expectedVote
    ) {
        $token = new UsernamePasswordToken(
            $user,
            'credentials',
            []
        );

        self::assertSame(
            $expectedVote,
            $this->voter->vote($token, $editedUser, [$attribute])
        );
    }
}
