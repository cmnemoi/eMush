<?php

namespace Mush\Tests\functional\Daedalus\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class DaedalusDestroyedOnEndDayCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testDestroyDaedalus(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(PlayerStatusEnum::MUSH)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => 'default']);
        $hunterConfigs = $I->grabEntitiesFromRepository(HunterConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
            'statusConfigs' => new ArrayCollection([$statusConfig]),
            'hunterConfigs' => new ArrayCollection($hunterConfigs),
        ]);

        /** @var User $user */
        $user = $I->have(User::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, [
            'cycle' => 8,
            'day' => 10,
            'filledAt' => new \DateTime(),
            'cycleStartedAt' => new \DateTime(),
        ]);
        $daedalus->setDaedalusVariables($daedalusConfig);

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
        $space = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::SPACE]);

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
        $player->setPlayerVariables($characterConfig);

        $I->haveInRepository($player);

        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $event = new DaedalusCycleEvent($daedalus, [], new \DateTime());
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        $I->assertEquals(GameStatusEnum::FINISHED, $playerInfo->getGameStatus());
        $I->assertEquals(GameStatusEnum::FINISHED, $daedalus->getGameStatus());
        $I->assertCount(0, $daedalus->getPlayers()->getPlayerAlive());
        $I->seeInRepository(ClosedDaedalus::class);
        $I->assertEquals(1, $daedalus->getCycle());
        $I->assertEquals(11, $daedalus->getDay());
    }
}
