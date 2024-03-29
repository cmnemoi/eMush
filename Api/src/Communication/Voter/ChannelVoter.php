<?php

namespace Mush\Communication\Voter;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ChannelVoter extends Voter
{
    public const VIEW = 'view';

    private ChannelServiceInterface $channelService;
    private PlayerInfoRepository $playerInfoRepository;

    public function __construct(
        ChannelServiceInterface $channelService,
        PlayerInfoRepository $playerInfoRepository
    ) {
        $this->channelService = $channelService;
        $this->playerInfoRepository = $playerInfoRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if ($attribute != self::VIEW) {
            return false;
        }

        if (!$subject instanceof Channel) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Channel $channel */
        $channel = $subject;

        switch ($attribute) {
            case self::VIEW:
                // @TODO : in the future, do not allow moderators to see channels of their own games
                return $user->isModerator() || $playerInfo && $this->canView($channel, $playerInfo);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Channel $channel, PlayerInfo $playerInfo): bool
    {
        /** @var Player $player */
        $player = $playerInfo->getPlayer();

        // check for pirated channels
        $piratedPlayer = $this->channelService->getPiratedPlayer($player);

        return $channel->isPublic() || $channel->isPlayerParticipant($playerInfo)
            || ($piratedPlayer && $channel->isPlayerParticipant($piratedPlayer->getPlayerInfo()));
    }
}
