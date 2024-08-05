<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Status\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SkillPointsCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    #[DataProvider('skillPointsDataProvider')]
    public function shouldIncrementAtDayChange(FunctionalTester $I, Example $skillPoints): void
    {
        $this->givenPlayerHasZeroSkillPoints($skillPoints);

        $this->whenADayPasses();

        $this->thenPlayerShouldHaveIncreasedSkillPoints($I, $skillPoints);
    }

    private function skillPointsDataProvider(): array
    {
        return SkillPointsEnum::getAll()->map(static fn (SkillPointsEnum $skillPoints) => [
            'name' => $skillPoints->toString(),
        ])->toArray();
    }

    private function givenPlayerHasZeroSkillPoints(Example $skillPoints): void
    {
        /** @var ChargeStatus $skillPointsStatus */
        $skillPointsStatus = $this->statusService->createStatusFromName(
            statusName: $skillPoints['name'],
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );
        $this->statusService->updateCharge($skillPointsStatus, -$skillPointsStatus->getThreshold(), [], new \DateTime());
    }

    private function whenADayPasses(): void
    {
        $cycleEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [EventEnum::NEW_DAY],
            time: new \DateTime()
        );
        $this->eventService->callEvent($cycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
    }

    private function thenPlayerShouldHaveIncreasedSkillPoints(FunctionalTester $I, Example $skillPoints): void
    {
        $skillPointsStatus = $this->chun->getChargeStatusByNameOrThrow($skillPoints['name']);
        $I->assertEquals(
            expected: $this->getSkillPointsIncrement($skillPoints['name']),
            actual: $skillPointsStatus->getCharge()
        );
    }

    private function getSkillPointsIncrement(string $skillPoints): int
    {
        return match (SkillPointsEnum::from($skillPoints)) {
            SkillPointsEnum::BOTANIST_POINTS => 2,
            SkillPointsEnum::CONCEPTOR_POINTS => 2,
            SkillPointsEnum::IT_EXPERT_POINTS => 2,
            SkillPointsEnum::PILGRED_POINTS => 1,
            SkillPointsEnum::SHOOTER_POINTS => 2,
            SkillPointsEnum::TECHNICIAN_POINTS => 1,
            default => 0,
        };
    }
}
