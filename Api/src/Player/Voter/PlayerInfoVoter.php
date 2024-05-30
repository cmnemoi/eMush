<?php

declare(strict_types=1);

namespace Mush\Player\Voter;

use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class PlayerInfoVoter extends Voter
{
    public const string PLAYER_INFO_VIEW = 'view';

    public function __construct(private PlayerInfoRepositoryInterface $playerInfoRepository) {}

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!\in_array($attribute, [self::PLAYER_INFO_VIEW], true)) {
            return false;
        }

        return $subject instanceof PlayerInfo;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // if the user is a moderator, they can't see a player in their own Daedalus
        switch ($attribute) {
            case self::PLAYER_INFO_VIEW:
                return $this->canModeratorViewPlayer($subject, $user);

            default:
                return false;
        }
    }

    private function canModeratorViewPlayer(PlayerInfo $playerInfo, User $moderator): bool
    {
        $moderatorCurrentPlayerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($moderator);

        return $playerInfo->getDaedalusName() !== $moderatorCurrentPlayerInfo?->getDaedalusName();
    }
}
