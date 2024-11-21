<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ImmunizedCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::IMMUNIZED,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    public function shouldNotPreventSporeConsumptionForMushPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush();

        $this->givenPlayerHasSpore(1);

        $this->whenPlayerConsumesSpore();

        $this->thenPlayerShouldHaveSpore(0, $I);
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerHasSpore(int $quantity): void
    {
        $this->player->setSpores($quantity);
    }

    private function whenPlayerConsumesSpore(): void
    {
        $this->eventService->callEvent(
            event: new PlayerVariableEvent(
                player: $this->player,
                variableName: PlayerVariableEnum::SPORE,
                quantity: -1,
                tags: [],
                time: new \DateTime()
            ),
            name: VariableEventInterface::CHANGE_VARIABLE
        );
    }

    private function thenPlayerShouldHaveSpore(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->player->getSpores());
    }
}
