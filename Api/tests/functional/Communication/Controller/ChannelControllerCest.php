<?php

declare(strict_types=1);

namespace Mush\tests\Functional\Communication\Controller;

use Mush\Communication\Controller\ChannelController;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class ChannelControllerCest extends AbstractFunctionalTest
{
    private ChannelController $channelController;
    private ChannelServiceInterface $channelService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->channelController = $I->grabService(ChannelController::class);
        $this->channelService = $I->grabService(ChannelServiceInterface::class);
    }

    public function testPlayerCannotPostMessageInPublicChannelIfNoTalkie(FunctionalTester $I): void
    {
        // given player has no talkie
        $I->assertFalse($this->player->hasEquipmentByName(ItemEnum::WALKIE_TALKIE));

        // when I check if player can post a message in public channel
        $publicChannel = $this->channelService->getPublicChannel($this->daedalus->getDaedalusInfo());
        $canPostMessage = $this->channelController->playerCanPostMessage($this->player, $publicChannel);

        // then player should not be able to post a message
        $I->assertFalse($canPostMessage);
    }

    public function testPlayerCanPostMessageInAPrivateChannelWithOnlyThemSelf(FunctionalTester $I): void
    {
        // given player has no talkie
        $I->assertFalse($this->player->hasEquipmentByName(ItemEnum::WALKIE_TALKIE));

        // given player creates a private channel
        $channel = $this->channelService->createPrivateChannel($this->player);

        // when I check if player can post a message in this channel
        $canPostMessage = $this->channelController->playerCanPostMessage($this->player, $channel);

        // then player should be able to post a message
        $I->assertTrue($canPostMessage);
    }
}
