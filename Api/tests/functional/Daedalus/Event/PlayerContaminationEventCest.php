<?php

declare(strict_types=1);

namespace Mush\tests\functional\Daedalus\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerContaminationEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldNotIncrementSporesCreatedStat(FunctionalTester $I): void
    {
        $this->givenPlayerHasSpores(2);
        $this->whenPlayerIsInfected();
        $this->thenSporesCreatedStatShouldNotBeIncremented($I);
    }

    private function givenPlayerHasSpores(int $spores): void
    {
        $this->player->setSpores($spores);
    }

    private function whenPlayerIsInfected(): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::SPORE,
            1,
            [ActionEnum::INFECT->value],
            new \DateTime(),
        );
        $playerModifierEvent->setAuthor($this->player);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function thenSporesCreatedStatShouldNotBeIncremented(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->player->getDaedalus()->getDaedalusInfo()->getDaedalusStatistics()->getSporesCreated());
    }
}
