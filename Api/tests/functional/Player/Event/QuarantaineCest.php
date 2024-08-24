<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Event;

use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class QuarantaineCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldNotRemoveMoralePoints(FunctionalTester $I): void
    {
        $this->givenChunHasMoralePoints(10, $I);

        $this->whenKuanTiIsQuarantained($I);

        $this->thenChunHasMoralePoints(10, $I);
    }

    private function givenChunHasMoralePoints(int $moralePoints): void
    {
        $this->chun->setMoralPoint($moralePoints);
    }

    private function whenKuanTiIsQuarantained(): void
    {
        $daethPlayer = new PlayerEvent(
            player: $this->kuanTi,
            tags: [EndCauseEnum::QUARANTINE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($daethPlayer, PlayerEvent::DEATH_PLAYER);
    }

    private function thenChunHasMoralePoints(int $moralePoints, FunctionalTester $I): void
    {
        $I->assertEquals($moralePoints, $this->chun->getMoralPoint());
    }
}
