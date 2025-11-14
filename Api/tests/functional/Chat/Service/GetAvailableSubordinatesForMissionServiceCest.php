<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Chat\Service;

use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Chat\Services\GetAvailableSubordinatesForMissionService;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class GetAvailableSubordinatesForMissionServiceCest extends AbstractFunctionalTest
{
    private ChannelServiceInterface $channelService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private GetAvailableSubordinatesForMissionService $getContactablePlayers;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->channelService = $I->grabService(ChannelServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->getContactablePlayers = $I->grabService(GetAvailableSubordinatesForMissionService::class);
    }

    public function shouldReturnPlayersWithFullPrivateChannels(FunctionalTester $I): void
    {
        // given Kuan Ti is full of private channels
        $this->channelService->createPrivateChannel($this->kuanTi);
        $this->channelService->createPrivateChannel($this->kuanTi);
        $this->channelService->createPrivateChannel($this->kuanTi);

        // given KT has a talkie
        $talkie = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::WALKIE_TALKIE,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun has a talkie
        $talkie = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::WALKIE_TALKIE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given chun is on a planet
        $this->chun->changePlace($this->daedalus->getPlanetPlace());

        // when I get contactable players for chun
        $contactablePlayers = $this->getContactablePlayers->execute($this->chun);

        // then I should see Kuan Ti in contactable players
        $I->assertContains($this->kuanTi, $contactablePlayers->toArray());
    }
}
