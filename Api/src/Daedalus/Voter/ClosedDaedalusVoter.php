<?php

declare(strict_types=1);

namespace Mush\Daedalus\Voter;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, ClosedDaedalus>
 */
final class ClosedDaedalusVoter extends Voter
{
    public const string DAEDALUS_IS_FINISHED = 'DAEDALUS_IS_FINISHED';

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if ($attribute !== self::DAEDALUS_IS_FINISHED) {
            return false;
        }

        return $subject instanceof ClosedDaedalus;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var ClosedDaedalus $daedalus */
        $daedalus = $subject;

        return $daedalus->getDaedalusInfo()->isDaedalusFinished();
    }
}
