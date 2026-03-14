<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Status\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Skill\Enum\SkillEnum;
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

    #[DataProvider('skillPointsDataProvider')]
    public function shouldPrintPrivateLogOnGain(FunctionalTester $I, Example $skillPoints): void
    {
        $this->givenPlayerHasZeroSkillPoints($skillPoints);

        $this->whenADayPasses();

        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => StatusEventLogEnum::CHARGE_STATUS_UPDATED_LOGS['gain']['value'][$skillPoints['pointName']],
            ],
        );
    }

    public function shouldITPolymathGainThreePoints(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::IT_EXPERT, $I);
        $this->addSkillToPlayer(SkillEnum::POLYMATH, $I);

        $this->givenPlayerHasZeroSkillPointsOf(SkillPointsEnum::COMPUTER_POINTS);

        $this->whenADayPasses();

        $this->thenPlayerShouldHaveIncreasedItPointsByThree($I, SkillPointsEnum::COMPUTER_POINTS);
    }

    public function shouldPolymathITGainThreePoints(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYMATH, $I);
        $this->addSkillToPlayer(SkillEnum::IT_EXPERT, $I);

        $this->givenPlayerHasZeroSkillPointsOf(SkillPointsEnum::COMPUTER_POINTS);

        $this->whenADayPasses();

        $this->thenPlayerShouldHaveIncreasedItPointsByThree($I, SkillPointsEnum::COMPUTER_POINTS);
    }

    public function shouldPrintPrivateLogOnDailyITGainOnly(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::IT_EXPERT, $I);
        $this->addSkillToPlayer(SkillEnum::POLYMATH, $I);

        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => StatusEventLogEnum::CHARGE_STATUS_UPDATED_LOGS['gain']['value'][SkillPointsEnum::COMPUTER_POINTS->toString()],
                'visibility' => VisibilityEnum::PRIVATE,
            ],
        );

        $this->givenPlayerHasZeroSkillPointsOf(SkillPointsEnum::COMPUTER_POINTS);

        $this->whenADayPasses();

        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => StatusEventLogEnum::CHARGE_STATUS_UPDATED_LOGS['gain']['value'][SkillPointsEnum::COMPUTER_POINTS->toString()],
                'visibility' => VisibilityEnum::PRIVATE,
            ],
        );
    }

    public function shouldPrintPrivateLogOnDailyPolyGainOnly(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYMATH, $I);
        $this->addSkillToPlayer(SkillEnum::IT_EXPERT, $I);

        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => StatusEventLogEnum::CHARGE_STATUS_UPDATED_LOGS['gain']['value'][SkillPointsEnum::COMPUTER_POINTS->toString()],
                'visibility' => VisibilityEnum::PRIVATE,
            ],
        );

        $this->givenPlayerHasZeroSkillPointsOf(SkillPointsEnum::COMPUTER_POINTS);

        $this->whenADayPasses();

        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'log' => StatusEventLogEnum::CHARGE_STATUS_UPDATED_LOGS['gain']['value'][SkillPointsEnum::COMPUTER_POINTS->toString()],
                'visibility' => VisibilityEnum::PRIVATE,
            ],
        );
    }

    private function givenPlayerHasZeroSkillPoints(Example $skillPoints): void
    {
        /** @var ChargeStatus $skillPointsStatus */
        $skillPointsStatus = $this->statusService->createStatusFromConfigName(
            configName: $skillPoints['configName'],
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );
        $this->statusService->updateCharge($skillPointsStatus, -$skillPointsStatus->getThreshold(), [], new \DateTime());
    }

    private function givenPlayerHasZeroSkillPointsOf(SkillPointsEnum $skillPoints): void
    {
        /** @var ChargeStatus $skillPointsStatus */
        $skillPointsStatus = $this->chun->getChargeStatusByNameOrThrow($skillPoints->toString());
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
        $skillPointsStatus = $this->chun->getChargeStatusByNameOrThrow($skillPoints['pointName']);
        $I->assertEquals(
            expected: $this->getSkillPointsIncrement($skillPoints['configName']),
            actual: $skillPointsStatus->getCharge()
        );
    }

    private function thenPlayerShouldHaveIncreasedItPointsByThree(FunctionalTester $I, SkillPointsEnum $skillPoints): void
    {
        $skillPointsStatus = $this->chun->getChargeStatusByNameOrThrow($skillPoints->toString());
        $I->assertEquals(
            expected: 3,
            actual: $skillPointsStatus->getCharge()
        );
    }

    private function getSkillPointsIncrement(string $skillPoints): int
    {
        return match ($skillPoints) {
            SkillPointsEnum::ONE_GARDEN_POINTS_MAX_2->value . '_default' => 1,
            SkillPointsEnum::TWO_GARDEN_POINTS_MAX_4->value . '_default' => 2,
            SkillPointsEnum::ONE_COOK_POINTS_MAX_2->value . '_default' => 1,
            SkillPointsEnum::FOUR_COOK_POINTS_MAX_8->value . '_default' => 4,
            SkillPointsEnum::TWO_CORE_POINTS_MAX_4->value . '_default' => 2,
            SkillPointsEnum::TWO_COMPUTER_POINTS_MAX_4->value . '_default' => 2,
            SkillPointsEnum::ONE_PILGRED_POINTS_MAX_2->value . '_default' => 1,
            SkillPointsEnum::TWO_SHOOT_POINTS_MAX_4->value . '_default' => 2,
            SkillPointsEnum::ONE_ENGINEER_POINTS_MAX_2->value . '_default' => 1,
            SkillPointsEnum::TWO_HEAL_POINTS_MAX_4->value . '_default' => 2,
            SkillPointsEnum::ONE_COMPUTER_POINTS_MAX_2->value . '_default' => 1,
            SkillPointsEnum::ONE_TORTURE_POINTS_MAX_2->value . '_default' => 1,
            default => throw new \LogicException("Please define the increment for {$skillPoints}"),
        };
    }

    private function skillPointsDataProvider(): array
    {
        return [
            ['configName' => SkillPointsEnum::ONE_COMPUTER_POINTS_MAX_2->value . '_default', 'pointName' => SkillPointsEnum::COMPUTER_POINTS->value],
            ['configName' => SkillPointsEnum::TWO_COMPUTER_POINTS_MAX_4->value . '_default', 'pointName' => SkillPointsEnum::COMPUTER_POINTS->value],
            ['configName' => SkillPointsEnum::ONE_GARDEN_POINTS_MAX_2->value . '_default', 'pointName' => SkillPointsEnum::GARDEN_POINTS->value],
            ['configName' => SkillPointsEnum::TWO_GARDEN_POINTS_MAX_4->value . '_default', 'pointName' => SkillPointsEnum::GARDEN_POINTS->value],
            ['configName' => SkillPointsEnum::ONE_COOK_POINTS_MAX_2->value . '_default', 'pointName' => SkillPointsEnum::COOK_POINTS->value],
            ['configName' => SkillPointsEnum::FOUR_COOK_POINTS_MAX_8->value . '_default', 'pointName' => SkillPointsEnum::COOK_POINTS->value],
            ['configName' => SkillPointsEnum::TWO_CORE_POINTS_MAX_4->value . '_default', 'pointName' => SkillPointsEnum::CORE_POINTS->value],
            ['configName' => SkillPointsEnum::TWO_HEAL_POINTS_MAX_4->value . '_default', 'pointName' => SkillPointsEnum::HEAL_POINTS->value],
            ['configName' => SkillPointsEnum::ONE_PILGRED_POINTS_MAX_2->value . '_default', 'pointName' => SkillPointsEnum::PILGRED_POINTS->value],
            ['configName' => SkillPointsEnum::TWO_SHOOT_POINTS_MAX_4->value . '_default', 'pointName' => SkillPointsEnum::SHOOT_POINTS->value],
            ['configName' => SkillPointsEnum::ONE_ENGINEER_POINTS_MAX_2->value . '_default', 'pointName' => SkillPointsEnum::ENGINEER_POINTS->value],
            ['configName' => SkillPointsEnum::ONE_TORTURE_POINTS_MAX_2->value . '_default', 'pointName' => SkillPointsEnum::TORTURE_POINTS->value],
        ];
    }
}
