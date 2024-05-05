<?php

declare(strict_types=1);

namespace Mush\MetaGame\Voter;

use Mush\MetaGame\Entity\ModerationSanction;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ModerationSanctionVoter extends Voter
{   
    public const string SANCTION_VIEW = 'SANCTION_VIEW';

    protected function supports(string $attribute, $subject): bool
    {   
        // if the attribute isn't one we support, return false
        if ($attribute !== self::SANCTION_VIEW) {
            return false;
        }

        return $subject instanceof ModerationSanction;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var ModerationSanction $moderationSanction */
        $moderationSanction = $subject;

        $user = $token->getUser();
        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return $this->canView($moderationSanction, $user);
    }

    private function canView(ModerationSanction $moderationSanction, User $user): bool
    {
        $isUserSanction = $moderationSanction->getUser()->getUserId() === $user->getUserId();
        $userIsModerator = $user->isModerator();

        return $isUserSanction || $userIsModerator;
    }
}