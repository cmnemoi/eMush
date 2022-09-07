<?php

namespace Mush\Communication\Voter;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoter extends Voter
{
    public const VIEW = 'view';
    public const CREATE = 'create';

    private ChannelServiceInterface $channelService;

    public function __construct(ChannelServiceInterface $channelService)
    {
        $this->channelService = $channelService;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::CREATE])) {
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

        // User must be logged in and have a current game
        if (!$user instanceof User || !($player = $user->getCurrentGame())) {
            return false;
        }

        /** @var Message $subject */
        $channel = $subject->getChannel();

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($channel, $player);
            case self::CREATE:
                return $this->canCreate($channel, $player);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Channel $channel, Player $player): bool
    {
        // check for pirated channels
        $piratedPlayer = $this->channelService->getPiratedPlayer($player);

        return $channel->isPublic() || $channel->isPlayerParticipant($player) ||
            ($piratedPlayer && $channel->isPlayerParticipant($piratedPlayer));
    }

    private function canCreate(Channel $channel, Player $player): bool
    {
        // check for pirated channels
        $piratedPlayer = $this->channelService->getPiratedPlayer($player);

        return $this->channelService->canPlayerCommunicate($player) && $player->isAlive() &&
            ($channel->isPublic() ||
                $channel->isPlayerParticipant($player) ||
                ($piratedPlayer && $channel->isPlayerParticipant($piratedPlayer))
            );
    }
}
