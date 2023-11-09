<?php

namespace Mush\Exploration\Voter;

use Mush\Exploration\Entity\ClosedExploration;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClosedExplorationVoter extends Voter
{   
    public const DAEDALUS_IS_FINISHED = 'DAEDALUS_IS_FINISHED';
    public const IS_AN_EXPLORATOR = 'IS_AN_EXPLORATOR';
    public const IS_IN_DAEDALUS_AND_EXPLORATION_IS_FINISHED = 'IS_IN_DAEDALUS_AND_EXPLORATION_IS_FINISHED';
    
    private PlayerServiceInterface $playerService;

    public function __construct(
        PlayerServiceInterface $playerService
    ) {
        $this->playerService = $playerService;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (
            !in_array($attribute, [
            self::DAEDALUS_IS_FINISHED,
            self::IS_AN_EXPLORATOR,
            self::IS_IN_DAEDALUS_AND_EXPLORATION_IS_FINISHED,
        ])) {
            return false;
        }

        return $subject instanceof ClosedExploration;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var ClosedExploration $closedExploration */
        $closedExploration = $subject;

        if (!$token->getUser()) {
            return false;
        }

        $userPlayer = $this->playerService->findUserCurrentGame($token->getUser());

        switch ($attribute) {
            case self::DAEDALUS_IS_FINISHED:
                return $closedExploration->getDaedalusInfo()->isDaedalusFinished();
            case self::IS_AN_EXPLORATOR: 
                return in_array($userPlayer?->getName(), $closedExploration->getExploratorNames());
            case self::IS_IN_DAEDALUS_AND_EXPLORATION_IS_FINISHED:
                return $closedExploration->isExplorationFinished() && $userPlayer?->getDaedalus()->getDaedalusInfo() === $closedExploration->getDaedalusInfo();
        }
    }
}
