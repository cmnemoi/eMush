<?php

namespace Mush\Player\Voter;

use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Mush\User\Service\UserServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlayerVoter extends Voter
{
    public const PLAYER_VIEW = 'player_view';
    public const PLAYER_CREATE = 'player_create';
    public const PLAYER_END = 'player_end';
    public const PLAYER_LIKE = 'player_like';

    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::PLAYER_VIEW, self::PLAYER_CREATE, self::PLAYER_END, self::PLAYER_LIKE])) {
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
            case self::PLAYER_LIKE:
                return $this->canPlayerLike($user, $subject);
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
        return $player !== null && $user === $player->getPlayerInfo()->getUser();
    }

    private function canPlayerLike(User $user, ?Player $player): bool
    {
        $userCurrentPlayer = $this->userService->findUserCurrentPlayer($user);

        $playerToLikeExists = null !== $player;
        $playerToLikeIsNotUserPlayer = $playerToLikeExists && $player->getPlayerInfo()->getUser() !== $user;
        $userPlayerIsDead = !$userCurrentPlayer->isAlive();
        // $userHasAlreadyLikedPlayer = $playerToLikeExists && $player->hasLikeFromUser($user);

        return $playerToLikeExists && $playerToLikeIsNotUserPlayer && $userPlayerIsDead;
    }
}
