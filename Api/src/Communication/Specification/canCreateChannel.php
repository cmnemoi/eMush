<?php

namespace Mush\Communication\Specification;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Player\Entity\Player;

class canCreateChannel implements SpecificationInterface
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
