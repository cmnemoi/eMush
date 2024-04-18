<?php

declare(strict_types=1);

namespace Mush\tests\Functional\Communication\Controller;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Voter\ChannelVoter;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ChannelControllerCest extends AbstractFunctionalTest
{
    private ChannelVoter $channelVoter;
    private ChannelServiceInterface $channelService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->channelVoter = $I->grabService(ChannelVoter::class);
        $this->channelService = $I->grabService(ChannelServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testPlayerCannotPostMessageInPublicChannelIfNoTalkie(FunctionalTester $I): void
    {
        // given player has no talkie
        $I->assertFalse($this->player->hasEquipmentByName(ItemEnum::WALKIE_TALKIE));

        // when I check if player can post a message in public channel
        $publicChannel = $this->channelService->getPublicChannel($this->daedalus->getDaedalusInfo());
        $canPostMessage = $this->channelVoter->playerCanPostMessage($this->player, $publicChannel);

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
        $canPostMessage = $this->channelVoter->playerCanPostMessage($this->player, $channel);

        // then player should be able to post a message
        $I->assertTrue($canPostMessage);
    }

    public function testPlayerCanPostMessageInAPrivateChannelWithAnotherCrewmate(FunctionalTester $I): void
    {
        // given player has a talkie
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ITRACKIE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );

        // given player creates a private channel
        $channel = $this->channelService->createPrivateChannel($this->player);

        // given another player has a talkie
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ITRACKIE,
            equipmentHolder: $this->player2,
            reasons: [],
            time: new \DateTime()
        );

        // given this player is in another room
        $this->player2->changePlace($this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus));

        // given this other player is in the channel
        $this->channelService->invitePlayer($this->player2, $channel);

        // when I check if player can post a message in this channel
        $canPostMessage = $this->channelVoter->playerCanPostMessage($this->player, $channel);

        // then player should be able to post a message
        $I->assertTrue($canPostMessage);
    }
}
