<?php

namespace Mush\User\Voter;

use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserVoter extends Voter
{
    public const string EDIT_USER_ROLE = 'EDIT_USER_ROLE';
    public const string HAS_ACCEPTED_RULES = 'HAS_ACCEPTED_RULES';
    public const string NOT_IN_GAME = 'NOT_IN_GAME';
    public const string IS_NOT_BANNED = 'IS_NOT_BANNED';
    public const string IS_CONNECTED = 'IS_CONNECTED';
    public const string USER_IN_GAME = 'user_in_game';

    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::USER_IN_GAME,
            self::EDIT_USER_ROLE,
            self::HAS_ACCEPTED_RULES,
            self::IS_NOT_BANNED,
            self::NOT_IN_GAME,
            self::IS_CONNECTED,
        ])) {
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

        switch ($attribute) {
            case self::USER_IN_GAME:
                return $user->isInGame();
            case self::EDIT_USER_ROLE:
                return $this->canEditUserRole($subject, $token);
            case self::NOT_IN_GAME:
                return !$user->isInGame();
            case self::HAS_ACCEPTED_RULES:
                return $user->hasAcceptedRules();
            case self::IS_NOT_BANNED:
                return !$user->isBanned();
            case self::IS_CONNECTED:
                return $user instanceof User;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEditUserRole(User $editedUser, TokenInterface $token): bool
    {
        $roles = $this->roleHierarchy->getReachableRoleNames($token->getRoleNames());
        foreach ($editedUser->getRoles() as $editedUserRole) {
            if (!in_array($editedUserRole, $roles)) {
                return false;
            }
        }

        return true;
    }
}
