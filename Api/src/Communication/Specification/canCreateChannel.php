<?php

namespace Mush\Communication\Specification;

use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;

class canCreateChannel implements SpecificationInterface
{
    private GameConfigServiceInterface $gameConfigService;
    private ChannelServiceInterface $channelService;

    public function __construct(GameConfigServiceInterface $gameConfigService, ChannelServiceInterface $channelService)
    {
        $this->gameConfigService = $gameConfigService;
        $this->channelService = $channelService;
    }

    public function isSatisfied($candidate): bool
    {
        if ($candidate instanceof Player) {
            $channels = $this->channelService->getPlayerChannels($candidate, true);
            if ($channels->count() < $this->gameConfigService->getConfig()->getMaxNumberPrivateChannel()) {
                return true;
            }
        }

        return false;
    }
}
