<?php

declare(strict_types=1);

namespace Mush\Chat\Voter;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Message;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Message>
 */
final class MessageVoter extends Voter
{
    public const VIEW = 'view';
    public const CREATE = 'create';
    public const FAVORITE = 'favorite';

    private ChannelServiceInterface $channelService;
    private PlayerInfoRepositoryInterface $playerInfoRepository;

    public function __construct(
        ChannelServiceInterface $channelService,
        PlayerInfoRepositoryInterface $playerInfoRepository
    ) {
        $this->channelService = $channelService;
        $this->playerInfoRepository = $playerInfoRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!\in_array($attribute, [self::VIEW, self::CREATE, self::FAVORITE], true)) {
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

        return match ($attribute) {
            self::VIEW => $this->canView($channel, $playerInfo),
            self::CREATE => $this->canCreate($channel, $playerInfo),
            self::FAVORITE => $this->canFavorite($channel, $playerInfo),
        };
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

    private function canFavorite(Channel $channel, PlayerInfo $playerInfo): bool
    {
        /** @var Player $player */
        $player = $playerInfo->getPlayer();

        return $this->canView($channel, $playerInfo)
            && $channel->isPublic()
            && $channel->getDaedalusInfo()->getDaedalus() === $player->getDaedalus();
    }
}
