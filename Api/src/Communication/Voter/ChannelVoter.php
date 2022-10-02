<?php

namespace Mush\Communication\Voter;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ChannelVoter extends Voter
{
    public const VIEW = 'view';

    private ChannelServiceInterface $channelService;

    public function __construct(ChannelServiceInterface $channelService)
    {
        $this->channelService = $channelService;
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
        $user = $token->getUser();

        // User must be logged in and have a current game
        if (!$user instanceof User || !($player = $user->getCurrentGame())) {
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Channel $channel */
        $channel = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($channel, $player);
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
}
