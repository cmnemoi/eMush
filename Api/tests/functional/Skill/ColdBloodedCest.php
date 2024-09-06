<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
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
final class ColdBloodedCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::COLD_BLOODED, $I, $this->chun);
    }

    public function shouldGainThreeActionPointsWhenPlayerDies(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(0);

        $this->whenKuanTiDies();

        $this->thenChunShouldHaveActionPoints(3, $I);
    }

    public function shouldPrintPrivateLog(FunctionalTester $I): void
    {
        $this->whenKuanTiDies();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Votre compétence **Sang-froid** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: PlayerModifierLogEnum::COLD_BLOODED_WORKED,
                visibility: VisibilityEnum::PRIVATE,
                inPlayerRoom: false
            ),
            I: $I
        );
    }

    public function shouldNotWorkForMushPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerHasActionPoints(0);

        $this->givenPlayerIsMush();

        $this->whenKuanTiDies();

        $this->thenPlayerShouldNotHaveActionPoints(0, $I);
    }

    private function givenChunHasActionPoints(int $actionPoints): void
    {
        $this->chun->setActionPoint($actionPoints);
    }

    private function givenPlayerHasActionPoints(int $actionPoints): void
    {
        $this->player->setActionPoint($actionPoints);
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenKuanTiDies(): void
    {
        $playerEvent = new PlayerEvent(
            player: $this->kuanTi,
            tags: [EndCauseEnum::DEPRESSION],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
    }

    private function thenChunShouldHaveActionPoints(int $expectedActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedActionPoints, $this->chun->getActionPoint());
    }

    private function thenPlayerShouldNotHaveActionPoints(int $expectedActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedActionPoints, $this->player->getActionPoint());
    }
}
