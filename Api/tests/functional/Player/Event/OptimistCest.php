<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Event;

use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class OptimistCest extends AbstractFunctionalTest
{
    private ChooseSkillUseCase $chooseSkillUseCase;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->givenPlayerIsOptimist($I);
    }

    public function shouldLoseOneLessMoralePointAtDayChange(FunctionalTester $I): void
    {
        $this->givenPlayerHasMoralePoints(10);

        $this->whenADayPasses();

        $this->thenPlayerShouldHaveMoralePoints(9, $I);
    }

    public function shouldPrintAPrivateLog(FunctionalTester $I): void
    {
        $this->whenADayPasses();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Votre compétence **Optimiste** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: PlayerModifierLogEnum::OPTIMIST_WORKED,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    private function givenPlayerIsOptimist(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->addSkillConfig(
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::OPTIMIST])
        );
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::OPTIMIST, $this->player));
    }

    private function givenPlayerHasMoralePoints(int $moralePoints): void
    {
        $this->player->setMoralPoint($moralePoints);
    }

    private function whenADayPasses(): void
    {
        $playerCycleEvent = new PlayerCycleEvent(
            player: $this->player,
            tags: [EventEnum::NEW_DAY],
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerCycleEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
    }

    private function thenPlayerShouldHaveMoralePoints(int $moralePoints, FunctionalTester $I): void
    {
        $I->assertEquals($moralePoints, $this->player->getMoralPoint());
    }
}
