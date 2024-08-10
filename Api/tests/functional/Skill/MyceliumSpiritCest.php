<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MyceliumSpiritCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->givenPlayerHasMyceliumSpirit($I);
    }

    public function shouldAddOneAvailableSpore(FunctionalTester $I): void {}

    public function shouldNotWorkIfHolderIsDead(FunctionalTester $I): void
    {
        $this->givenPlayerIsDead();

        $this->thenMaximumAvailableSporeShouldBe(4, $I);
    }

    private function givenPlayerHasMyceliumSpirit(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MYCELIUM_SPIRIT, $I);
    }

    private function givenPlayerIsDead(): void
    {
        $event = new PlayerEvent(
            player: $this->player,
            tags: [EndCauseEnum::QUARANTINE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, PlayerEvent::DEATH_PLAYER);
    }

    private function thenMaximumAvailableSporeShouldBe(int $expectedSporeNb, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSporeNb, $this->daedalus->getVariableByName(DaedalusVariableEnum::SPORE)->getMaxValue());
    }
}
