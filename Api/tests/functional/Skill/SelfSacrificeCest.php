<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class SelfSacrificeCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::SELF_SACRIFICE, $I);
        $this->setupNoIncidents();
    }

    public function shouldNotDieAtZeroMorale(FunctionalTester $I): void
    {
        $this->givenPlayerHasMorale(0);

        $this->whenCyclePassesForPlayer();

        $this->thenPlayerShouldBeAlive($I);
    }

    public function shouldLoseOneHealthPointAtZeroMorale(FunctionalTester $I): void
    {
        $this->givenPlayerHasHealthPoints(14);

        $this->givenPlayerHasMorale(0);

        $this->whenCyclePassesForPlayer();

        $I->assertEquals(13, $this->player->getHealthPoint());
    }

    public function shouldPrintPrivateLog(FunctionalTester $I): void
    {
        $this->givenPlayerHasMorale(0);

        $this->whenCyclePassesForPlayer();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "Votre compétence **Abnégation** a porté ses fruits, vous n'êtes pas morte...",
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: PlayerModifierLogEnum::SELF_SACRIFICE_WORKED,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    private function givenPlayerHasMorale(int $morale): void
    {
        $this->player->setMoralPoint($morale);
    }

    private function givenPlayerHasHealthPoints(int $healthPoints): void
    {
        $this->player->setHealthPoint($healthPoints);
    }

    private function whenCyclePassesForPlayer(): void
    {
        $this->eventService->callEvent(
            event: new PlayerCycleEvent(
                player: $this->player,
                tags: [EventEnum::NEW_CYCLE],
                time: new \DateTime(),
            ),
            name: PlayerCycleEvent::PLAYER_NEW_CYCLE,
        );
    }

    private function thenPlayerShouldBeAlive(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->isAlive());
    }

    private function setupNoIncidents(): void
    {
        $this->daedalus->setDay(0);
    }
}
