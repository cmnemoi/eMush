<?php

namespace Mush\User\Voter;

use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * @template TAttribute of string
 * @template TSubject of mixed
 *
 * @template-extends Voter<TAttribute, TSubject>
 */
class UserVoter extends Voter
{
    public const USER_IN_GAME = 'user_in_game';
    public const EDIT_USER_ROLE = 'EDIT_USER_ROLE';

    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::USER_IN_GAME, self::EDIT_USER_ROLE])) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
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
