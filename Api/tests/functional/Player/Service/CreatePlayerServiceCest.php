<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Config\CharacterConfigCollection;
use Mush\Player\Service\PlayerService;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;
use Mush\User\Factory\UserFactory;

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

        $beginnerStatusConfig = new StatusConfig();
        $beginnerStatusConfig
            ->setStatusName(PlayerStatusEnum::BEGINNER)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($beginnerStatusConfig);

        $rebelBaseContactDurationStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['name' => DaedalusStatusEnum::REBEL_BASE_CONTACT_DURATION . '_' . GameConfigEnum::DEFAULT]);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);

        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $daedalusConfig->setStartingApprentrons([
            'apprentron_technician' => 14,
        ]);

        $daedalusConfig->setPlayerCount(2);

        $equipmentConfigs = new ArrayCollection();
        $equipmentConfigs->add($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'apprentron_technician_default']));
        $equipmentConfigs->add($I->grabEntityFromRepository(ItemConfig::class, ['name' => 'mush_sample_default']));

        $hunterConfig = $I->grabEntityFromRepository(HunterConfig::class, ['name' => HunterEnum::TRANSPORT . '_' . GameConfigEnum::DEFAULT]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'statusConfigs' => new ArrayCollection([$mushStatusConfig, $rebelBaseContactDurationStatusConfig, $beginnerStatusConfig]),
            'daedalusConfig' => $daedalusConfig,
            'equipmentsConfig' => $equipmentConfigs,
            'hunterConfigs' => new ArrayCollection([$hunterConfig]),
        ]);

        /** @var CharacterConfig $gioeleCharacterConfig */
        $gioeleCharacterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::GIOELE]);

        /** @var CharacterConfig $finolaCharacterConfig */
        $finolaCharacterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::FINOLA]);

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

        /** @var Place $storage */
        $storage = $I->have(Place::class, ['name' => RoomEnum::FRONT_STORAGE, 'daedalus' => $daedalus]);

        $daedalus->addPlace($room);
        $daedalus->addPlace($storage);

        /** @var User $user */
        $user = $I->have(User::class);

        $charactersConfig = new CharacterConfigCollection();
        $charactersConfig->add($gioeleCharacterConfig);
        $charactersConfig->add($finolaCharacterConfig);

        $gameConfig->setCharactersConfig($charactersConfig);
        $daedalus->setAvailableCharacters($charactersConfig);
        $daedalusInfo->setGameConfig($gameConfig);
        $I->haveInRepository($daedalus);

        $playerGioele = $this->playerService->createPlayer($daedalus, $user, CharacterEnum::GIOELE);

        $I->assertEquals($gioeleCharacterConfig, $playerGioele->getPlayerInfo()->getCharacterConfig());
        $I->assertEquals($gioeleCharacterConfig->getInitActionPoint(), $playerGioele->getActionPoint());

        $user = UserFactory::createUser();
        $I->haveInRepository($user);
        $playerFinola = $this->playerService->createPlayer($daedalus, $user, CharacterEnum::FINOLA);

        $I->assertEquals($finolaCharacterConfig, $playerFinola->getPlayerInfo()->getCharacterConfig());
        $I->assertEquals($finolaCharacterConfig->getInitActionPoint(), $playerFinola->getActionPoint());

        $I->assertTrue($playerFinola->isMush());
        $I->assertTrue($playerGioele->isMush());
        $I->assertNotNull($daedalus->getFilledAt());
    }
}
