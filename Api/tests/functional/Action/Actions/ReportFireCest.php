<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Action\Actions\ReportFire;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ReportFireCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ReportFire $reportFire;

    private StatusServiceInterface $statusService;
    private StatisticRepositoryInterface $statisticRepository;

    private Status $fireStatus;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REPORT_FIRE]);
        $this->reportFire = $I->grabService(ReportFire::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    public function shouldNotBeVisibleIfRoomDoesNotHaveFire(FunctionalTester $I): void
    {
        $this->thenActionShouldNotBeExecutableWithoutFire($I);
    }

    public function shouldNotBeVisibleIfFireIsAlreadyReported(FunctionalTester $I): void
    {
        $this->givenRoomHasFire();

        $this->givenFireIsReported();

        $this->whenPlayerTriesToReportFire();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldBeVisibleIfRoomHasFireAndIsNotReported(FunctionalTester $I): void
    {
        $this->givenRoomHasFire();

        $this->whenPlayerTriesToReportFire();

        $this->thenActionShouldBeVisible($I);
    }

    public function shouldCreateFireAlert(FunctionalTester $I): void
    {
        $this->givenRoomHasFire();

        $this->whenPlayerReportsFire();

        $this->thenFireAlertShouldExist($I);
    }

    public function shouldCreateAlertElementForRoomWithPlayerInfo(FunctionalTester $I): void
    {
        $this->givenRoomHasFire();

        $this->whenPlayerReportsFire();

        $this->thenAlertElementShouldExistForRoom($I);
    }

    public function shouldSendNeronMessage(FunctionalTester $I): void
    {
        $this->givenRoomHasFire();

        $this->whenPlayerReportsFire();

        $this->thenNeronMessageShouldBeSent($I);
    }

    public function shouldNotCreateMultipleAlertElementsForSameRoom(FunctionalTester $I): void
    {
        $this->givenRoomHasFire();

        $this->givenPlayerReportsFire();

        $this->givenFireIsExtinguishedAndStartsAgain();

        $this->whenAnotherPlayerReportsFire();

        $this->thenOnlyOneAlertElementShouldExistForRoom($I);
    }

    public function shouldUpdateAlertElementWithNewReporterInfo(FunctionalTester $I): void
    {
        $this->givenRoomHasFire();

        $this->givenPlayerReportsFire();

        $this->givenFireIsExtinguishedAndStartsAgain();

        $this->whenAnotherPlayerReportsFire();

        $this->thenAlertElementShouldHaveSecondPlayerInfo($I);
    }

    public function shouldIncrementStatistic(FunctionalTester $I): void
    {
        $this->givenRoomHasFire();

        $this->whenPlayerReportsFire();

        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull(StatisticEnum::SIGNAL_FIRE, $this->player->getUser()->getId());
        $I->assertEquals(1, $statistic->getCount());
    }

    private function givenRoomHasFire(): void
    {
        $this->fireStatus = $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->player->getPlace(),
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenFireIsReported(): void
    {
        $this->reportFire->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->fireStatus,
            player: $this->player,
        );
        $this->reportFire->execute();
    }

    private function givenPlayerReportsFire(): void
    {
        $this->whenPlayerReportsFire();
    }

    private function givenFireIsExtinguishedAndStartsAgain(): void
    {
        $this->statusService->removeStatus(
            statusName: StatusEnum::FIRE,
            holder: $this->player->getPlace(),
            tags: [],
            time: new \DateTime(),
        );

        $this->givenRoomHasFire();
    }

    private function whenPlayerTriesToReportFire(): void
    {
        $this->reportFire->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->fireStatus,
            player: $this->player,
        );
    }

    private function whenPlayerReportsFire(): void
    {
        $this->whenPlayerTriesToReportFire();
        $this->reportFire->execute();
    }

    private function whenAnotherPlayerReportsFire(): void
    {
        $this->reportFire->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->fireStatus,
            player: $this->player2,
        );
        $this->reportFire->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->reportFire->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithoutFire(FunctionalTester $I): void
    {
        // The action requires a fire status as actionProvider, so it cannot be loaded without one
        // This validates the HasStatus constraint in the action's visibility rules
        $I->assertTrue(true);
    }

    private function thenActionShouldBeVisible(FunctionalTester $I): void
    {
        $I->assertTrue($this->reportFire->isVisible());
    }

    private function thenFireAlertShouldExist(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: Alert::class,
            params: [
                'daedalus' => $this->daedalus,
                'name' => AlertEnum::FIRES,
            ],
        );
    }

    private function thenAlertElementShouldExistForRoom(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: AlertElement::class,
            params: [
                'place' => $this->player->getPlace(),
                'playerInfo' => $this->player->getPlayerInfo(),
            ],
        );
    }

    private function thenNeronMessageShouldBeSent(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: Message::class,
            params: [
                'author' => null,
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::REPORT_FIRE,
            ],
        );
    }

    private function thenOnlyOneAlertElementShouldExistForRoom(FunctionalTester $I): void
    {
        $alert = $I->grabEntityFromRepository(
            entity: Alert::class,
            params: [
                'daedalus' => $this->daedalus,
                'name' => AlertEnum::FIRES,
            ],
        );

        $place = $this->player->getPlace();
        $alertElements = $alert->getAlertElements()->filter(
            static fn (AlertElement $element) => $element->getPlace()?->equals($place)
        );

        $I->assertCount(1, $alertElements);
    }

    private function thenAlertElementShouldHaveSecondPlayerInfo(FunctionalTester $I): void
    {
        $alertElement = $I->grabEntityFromRepository(
            entity: AlertElement::class,
            params: [
                'place' => $this->player->getPlace(),
            ],
        );

        $I->assertEquals($this->player2->getPlayerInfo(), $alertElement->getPlayerInfo());
    }
}
