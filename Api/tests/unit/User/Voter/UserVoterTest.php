<?php

namespace Mush\Tests\unit\User\Voter;

use Mockery;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;
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
