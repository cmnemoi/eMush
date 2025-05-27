<?php

namespace Mush\Communication\Voter;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Channel>
 */
final class ChannelVoter extends Voter
{
    public const string VIEW = 'view';
    public const string POST = 'post';

    private ChannelServiceInterface $channelService;
    private MessageServiceInterface $messageService;
    private PlayerInfoRepositoryInterface $playerInfoRepository;

    public function __construct(
        ChannelServiceInterface $channelService,
        MessageServiceInterface $messageService,
        PlayerInfoRepositoryInterface $playerInfoRepository
    ) {
        $this->channelService = $channelService;
        $this->messageService = $messageService;
        $this->playerInfoRepository = $playerInfoRepository;
    }

    public function playerCanPostMessage(Player $player, Channel $channel): bool
    {
        // all Mush players can post in mush channel, whatever the conditions
        if ($channel->isMushChannel() && $player->isMush()) {
            return true;
        }

        $cannotPostInPrivateChannel = !$this->messageService->canPlayerPostMessage($player, $channel)
        || !$this->channelService->canPlayerWhisperInChannel($channel, $player);

        $cannotPostInPublicChannel = !$this->messageService->canPlayerPostMessage($player, $channel)
        || !$this->channelService->canPlayerCommunicate($player);

        if ($channel->isPrivate() && $cannotPostInPrivateChannel) {
            return false;
        }

        if ($channel->isPublicOrFavorites() && $cannotPostInPublicChannel) {
            return false;
        }

        return true;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!\in_array($attribute, [self::VIEW, self::POST], true)) {
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
        $playerInfo = $this->playerInfoRepository->getCurrentPlayerInfoForUserOrNull($user);
        $player = $playerInfo?->getPlayer();

        if (!$player) {
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Channel $channel */
        $channel = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $playerInfo && $this->canView($channel, $playerInfo);

            case self::POST:
                return $this->canPost($channel, $player);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Channel $channel, PlayerInfo $playerInfo): bool
    {
        /** @var Player $player */
        $player = $playerInfo->getPlayer();

        // check for pirated channels
        $piratedPlayer = $this->channelService->getPiratedPlayer($player);

        return $channel->isPublicOrFavorites() || $channel->isPlayerParticipant($playerInfo)
            || ($piratedPlayer && $channel->isPlayerParticipant($piratedPlayer->getPlayerInfo()));
    }

    private function canPost(Channel $channel, Player $player): bool
    {
        return $this->playerCanPostMessage($player, $channel)
            && $channel->getDaedalusInfo()->getDaedalus() === $player->getDaedalus();
    }
}
