<?php

namespace Mush\Tests\functional\Place\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class LogisticsExpertCest extends AbstractFunctionalTest
{
    private AddSkillToPlayerService $addSkillToPlayerService;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->addSkillToPlayerService = $I->grabService(AddSkillToPlayerService::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldGiveActionPointToASingleOtherPlayer(FunctionalTester $I): void
    {
        // setup no incidents to avoid false positive due to panic crisis
        $this->daedalus->setDay(0);

        // given paola is a logistic expert
        $paola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::LOGISTICS_EXPERT, $paola));

        // given all players in the room have 10 action points
        $this->player1->setActionPoint(10);
        $this->player2->setActionPoint(10);
        $paola->setActionPoint(10);

        // when cycle change is triggered
        $cycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Paola should have 11 AP (10 + 1 from cycle change)
        $I->assertEquals(11, $paola->getActionPoint());

        // then one player should habe 11 AP (10 + 1 from cycle change) and the other one should have 12 AP (10 + 1 from cycle change + 1 from Paola) = 23 AP
        $I->assertEquals(23, $this->player1->getActionPoint() + $this->player2->getActionPoint());

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

    public function multipleLogisticsExpertShouldGiveActionPointToMultiplePlayers(FunctionalTester $I): void
    {
        // setup no incidents to avoid false positive due to panic crisis
        $this->daedalus->setDay(0);

        // given Chun and KT are logistic experts
        $this->addSkillToPlayerService->execute(SkillEnum::LOGISTICS_EXPERT, $this->chun);
        $this->addSkillToPlayerService->execute(SkillEnum::LOGISTICS_EXPERT, $this->kuanTi);

        // given KT and Chun has 10 AP
        $this->chun->setActionPoint(10);
        $this->kuanTi->setActionPoint(10);

        // when cycle change is triggered
        $cycleEvent = new DaedalusCycleEvent($this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        // then Chun and KT should have 12 AP (10 + 1 from cycle change + 1 from the other logistic expert)
        $I->assertEquals(12, $this->chun->getActionPoint());
        $I->assertEquals(12, $this->kuanTi->getActionPoint());

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
}
