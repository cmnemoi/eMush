<?php

declare(strict_types=1);

namespace Mush\Player\Voter;

use Mush\Player\Entity\ClosedPlayer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, ClosedPlayer>
 */
final class ClosedPlayerVoter extends Voter
{
    public const DAEDALUS_IS_FINISHED = 'DAEDALUS_IS_FINISHED';

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if ($attribute !== self::DAEDALUS_IS_FINISHED) {
            return false;
        }

        return $subject instanceof ClosedPlayer;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var ClosedPlayer $player */
        $player = $subject;

        $daedalus = $player->getClosedDaedalus();

        return $daedalus->getDaedalusInfo()->isDaedalusFinished();
    }
}
