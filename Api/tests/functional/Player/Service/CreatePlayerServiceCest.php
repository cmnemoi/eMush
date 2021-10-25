<?php

namespace functional\Player\Service;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Service\PlayerService;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
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

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $mushStatusConfig = new ChargeStatusConfig();
        $mushStatusConfig
            ->setName(PlayerStatusEnum::MUSH)
            ->setGameConfig($gameConfig)
        ;
        $sporeStatusConfig = new ChargeStatusConfig();
        $sporeStatusConfig
            ->setName(PlayerStatusEnum::SPORES)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($mushStatusConfig);
        $I->haveInRepository($sporeStatusConfig);

        /** @var CharacterConfig $gioeleCharacterConfig */
        $gioeleCharacterConfig = $I->have(CharacterConfig::class);
        $gioeleCharacterConfig->setInitStatuses(new ArrayCollection([$sporeStatusConfig]));
        /** @var $andieCharacterConfig $characterConfig */
        $andieCharacterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);
        $andieCharacterConfig->setInitStatuses(new ArrayCollection([$sporeStatusConfig]));

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['neron' => $neron, 'gameConfig' => $gameConfig]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY, 'daedalus' => $daedalus]);

        $daedalus->addPlace($room);

        /** @var User $user */
        $user = $I->have(User::class);

        $charactersConfig = new ArrayCollection();
        $charactersConfig->add($gioeleCharacterConfig);
        $charactersConfig->add($andieCharacterConfig);

        $gameConfig->setCharactersConfig($charactersConfig);
        $daedalus->setGameConfig($gameConfig);

        $I->refreshEntities($daedalus);

        $I->expectThrowable(\LogicException::class, fn () => (
            $this->playerService->createPlayer($daedalus, $user, 'non_existent_player')
        ));

        $playerGioele = $this->playerService->createPlayer($daedalus, $user, CharacterEnum::GIOELE);

        $I->assertEquals($gioeleCharacterConfig, $playerGioele->getCharacterConfig());
        $I->assertEquals($daedalus->getGameConfig()->getInitActionPoint(), $playerGioele->getActionPoint());

        $playerAndie = $this->playerService->createPlayer($daedalus, $user, CharacterEnum::ANDIE);

        $I->assertEquals($andieCharacterConfig, $playerAndie->getCharacterConfig());
        $I->assertEquals($daedalus->getGameConfig()->getInitActionPoint(), $playerAndie->getActionPoint());

        $I->assertTrue($playerAndie->isMush());
        $I->assertTrue($playerGioele->isMush());
        $I->assertNotNull($daedalus->getFilledAt());
    }
}
