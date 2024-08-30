<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DetachedCrewmemberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->givenPlayerIsADetachedCrewmember($I);
    }

    public function shouldNotLoseMoralePointsWhenCrewmateDies(FunctionalTester $I): void
    {
        $this->givenPlayerHasMoralePoints(10);

        $this->whenCrewmateDies();

        $this->thenPlayerShouldHaveMoralePoints(10, $I);
    }

    private function givenPlayerIsADetachedCrewmember(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::DETACHED_CREWMEMBER, $I);
    }

    private function givenPlayerHasMoralePoints(int $moralePoints): void
    {
        $this->player->setMoralPoint($moralePoints);
    }

    private function whenCrewmateDies(): void
    {
        $deathEvent = new PlayerEvent(
            player: $this->player2,
            tags: [EndCauseEnum::ABANDONED],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);
    }

    private function thenPlayerShouldHaveMoralePoints(int $expectedMoralePoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedMoralePoints, $this->player->getMoralPoint());
    }
}
