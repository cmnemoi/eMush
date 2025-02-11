<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

final class LastPlayerKilledCest
{
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I): void
    {
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
    }

    public function testLastPlayerKilled(FunctionalTester $I)
    {
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['daedalusConfig' => $daedalusConfig, 'localizationConfig' => $localizationConfig]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 5,
            'day' => 10,
            'filledAt' => new \DateTime(),
            'cycleStartedAt' => new \DateTime(),
        ]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(
            Player::class,
            [
                'daedalus' => $daedalus,
                'place' => $room,
                'user' => $user,
            ]
        );
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $this->playerService->killPlayer(
            player: $player,
            endReason: EndCauseEnum::mapEndCause([ActionEnum::HIT->value]),
            time: new \DateTime(),
        );

        $I->assertEquals(GameStatusEnum::FINISHED, $playerInfo->getGameStatus());
        $I->assertEquals(GameStatusEnum::FINISHED, $daedalus->getGameStatus());
        $I->assertCount(0, $daedalus->getPlayers()->getPlayerAlive());
        $I->seeInRepository(ClosedDaedalus::class);
    }
}
