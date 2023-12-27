<?php

namespace Mush\MetaGame\Voter;

use Mush\MetaGame\Entity\News;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NewsVoter extends Voter
{
    public const NEWS_IS_PUBLISHED = 'NEWS_IS_PUBLISHED';

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if ($attribute !== self::NEWS_IS_PUBLISHED) {
            return false;
        }

        return $subject instanceof News;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var News $news */
        $news = $subject;

        return $news->getIsPublished();
    }
}
