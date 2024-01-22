<?php

namespace Mush\Exploration\Voter;

use Mush\Exploration\Entity\ClosedExploration;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\User;
use Mush\User\Service\UserServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClosedExplorationVoter extends Voter
{
    public const DAEDALUS_IS_FINISHED = 'DAEDALUS_IS_FINISHED';
    public const IS_AN_EXPLORATOR = 'IS_AN_EXPLORATOR';
    public const IS_IN_DAEDALUS_AND_EXPLORATION_IS_FINISHED = 'IS_IN_DAEDALUS_AND_EXPLORATION_IS_FINISHED';

    private PlayerServiceInterface $playerService;
    private UserServiceInterface $userService;

    public function __construct(
        PlayerServiceInterface $playerService,
        UserServiceInterface $userService
    ) {
        $this->playerService = $playerService;
        $this->userService = $userService;
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

        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $userPlayer = $this->playerService->findUserCurrentGame($user);
        $userClosedPlayers = $this->userService->findUserClosedPlayers($user);

        switch ($attribute) {
            case self::DAEDALUS_IS_FINISHED:
                return $closedExploration->getDaedalusInfo()->isDaedalusFinished();
            case self::IS_AN_EXPLORATOR:
                return $closedExploration->getClosedExplorators()->exists(
                    fn ($key, ClosedPlayer $closedPlayer) => $userClosedPlayers->contains($closedPlayer)
                );
            case self::IS_IN_DAEDALUS_AND_EXPLORATION_IS_FINISHED:
                return $userPlayer?->getDaedalus() === $closedExploration->getDaedalusInfo()->getDaedalus()
                    && $closedExploration->isExplorationFinished();
        }

        throw new \LogicException('This code should not be reached!');
    }
}
