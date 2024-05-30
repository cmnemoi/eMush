<?php

namespace Mush\Communication\Voter;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoter extends Voter
{
    public const VIEW = 'view';
    public const CREATE = 'create';

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
        if (!\in_array($attribute, [self::VIEW, self::CREATE], true)) {
            return false;
        }

        if (!$subject instanceof Message) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        $playerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user);

        // User must be logged in and have a current game
        if ($playerInfo === null) {
            return false;
        }

        /** @var Message $subject */
        $channel = $subject->getChannel();

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($channel, $playerInfo);

            case self::CREATE:
                return $this->canCreate($channel, $playerInfo);
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

    private function canCreate(Channel $channel, PlayerInfo $playerInfo): bool
    {
        /** @var Player $player */
        $player = $playerInfo->getPlayer();

        // check for pirated channels
        $piratedPlayer = $this->channelService->getPiratedPlayer($player);

        return $this->channelService->canPlayerCommunicate($player) && $playerInfo->isAlive()
            && (
                $channel->isPublic()
                || $channel->isPlayerParticipant($playerInfo)
                || ($piratedPlayer && $channel->isPlayerParticipant($piratedPlayer->getPlayerInfo()))
            );
    }
}
