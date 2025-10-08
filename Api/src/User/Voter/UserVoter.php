<?php

namespace Mush\User\Voter;

use Mush\User\Entity\User;
use Mush\User\Repository\BannedIpRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @extends Voter<string, User>
 */
final class UserVoter extends Voter
{
    public const string EDIT_USER_ROLE = 'EDIT_USER_ROLE';
    public const string HAS_ACCEPTED_RULES = 'HAS_ACCEPTED_RULES';
    public const string IS_CONNECTED = 'IS_CONNECTED';
    public const string IS_NOT_BANNED = 'IS_NOT_BANNED';
    public const string NOT_IN_GAME = 'NOT_IN_GAME';
    public const string USER_IN_GAME = 'user_in_game';
    public const string IS_REQUEST_USER = 'IS_REQUEST_USER';

    public function __construct(
        private BannedIpRepositoryInterface $bannedIpRepository,
        private RoleHierarchyInterface $roleHierarchy
    ) {}

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!\in_array($attribute, [
            self::EDIT_USER_ROLE,
            self::HAS_ACCEPTED_RULES,
            self::IS_CONNECTED,
            self::IS_NOT_BANNED,
            self::NOT_IN_GAME,
            self::USER_IN_GAME,
            self::IS_REQUEST_USER,
        ], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        $ipBanned = $this->bannedIpRepository->hasAny($user->getHashedIps());

        return match ($attribute) {
            self::USER_IN_GAME => $user->isInGame(),
            self::EDIT_USER_ROLE => $this->canEditUserRole($subject, $token),
            self::NOT_IN_GAME => !$user->isInGame(),
            self::HAS_ACCEPTED_RULES => $user->hasAcceptedRules(),
            self::IS_NOT_BANNED => !$user->isBanned() && !$ipBanned,
            self::IS_CONNECTED => $user instanceof User,
            self::IS_REQUEST_USER => $user->getUserId() === $subject->getUserId(),
            default => throw new \LogicException('This code should not be reached!'),
        };

        throw new \LogicException('This code should not be reached!');
    }

    private function canEditUserRole(User $editedUser, TokenInterface $token): bool
    {
        $roles = $this->roleHierarchy->getReachableRoleNames($token->getRoleNames());
        foreach ($editedUser->getRoles() as $editedUserRole) {
            if (!\in_array($editedUserRole, $roles, true)) {
                return false;
            }
        }

        return true;
    }
}
