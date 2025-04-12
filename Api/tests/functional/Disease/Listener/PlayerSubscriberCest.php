<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Event;

use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private PlayerServiceInterface $playerService;
    private RoomLogServiceInterface $roomLogService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->roomLogService = $I->grabService(RoomLogServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testDispatchSickPlayerDeath(FunctionalTester $I): void
    {
        $this->givenPlayerIsSick();
        $this->givenNoWitnesses(); // avoid false positives caused by trauma diseases when a player is about to die
        $this->thenSicknessShouldExist($I);
        $this->whenPlayerDies();
        $this->thenSicknessShouldBeCorrectlyRemoved($I);
        $this->thenPlayerDoesNotSeeSicknessCuredLog($I);
    }

    private function givenPlayerIsSick(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FLU,
            player: $this->player,
            reasons: [],
        );
    }

    private function givenNoWitnesses(): void
    {
        foreach ($this->player->getAlivePlayersInRoomExceptSelf() as $witness) {
            $this->player->getPlace()->removePlayer($witness);
            $witness->setPlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::PLANET));
        }
    }

    private function whenPlayerDies(): void
    {
        $this->playerService->killPlayer(
            player: $this->player,
            endReason: EndCauseEnum::DEPRESSION,
            time: new \DateTime(),
        );
    }

    private function thenSicknessShouldExist(FunctionalTester $I): void
    {
        $I->seeInRepository(PlayerDisease::class);
    }

    private function thenSicknessShouldBeCorrectlyRemoved(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(PlayerDisease::class);
    }

    private function thenPlayerDoesNotSeeSicknessCuredLog(FunctionalTester $I): void
    {
        $roomLogs = $this->roomLogService->getRoomLog($this->player);
        $diseaseCureRoomLogs = $roomLogs->filter(static fn (RoomLog $log) => $log->getLog() === LogEnum::DISEASE_CURED)->toArray();
        $I->assertEmpty($diseaseCureRoomLogs);
    }
}
