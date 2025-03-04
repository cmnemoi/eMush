<?php

namespace Mush\Chat\Specification;

use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Player\Entity\Player;

final class CanCreateChannel implements SpecificationInterface
{
    private ChannelServiceInterface $channelService;

    public function __construct(ChannelServiceInterface $channelService)
    {
        $this->channelService = $channelService;
    }

    public function isSatisfied($candidate): bool
    {
        if (!$candidate instanceof Player) {
            return false;
        }

        if ($candidate->isDead()) {
            return false;
        }

        $channels = $this->channelService->getPlayerChannels($candidate, true);

        return $channels->count() < $candidate->getMaxPrivateChannels();
    }
}
