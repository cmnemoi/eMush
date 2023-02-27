<?php

namespace Mush\Player\Voter;

use Mush\Player\Entity\ClosedPlayer;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClosedPlayerVoter extends Voter
{
    public const DAEDALUS_IS_FINISHED = 'DAEDALUS_IS_FINISHED';
    public const PLAYER_HAS_NOT_ALREADY_LIKED = 'PLAYER_HAS_NOT_ALREADY_LIKED';

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::DAEDALUS_IS_FINISHED, self::PLAYER_HAS_NOT_ALREADY_LIKED])) {
            return false;
        }

        // only vote on `Player` objects
        if (null !== $subject && !$subject instanceof ClosedPlayer) {
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

        /** @var ClosedPlayer $player */
        $player = $subject;

        switch ($attribute) {
            case self::DAEDALUS_IS_FINISHED:
                return $this->daedalusIsFinished($player);
            case self::PLAYER_HAS_NOT_ALREADY_LIKED:
                return $this->playerHasNotAlreadyLiked($player);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function daedalusIsFinished(ClosedPlayer $player): bool
    {
        return $player->getClosedDaedalus()->getDaedalusInfo()->isDaedalusFinished();
    }

    private function playerHasNotAlreadyLiked(ClosedPlayer $player): bool
    {
        return true; // TODO
    }
}
