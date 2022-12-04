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
        if ($candidate instanceof Player) {
            if (!$candidate->isAlive()) {
                return false;
            }
            $channels = $this->channelService->getPlayerChannels($candidate, true);
            if ($channels->count() < $candidate->getPlayerInfo()->getCharacterConfig()->getMaxNumberPrivateChannel()) {
                return true;
            }
        }

        return false;
    }
}
