<?php

declare(strict_types=1);

namespace Mush\tests\unit\MetaGame\Voter;

use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\MetaGame\Voter\ModerationSanctionVoter;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

final class ModerationSanctionVoterTest extends TestCase
{
    private ModerationSanctionVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new ModerationSanctionVoter();
    }

    public function testShouldLetUserAccessToTheirOwnSanctions(): void
    {
        // given a user
        $user = UserFactory::createUser();

        // given this user is banned
        $sanction = $this->banUser($user);

        // when the user tries to access their own sanction
        $result = $this->voter->vote(
            $this->getToken($user), 
            $sanction, 
            [ModerationSanctionVoter::SANCTION_VIEW]
        );

        // then the user should be able to access their own sanction
        self::assertEquals(ModerationSanctionVoter::ACCESS_GRANTED, $result);
    }

    public function testShouldLetModeratorAccessToOtherUsersSanctions(): void
    {
        // given a user
        $user = UserFactory::createUser();

        // given this user is banned
        $sanction = $this->banUser($user);

        // given a moderator
        $moderator = UserFactory::createModerator();

        // when the moderator tries to access the user's sanction
        $result = $this->voter->vote(
            $this->getToken($moderator), 
            $sanction, 
            [ModerationSanctionVoter::SANCTION_VIEW]
        );

        // then the moderator should be able to access the user's sanction
        self::assertEquals(ModerationSanctionVoter::ACCESS_GRANTED, $result);
    }

    public function testShouldNotLetUserAccessToOtherUsersSanctions(): void
    {
        // given a user
        $user = UserFactory::createUser();

        // given this user is banned
        $sanction = $this->banUser($user);

        // given another user
        $anotherUser = UserFactory::createUser();

        // when the user tries to access the other user's sanction
        $result = $this->voter->vote(
            $this->getToken($anotherUser), 
            $sanction, 
            [ModerationSanctionVoter::SANCTION_VIEW]
        );

        // then the user should not be able to access the other user's sanction
        self::assertEquals(ModerationSanctionVoter::ACCESS_DENIED, $result);
    }

    private function banUser(User $user): ModerationSanction
    {
        $sanction = new ModerationSanction($user, new \DateTime());
        $sanction
            ->setModerationAction(ModerationSanctionEnum::BAN_USER)
            ->setReason(ModerationSanctionEnum::MULTI_ACCOUNT)
            ->setEndDate(new \DateTime('+1 day'));

        $user->addModerationSanction($sanction);

        return $sanction;
    }

    private function getToken(User $user): UsernamePasswordToken 
    {
        return new UsernamePasswordToken($user, 'credentials', []);
    }

    
}