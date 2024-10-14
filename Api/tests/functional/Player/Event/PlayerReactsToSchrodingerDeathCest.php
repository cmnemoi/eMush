<?php

namespace Mush\Tests\functional\Player\Event;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventService;
use Mush\Player\Entity\Player;
use Mush\Player\Listener\EquipmentSubscriber;
use Mush\Player\Service\PlayerService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
final class PlayerReactsToSchrodingerDeathCest extends AbstractFunctionalTest
{
    private $equipmentSubscriber;

    private PlayerService $playerService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusService $statusService;
    private EventService $eventService;

    private Player $raluca;
    private Player $roland;
    private GameItem $schrodinger;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->equipmentSubscriber = $I->grabService(EquipmentSubscriber::class);
        $this->playerService = $I->grabService(PlayerService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusService::class);
        $this->eventService = $I->grabService(EventService::class);
    }

    public function ifSchrodingerDiesRalucaShouldLose4Morale(FunctionalTester $I)
    {
        $this->GivenRalucaAwakens($I);
        $this->GivenPlayerHasMoralPoints($this->raluca, 10);
        $this->SelectSchrodinger();

        $this->whenCatIsDestroyed();
        $I->assertEquals(6, $this->raluca->getMoralPoint());
    }

    public function ifSomeoneElseIsGivenCatOwnerStatusAndSchrodingerDiesTheyShouldLose4Morale(FunctionalTester $I)
    {
        $this->GivenRolandAwakens($I);
        $this->GivenPlayerHasMoralPoints($this->roland, 10);
        $this->CreateSchrodinger();
        $this->GivenPlayerhasStatusCatOwner($this->roland);

        $this->whenCatIsDestroyed();
        $I->assertEquals(6, $this->roland->getMoralPoint());
    }

    public function ifSchrodingerDiesPlayersWithoutCatOwnerLoseNoMorale(FunctionalTester $I)
    {
        $this->GivenRolandAwakens($I);
        $this->GivenPlayerHasMoralPoints($this->roland, 10);
        $this->CreateSchrodinger();

        $this->whenCatIsDestroyed();
        $I->assertEquals(10, $this->roland->getMoralPoint());
    }

    private function GivenRalucaAwakens(FunctionalTester $I)
    {
        $user = new User();
        $user
            ->setUserId(Uuid::v4()->toRfc4122())
            ->setUserName(Uuid::v4()->toRfc4122());
        $I->haveInRepository($user);

        $this->raluca = $this->playerService->createPlayer($this->daedalus, $user, CharacterEnum::RALUCA);
    }

    private function GivenRolandAwakens(FunctionalTester $I)
    {
        $user = new User();
        $user
            ->setUserId(Uuid::v4()->toRfc4122())
            ->setUserName(Uuid::v4()->toRfc4122());
        $I->haveInRepository($user);

        $this->roland = $this->playerService->createPlayer($this->daedalus, $user, CharacterEnum::ROLAND);
    }

    private function GivenPlayerHasMoralPoints(Player $player, int $moralePoints)
    {
        $player->setMoralPoint($moralePoints);
    }

    private function GivenPlayerHasStatusCatOwner(Player $player)
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::CAT_OWNER,
            $player,
            [],
            new \DateTime(),
        );
    }

    private function SelectSchrodinger()
    {
        $this->schrodinger = $this->raluca->getPlace()->getAllEquipmentsByName(ItemEnum::SCHRODINGER)->first();
    }

    private function CreateSchrodinger()
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenCatIsDestroyed(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            $this->schrodinger,
            null,
            VisibilityEnum::PRIVATE,
            [],
            new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
