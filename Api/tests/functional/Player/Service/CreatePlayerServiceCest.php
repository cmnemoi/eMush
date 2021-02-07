<?php

namespace functional\Player\Service;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Service\PlayerService;
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
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var Place $room */
        $room = $I->have(Place::class, ['name' => RoomEnum::LABORATORY]);

        /** @var CharacterConfig $gioeleCharacterConfig */
        $gioeleCharacterConfig = $I->have(CharacterConfig::class);

        /** @var $andieCharacterConfig $characterConfig */
        $andieCharacterConfig = $I->have(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);

        $daedalus->addPlace($room);

        /** @var User $user */
        $user = $I->have(User::class);

        $charactersConfig = new ArrayCollection();
        $charactersConfig->add($gioeleCharacterConfig);
        $charactersConfig->add($andieCharacterConfig);

        $daedalus->getGameConfig()->setCharactersConfig($charactersConfig);

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
