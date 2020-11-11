<?php

namespace Mush\Player\Voter;

use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CharacterVoter extends Voter
{
    public const PLAYER_VIEW = 'player_view';
    public const PLAYER_CREATE = 'player_create';

    protected function supports(string $attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::PLAYER_VIEW, self::PLAYER_CREATE])) {
            return false;
        }

        // only vote on `Player` objects
        if (null !== $subject && !$subject instanceof Player) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::PLAYER_VIEW:
                return $this->canViewPlayer($user, $subject);
            case self::PLAYER_CREATE:
                return $this->canCreatePlayer($user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canViewPlayer(User $user, ?Player $player): bool
    {
        return null === $player || $player->getUser() === $user;
    }

    private function canCreatePlayer(User $user): bool
    {
        return null === $user->getCurrentGame();
    }
}
