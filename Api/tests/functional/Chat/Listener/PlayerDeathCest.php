<?php

namespace Mush\Tests\functional\Chat\Listener;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Daedalus\Entity\Neron;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerDeathCest extends AbstractFunctionalTest
{
    private PlayerServiceInterface $playerService;
    private ChannelServiceInterface $channelService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->channelService = $I->grabService(ChannelServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
    }

    public function testDispatchPlayerDeath(FunctionalTester $I)
    {
        $neron = $this->daedalus->getNeron();
        // given a private channel is created
        $privateChannel = $this->channelService->createPrivateChannel($this->player);

        // when a player is killed
        $this->playerService->killPlayer(
            player: $this->player,
            endReason: EndCauseEnum::ROCKETED,
            time: new \DateTime(),
        );

        // then i should see player death message from neron in the public channel
        $message1 = $I->grabEntityFromRepository(Message::class, [
            'message' => NeronMessageEnum::PLAYER_DEATH,
            'neron' => $neron,
            'channel' => $this->publicChannel,
        ]);
        $I->assertInstanceOf(Message::class, $message1);

        // then i should see player leave because of death from the system in the private channel
        $message2 = $I->grabEntityFromRepository(Message::class, [
            'message' => NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH,
            'channel' => $privateChannel,
        ]);
        $I->assertInstanceOf(Message::class, $message2);

        // then there should be no player in the private channel
        $I->assertCount(0, $privateChannel->getParticipants());
    }

    public function testNeronShouldNotAnnounceDeathIfBIOSOptionIsOff(FunctionalTester $I)
    {
        $neron = $this->daedalus->getNeron();

        // given death announcements are toggled off
        $this->daedalus->getNeron()->toggleDeathAnnouncements();

        // when a player is killed
        $this->playerService->killPlayer(
            player: $this->player,
            endReason: EndCauseEnum::ROCKETED,
            time: new \DateTime(),
        );

        // then i should not see a player death message from neron in the public channel
        $I->cantSeeInRepository(Message::class, [
            'message' => NeronMessageEnum::PLAYER_DEATH,
            'neron' => $neron,
            'channel' => $this->publicChannel,
        ]);
    }
}
