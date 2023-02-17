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
    public const POST = 'post';
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
        if (!in_array($attribute, [self::VIEW])) {
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

        // User must be logged in and have a current game
        if ($playerInfo === null) {
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Channel $channel */
        $channel = $subject;

        switch ($attribute) {
            case self::POST:
                return $this->canPost($playerInfo);
            case self::VIEW:
                return $this->canView($channel, $playerInfo);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canPost(PlayerInfo $playerInfo): bool
    {
        /** @var Player $player */
        $player = $playerInfo->getPlayer();

        return $this->channelService->canPlayerCommunicate($player);
    }

    private function canView(Channel $channel, PlayerInfo $playerInfo): bool
    {
        /** @var Player $player */
        $player = $playerInfo->getPlayer();

        // check for pirated channels
        $piratedPlayer = $this->channelService->getPiratedPlayer($player);

        $playerCanCommunicate = $this->channelService->canPlayerCommunicate($player);

        return $playerCanCommunicate && (
            $channel->isPublic() || $channel->isPlayerParticipant($playerInfo) ||
                ($piratedPlayer && $channel->isPlayerParticipant($piratedPlayer->getPlayerInfo()))
        );
    }
}
