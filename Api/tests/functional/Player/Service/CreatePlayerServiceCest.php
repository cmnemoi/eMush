<?php

namespace Mush\Tests\functional\Player\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Service\PlayerService;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class CreatePlayerServiceCest
{
    private PlayerService $playerService;

    public function _before(FunctionalTester $I)
    {
        $this->playerService = $I->grabService(PlayerService::class);
    }

    public function createPlayerTest(FunctionalTester $I)
    {
        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        $mushStatusConfig = new ChargeStatusConfig();
        $mushStatusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($mushStatusConfig);

        $alphaMushStatusConfig = new StatusConfig();
        $alphaMushStatusConfig
            ->setStatusName(PlayerStatusEnum::ALPHA_MUSH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($alphaMushStatusConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$mushStatusConfig, $alphaMushStatusConfig]),
        ]);

        /** @var CharacterConfig $gioeleCharacterConfig */
        $gioeleCharacterConfig = $I->have(CharacterConfig::class);

        /** @var $andieCharacterConfig $characterConfig */
        $andieCharacterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($channel);

        $mushChannel = new Channel();
        $mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($mushChannel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        $daedalus->addPlace($room);
        $I->refreshEntities($daedalus);

        /** @var User $user */
        $user = $I->have(User::class);

        $charactersConfig = new ArrayCollection();
        $charactersConfig->add($gioeleCharacterConfig);
        $charactersConfig->add($andieCharacterConfig);

        $gameConfig->setCharactersConfig($charactersConfig);
        $daedalusInfo->setGameConfig($gameConfig);

        $I->expectThrowable(
            \LogicException::class,
            fn () => $this->playerService->createPlayer($daedalus, $user, 'non_existent_player')
        );

        $playerGioele = $this->playerService->createPlayer($daedalus, $user, CharacterEnum::GIOELE);

        $I->assertEquals($gioeleCharacterConfig, $playerGioele->getPlayerInfo()->getCharacterConfig());
        $I->assertEquals($gioeleCharacterConfig->getInitActionPoint(), $playerGioele->getActionPoint());

        $playerAndie = $this->playerService->createPlayer($daedalus, $user, CharacterEnum::ANDIE);

        $I->assertEquals($andieCharacterConfig, $playerAndie->getPlayerInfo()->getCharacterConfig());
        $I->assertEquals($andieCharacterConfig->getInitActionPoint(), $playerAndie->getActionPoint());

        $I->assertTrue($playerAndie->isMush());
        $I->assertTrue($playerGioele->isMush());
        $I->assertNotNull($daedalus->getFilledAt());
    }
}
