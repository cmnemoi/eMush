<?php

namespace Mush\User\Voter;

use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserVoter extends Voter
{
    public const USER_IN_GAME = 'user_in_game';
    public const EDIT_USER_ROLE = 'EDIT_USER_ROLE';

    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::USER_IN_GAME, self::EDIT_USER_ROLE])) {
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
                return $this->isPlayerInGame($user);
            case self::EDIT_USER_ROLE:
                return $this->canEditUserRole($subject, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function isPlayerInGame(User $user): bool
    {
        return null !== $user->getPlayerInfo();
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
