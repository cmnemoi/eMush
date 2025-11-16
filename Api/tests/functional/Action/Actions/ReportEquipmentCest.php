<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Action\Actions\ReportEquipment;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\NeronMessageService;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ReportEquipmentCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ReportEquipment $reportEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private StatisticRepositoryInterface $statisticRepository;
    private NeronMessageService $neronMessageService;

    private GameEquipment $equipment;
    private ?Message $failuresThread = null;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REPORT_EQUIPMENT]);
        $this->reportEquipment = $I->grabService(ReportEquipment::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
        $this->neronMessageService = $I->grabService(NeronMessageService::class);
    }

    public function shouldNotBeVisibleIfEquipmentIsNotBroken(FunctionalTester $I): void
    {
        $this->givenEquipmentInPlayerRoom();

        $this->whenPlayerTriesToReportEquipment();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeVisibleIfEquipmentIsAlreadyReported(FunctionalTester $I): void
    {
        $this->givenBrokenEquipmentInPlayerRoom();
        $this->givenEquipmentIsReported();

        $this->whenPlayerTriesToReportEquipment();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeVisibleIfEquipmentIsNotInRoom(FunctionalTester $I): void
    {
        $this->givenBrokenEquipmentInSpace();

        $this->whenPlayerTriesToReportEquipment();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableOnPlanet(FunctionalTester $I): void
    {
        $this->givenBrokenEquipmentInPlayerRoom();
        $this->givenPlayerIsOnPlanet();

        $this->whenPlayerTriesToReportEquipment();

        $this->thenActionShouldNotBeExecutable(
            message: ActionImpossibleCauseEnum::ON_PLANET,
            I: $I,
        );
    }

    public function shouldCreateBrokenEquipmentAlert(FunctionalTester $I): void
    {
        $this->givenBrokenEquipmentInPlayerRoom();

        $this->whenPlayerReportsEquipment();

        $this->thenBrokenEquipmentAlertShouldBeCreated($I);
    }

    public function shouldCreateAlertElementWithPlayerInfo(FunctionalTester $I): void
    {
        $this->givenBrokenEquipmentInPlayerRoom();

        $this->whenPlayerReportsEquipment();

        $this->thenAlertElementShouldBeCreatedWithPlayerInfo($I);
    }

    public function shouldCreateNeronMessage(FunctionalTester $I): void
    {
        $this->givenBrokenEquipmentInPlayerRoom();

        $this->whenPlayerReportsEquipment();

        $this->thenNeronMessageShouldBeCreated($I);
    }

    public function shouldCreateBrokenDoorAlertForDoor(FunctionalTester $I): void
    {
        $this->givenBrokenDoorInPlayerRoom($I);

        $this->whenPlayerReportsDoor();

        $this->thenBrokenDoorAlertShouldBeCreated($I);
    }

    public function shouldCreateAlertElementForDoorWithPlayerInfo(FunctionalTester $I): void
    {
        $this->givenBrokenDoorInPlayerRoom($I);

        $this->whenPlayerReportsDoor();

        $this->thenDoorAlertElementShouldBeCreatedWithPlayerInfo($I);
    }

    public function shouldPushNeronThreadMessageUp(FunctionalTester $I): void
    {
        $this->givenBrokenEquipmentInPlayerRoom();
        $this->givenEquipmentFailureNeronThreadAlreadyExists();
        $this->givenFailureThreadHasBeenUpdatedOneHourAgo();

        $this->whenPlayerReportsEquipment();

        $this->thenNeronThreadUpdatedAtDateShouldBeNow($I);
    }

    public function shouldIncrementStatistic(FunctionalTester $I): void
    {
        $this->givenBrokenEquipmentInPlayerRoom();

        $this->whenPlayerReportsEquipment();

        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::SIGNAL_EQUIP, $this->player->getUser()->getId());
        $I->assertEquals(1, $statistic?->getCount());
    }

    private function givenEquipmentInPlayerRoom(): void
    {
        $this->equipment = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::FUEL_TANK,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenBrokenEquipmentInPlayerRoom(): void
    {
        $this->givenEquipmentInPlayerRoom();
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->equipment,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenBrokenEquipmentInSpace(): void
    {
        $this->equipment = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::RESEARCH_LABORATORY,
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->equipment,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenEquipmentIsReported(): void
    {
        $this->reportEquipment->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->equipment,
            player: $this->player,
            target: $this->equipment,
        );
        $this->reportEquipment->execute();
    }

    private function givenPlayerIsOnPlanet(): void
    {
        $this->player->changePlace($this->daedalus->getPlanetPlace());
    }

    private function givenBrokenDoorInPlayerRoom(FunctionalTester $I): void
    {
        $door = Door::createFromRooms($this->player->getPlace(), $this->daedalus->getSpace());
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);
        $this->equipment = $door;

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $door,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerTriesToReportEquipment(): void
    {
        $this->reportEquipment->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->equipment,
            player: $this->player,
            target: $this->equipment,
        );
    }

    private function whenPlayerReportsEquipment(): void
    {
        $this->whenPlayerTriesToReportEquipment();
        $this->reportEquipment->execute();
    }

    private function whenPlayerReportsDoor(): void
    {
        $this->reportEquipment->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->equipment,
            player: $this->player,
            target: $this->equipment,
        );
        $this->reportEquipment->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->reportEquipment->isVisible());
    }

    private function thenActionShouldNotBeExecutable(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->reportEquipment->cannotExecuteReason());
    }

    private function thenBrokenEquipmentAlertShouldBeCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: Alert::class,
            params: [
                'daedalus' => $this->daedalus,
                'name' => AlertEnum::BROKEN_EQUIPMENTS,
            ]
        );
    }

    private function thenAlertElementShouldBeCreatedWithPlayerInfo(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: AlertElement::class,
            params: [
                'place' => $this->player->getPlace(),
                'equipment' => $this->equipment,
                'playerInfo' => $this->player->getPlayerInfo(),
            ]
        );
    }

    private function thenNeronMessageShouldBeCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: Message::class,
            params: [
                'author' => null,
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::REPORT_EQUIPMENT,
            ]
        );
    }

    private function thenBrokenDoorAlertShouldBeCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: Alert::class,
            params: [
                'daedalus' => $this->daedalus,
                'name' => AlertEnum::BROKEN_DOORS,
            ]
        );
    }

    private function thenDoorAlertElementShouldBeCreatedWithPlayerInfo(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: AlertElement::class,
            params: [
                'place' => $this->player->getPlace(),
                'equipment' => $this->equipment,
                'playerInfo' => $this->player->getPlayerInfo(),
            ]
        );
    }

    private function givenEquipmentFailureNeronThreadAlreadyExists(): void
    {
        $this->failuresThread = $this->neronMessageService->getMessageNeronCycleFailures(
            daedalus: $this->daedalus,
            time: new \DateTime(),
        );
    }

    private function givenFailureThreadHasBeenUpdatedOneHourAgo(): void
    {
        $this->failuresThread->setUpdatedAt(new \DateTime('-1 hour'));
    }

    private function thenNeronThreadUpdatedAtDateShouldBeNow(FunctionalTester $I): void
    {
        $now = new \DateTime();
        $I->assertEquals(
            expected: $now->format('H:i:s'),
            actual: $this->failuresThread->getUpdatedAt()->format('H:i:s'),
            message: "NERON failure thread updated at date should be {$now->format('H:i:s')}, but got {$this->failuresThread->getUpdatedAt()->format('H:i:s')}"
        );
    }
}
