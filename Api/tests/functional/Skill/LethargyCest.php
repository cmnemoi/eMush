<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class LethargyCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::LETHARGY, $I);
    }

    public function shouldDoublesMaximumPlayerActionPoints(FunctionalTester $I): void
    {
        $this->thenChunMaxActionPointsShouldBe(24, $I);
    }

    public function shouldNotDoubleMaximumPointsOfOtherPlayers(FunctionalTester $I): void
    {
        $this->thenKuanTiMaxActionPointsShouldBe(12, $I);
    }

    public function shouldGiveOneExtraActionPointIfSleepingForFourCyclesAndMore(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(0);

        $this->givenChunHasBeenSleepingForCycles(3);

        $this->whenACyclePassesForChun();

        // 1PA (base) + 1PA (sleep bonus) + 1PA (lethargy bonus) = 3PA
        $this->thenChunShouldHaveActionPoints(3, $I);
    }

    public function shouldPrintAPrivateLogForSleepingBonus(FunctionalTester $I): void
    {
        $this->givenChunHasBeenSleepingForCycles(4);

        $this->whenACyclePassesForChun();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Votre compétence **Léthargie** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::LETHARGY_WORKED,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );
    }

    public function shouldNotGiveAnyExtraActionPointIfSleepingForLessThanFourCycles(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(0);

        $this->givenChunHasBeenSleepingForCycles(2);

        $this->whenACyclePassesForChun();

        // 1PA (base) + 1PA (sleep bonus) = 2PA
        $this->thenChunShouldHaveActionPoints(2, $I);
    }

    public function shouldNotGiveAnyExtraActionPointIfSleepWasInterrupted(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(0);

        $this->givenChunHasBeenSleepingForCycles(10);

        $this->givenChunWakesUp();

        $this->givenChunSleeps();

        $this->whenACyclePassesForChun();

        // 1PA (base) + 1PA (sleep bonus) = 2PA
        $this->thenChunShouldHaveActionPoints(2, $I);
    }

    private function givenChunHasActionPoints(int $actionPoints): void
    {
        $this->chun->setActionPoint($actionPoints);
    }

    private function givenChunHasBeenSleepingForCycles(int $numberOfCycles): void
    {
        $lyingDownStatus = $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
        $this->statusService->updateCharge(
            chargeStatus: $lyingDownStatus,
            delta: $numberOfCycles,
            tags: [],
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
    }

    private function givenChunWakesUp(): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunSleeps(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenACyclePassesForChun(): void
    {
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_CYCLE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
    }

    private function thenChunMaxActionPointsShouldBe(int $maxActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($maxActionPoints, $this->chun->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValueOrThrow());
    }

    private function thenKuanTiMaxActionPointsShouldBe(int $maxActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($maxActionPoints, $this->kuanTi->getVariableByName(PlayerVariableEnum::ACTION_POINT)->getMaxValueOrThrow());
    }

    private function thenChunShouldHaveActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->chun->getActionPoint());
    }
}
