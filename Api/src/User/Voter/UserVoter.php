<?php

namespace Mush\User\Voter;

use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const USER_IN_GAME = 'user_in_game';

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::USER_IN_GAME])) {
            return false;
        }

        if (null !== $subject) {
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
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function isPlayerInGame(User $user): bool
    {
        return null !== $user->getCurrentGame();
    }
}
