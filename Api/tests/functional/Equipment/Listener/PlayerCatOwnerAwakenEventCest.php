<?php

namespace Mush\Tests\functional\Equipment\Listener;

use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Listener\PlayerEventSubscriber;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerService;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
final class PlayerCatOwnerAwakenEventCest extends AbstractFunctionalTest
{
    private $playerEventSubscriber;

    private Player $raluca;
    private Player $roland;

    private PlayerService $playerService;
    private StatusService $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->playerEventSubscriber = $I->grabService(PlayerEventSubscriber::class);
        $this->playerService = $I->grabService(PlayerService::class);
        $this->statusService = $I->grabService(StatusService::class);
    }

    public function ifRalucaAwakensSchrodingerShouldSpawn(FunctionalTester $I)
    {
        $this->WhenRalucaAwakens($I);
        $I->assertTrue($this->raluca->getPlace()->hasEquipmentByName(ItemEnum::SCHRODINGER);
    }

    public function ifSomeoneElseIsGivenCatOwnerStatusAndAwakensSchrodingerShouldSpawn(FunctionalTester $I)
    {
        $this->GivenRolandHasCatOwner($I);
        $this->WhenRolandAwakens($I);
        $I->assertEquals(1, $this->roland->getPlace()->getAllEquipmentsByName(ItemEnum::SCHRODINGER)->count());
    }

    public function WhenSchrodingerIsSpawnedByACatOwnerThereShouldBeALog(FunctionalTester $I)
    {
        $this->WhenRalucaAwakens($I);
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'log' => LogEnum::AWAKEN_SCHRODINGER,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    private function GivenRolandHasCatOwner($I)
    {
        $charactersConfig = $this->daedalus->getGameConfig()->getCharactersConfig();
        $charactersConfig->getCharacter(CharacterEnum::ROLAND)->setInitStatuses([$this->statusService->getStatusConfigByNameAndDaedalus(PlayerStatusEnum::CAT_OWNER, $this->daedalus)]);
        $this->daedalus->getGameConfig()->setCharactersConfig($charactersConfig);
    }

    private function WhenRalucaAwakens(FunctionalTester $I)
    {
        $user = new User();
        $user
            ->setUserId(Uuid::v4()->toRfc4122())
            ->setUserName(Uuid::v4()->toRfc4122());
        $I->haveInRepository($user);

        $this->raluca = $this->playerService->createPlayer($this->daedalus, $user, CharacterEnum::RALUCA);
    }

    private function WhenRolandAwakens(FunctionalTester $I)
    {
        $user = new User();
        $user
            ->setUserId(Uuid::v4()->toRfc4122())
            ->setUserName(Uuid::v4()->toRfc4122());
        $I->haveInRepository($user);

        $this->roland = $this->playerService->createPlayer($this->daedalus, $user, CharacterEnum::ROLAND);
    }
}
