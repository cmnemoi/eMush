<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerCycleEvent;
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
final class PanicCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;
    private Player $jinSu;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);
        $this->addSkillToPlayer(SkillEnum::PANIC, $I);
    }

    public function shouldGainOneExtraActionPointIfMushControlsMoreThanHalfOfTheCrew(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($this->jinSu);

        $this->givenPlayerIsMush($this->kuanTi);

        $this->givenChunHasActionPoints(0);

        $this->whenACyclePassesForChun();

        // 1AP (base) + 1AP (panic bonus) = 2AP
        $this->thenChunShouldHaveActionPoints(2, $I);
    }

    public function shouldGainOneExtraMovementPointIfMushControlsMoreThanHalfOfTheCrew(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($this->jinSu);

        $this->givenPlayerIsMush($this->kuanTi);

        $this->givenChunHasMovementPoints(0);

        $this->whenACyclePassesForChun();

        // 1MP (base) + 1MP (panic bonus) = 2MP
        $this->thenChunShouldHaveMovementPoints(2, $I);
    }

    public function shouldNotGainExtraActionPointIfMushControlsLessThanHalfOfTheCrew(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($this->jinSu);

        $this->givenChunHasActionPoints(0);

        $this->whenACyclePassesForChun();

        // 1AP (base)
        $this->thenChunShouldHaveActionPoints(1, $I);
    }

    public function shouldNotGainExtraMovementPointIfMushControlsLessThanHalfOfTheCrew(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($this->jinSu);

        $this->givenChunHasMovementPoints(0);

        $this->whenACyclePassesForChun();

        $this->thenChunShouldHaveMovementPoints(1, $I);
    }

    public function shouldPrintPrivateLogsForPanicBonus(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($this->jinSu);

        $this->givenPlayerIsMush($this->kuanTi);

        $this->givenChunHasActionPoints(2);

        $this->whenACyclePassesForChun();

        $this->ISeeTranslatedRoomLogsInRepository(
            expectedRoomLog: 'Votre compétence **Panique** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: PlayerModifierLogEnum::PANIC_WORKED,
                visibility: VisibilityEnum::PRIVATE,
            ),
            number: 2,
            I: $I,
        );
    }

    public function shouldNotWorkIfPlayerIsMush(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush($this->jinSu);

        $this->givenPlayerIsMush($this->kuanTi);

        $this->givenPlayerIsMush($this->chun);

        $this->givenChunHasActionPoints(0);

        $this->whenACyclePassesForChun();

        // 1AP (base)
        $this->thenChunShouldHaveActionPoints(1, $I);
    }

    private function givenPlayerIsMush(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunHasActionPoints(int $actionPoints): void
    {
        $this->chun->setActionPoint($actionPoints);
    }

    private function givenChunHasMovementPoints(int $movementPoints): void
    {
        $this->chun->setMovementPoint($movementPoints);
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

    private function thenChunShouldHaveActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->chun->getActionPoint());
    }

    private function thenChunShouldHaveMovementPoints(int $movementPoints, FunctionalTester $I): void
    {
        $I->assertEquals($movementPoints, $this->chun->getMovementPoint());
    }
}
