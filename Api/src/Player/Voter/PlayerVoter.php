<?php

namespace Mush\Player\Voter;

use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlayerVoter extends Voter
{
    public const PLAYER_VIEW = 'player_view';
    public const PLAYER_CREATE = 'player_create';
    public const PLAYER_END = 'player_end';
    public const PLAYER_QUARANTINE = 'player_quarantine';

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!\in_array($attribute, [self::PLAYER_VIEW, self::PLAYER_CREATE, self::PLAYER_END, self::PLAYER_QUARANTINE], true)) {
            return false;
        }

        // only vote on `Player` objects
        if (null !== $subject && !$subject instanceof Player) {
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
            case self::PLAYER_VIEW:
                return $this->canViewPlayer($user, $subject);

            case self::PLAYER_CREATE:
                return $this->canCreatePlayer($user);

            case self::PLAYER_END:
                return $this->canPlayerEnd($user, $subject);

            case self::PLAYER_QUARANTINE:
                return $this->canQuarantinePlayer($user, $subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canViewPlayer(User $user, ?Player $player): bool
    {
        return null !== $player && $player->getPlayerInfo()->getUser() === $user;
    }

    private function canCreatePlayer(User $user): bool
    {
        return !$user->isInGame();
    }

    private function canPlayerEnd(User $user, ?Player $player): bool
    {
        $playerExists = $player !== null;
        $playerIsUserPlayer = $playerExists && $player->getPlayerInfo()->getUser() === $user;
        $userIsAdmin = $user->isAdmin();

        return $playerIsUserPlayer || $userIsAdmin;
    }

    private function canQuarantinePlayer(User $user, ?Player $player): bool
    {
        return $player !== null && $player->isAlive() && $user->isAdmin();
    }
}
