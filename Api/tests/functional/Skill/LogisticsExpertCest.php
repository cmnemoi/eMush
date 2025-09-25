<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class LogisticsExpertCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    private Player $paola;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldGiveActionPointToASingleOtherPlayer(FunctionalTester $I): void
    {
        $this->givenPaolaIsLogisticsExpert($I);
        $this->givenAllPlayersHave10ActionPoints();

        $this->whenCycleChangeIsTriggered();

        $this->thenPaolaShouldHave11ActionPoints($I);
        $this->thenOnePlayerShouldHave11AndOther12ActionPoints($I);
        $this->thenLogisticLogShouldBePrinted($I);
    }

    public function multipleLogisticsExpertShouldGiveActionPointToMultiplePlayers(FunctionalTester $I): void
    {
        $this->givenChunAndKuanTiAreLogisticsExperts($I);
        $this->givenChunAndKuanTiHave10ActionPoints();

        $this->whenCycleChangeIsTriggered();

        $this->thenChunAndKuanTiShouldHave12ActionPoints($I);
        $this->thenLogisticLogsShouldBePrintedForBoth($I);
    }

    public function shouldIgnoreHighlyInactivePlayers(FunctionalTester $I): void
    {
        $this->givenPlayer1IsLogisticsExpert($I);
        $this->givenPlayer2IsHighlyInactive();
        $this->givenPlayer2Has10ActionPoints();

        $this->whenCycleChangeIsTriggered();

        $this->thenPlayer2ShouldHave11ActionPoints($I);
    }

    public function shouldNotIgnoreInactivePlayers(FunctionalTester $I): void
    {
        $this->givenPlayer1IsLogisticsExpert($I);
        $this->givenPlayer2IsInactive();
        $this->givenPlayer2Has10ActionPoints();

        $this->whenCycleChangeIsTriggered();

        $this->thenPlayer2ShouldHave12ActionPoints($I);
    }

    private function givenPaolaIsLogisticsExpert(FunctionalTester $I): void
    {
        $this->paola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        $this->addSkillToPlayer(SkillEnum::LOGISTICS_EXPERT, $I, $this->paola);
    }

    private function givenAllPlayersHave10ActionPoints(): void
    {
        $this->player1->setActionPoint(10);
        $this->player2->setActionPoint(10);
        $this->paola->setActionPoint(10);
    }

    private function givenChunAndKuanTiAreLogisticsExperts(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::LOGISTICS_EXPERT, $I, $this->chun);
        $this->addSkillToPlayer(SkillEnum::LOGISTICS_EXPERT, $I, $this->kuanTi);
    }

    private function givenChunAndKuanTiHave10ActionPoints(): void
    {
        $this->chun->setActionPoint(10);
        $this->kuanTi->setActionPoint(10);
    }

    private function givenPlayer1IsLogisticsExpert(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::LOGISTICS_EXPERT, $I, $this->player1);
    }

    private function givenPlayer2IsHighlyInactive(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HIGHLY_INACTIVE,
            holder: $this->player2,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenPlayer2IsInactive(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $this->player2,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenPlayer2Has10ActionPoints(): void
    {
        $this->player2->setActionPoint(10);
    }

    private function whenCycleChangeIsTriggered(): void
    {
        $cycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function thenPaolaShouldHave11ActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(11, $this->paola->getActionPoint());
    }

    private function thenOnePlayerShouldHave11AndOther12ActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(23, $this->player1->getActionPoint() + $this->player2->getActionPoint());
    }

    private function thenLogisticLogShouldBePrinted(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'La compétence **Logistique** de **Paola** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player1->getActionPoint() === 12 ? $this->player1 : $this->player2,
                log: PlayerModifierLogEnum::LOGISTIC_LOG,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );
    }

    private function thenChunAndKuanTiShouldHave12ActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(12, $this->chun->getActionPoint());
        $I->assertEquals(12, $this->kuanTi->getActionPoint());
    }

    private function thenLogisticLogsShouldBePrintedForBoth(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'La compétence **Logistique** de **Kuan Ti** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: PlayerModifierLogEnum::LOGISTIC_LOG,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'La compétence **Logistique** de **Chun** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->kuanTi,
                log: PlayerModifierLogEnum::LOGISTIC_LOG,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );
    }

    private function thenPlayer2ShouldHave11ActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(11, $this->player2->getActionPoint());
    }

    private function thenPlayer2ShouldHave12ActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(12, $this->player2->getActionPoint());
    }
}
