<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Service;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Services\CreateNeronChannelForPlayerService;
use Mush\Player\Entity\Player;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CreateNeronChannelForPlayerServiceCest extends AbstractFunctionalTest
{
    private CreateNeronChannelForPlayerService $createNeronChannelForPlayer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->createNeronChannelForPlayer = $I->grabService(CreateNeronChannelForPlayerService::class);
    }

    public function shouldCreateNeronChannel(FunctionalTester $I): void
    {
        // when I create a Neron channel
        $this->createNeronChannelForPlayer->execute($this->player);

        // then I should have a Neron channel in repository
        $I->seeInRepository(Channel::class, [
            'scope' => ChannelScopeEnum::NERON,
            'daedalusInfo' => $this->player->getDaedalus()->getDaedalusInfo()->getId(),
        ]);
    }

    public function playerShouldBeInCreatedChannel(FunctionalTester $I): void
    {
        // when I create a Neron channel
        $this->createNeronChannelForPlayer->execute($this->player);

        // then player should be in created channel
        $channel = $I->grabEntityFromRepository(Channel::class, [
            'scope' => ChannelScopeEnum::NERON,
            'daedalusInfo' => $this->player->getDaedalus()->getDaedalusInfo()->getId(),
        ]);
        $I->assertTrue(
            $channel->isPlayerParticipant($this->player->getPlayerInfo()),
            'Player should be in created channel'
        );
    }
}
