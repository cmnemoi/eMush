<?php

namespace Mush\Tests\functional\Place\Event;

use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Event\PlaceCycleEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlaceEventCest extends AbstractFunctionalTest
{
    private ChooseSkillUseCase $chooseSkillUseCase;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function logisticExpertGiveActionPointToASingleOtherPlayer(FunctionalTester $I): void
    {
        // given paola is a logistic expert
        $paola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::LOGISTICS_EXPERT, $paola));

        // given all players in the room have 10 action points
        $this->player1->setActionPoint(10);
        $this->player2->setActionPoint(10);
        $paola->setActionPoint(10);

        // when cycle change is triggered
        $cycleEvent = new PlaceCycleEvent($this->player1->getPlace(), [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, PlaceCycleEvent::PLACE_NEW_CYCLE);

        // then Paola should remain at 10 AP and one of the other player should have received 1 AP
        $I->assertEquals(21, $this->player1->getActionPoint() + $this->player2->getActionPoint());
        $I->assertEquals(10, $paola->getActionPoint());

        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player1->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'log' => PlayerModifierLogEnum::LOGISTIC_LOG,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }
}
