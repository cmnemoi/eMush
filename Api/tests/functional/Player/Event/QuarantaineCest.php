<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Event;

use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class QuarantaineCest extends AbstractFunctionalTest
{
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playerService = $I->grabService(PlayerServiceInterface::class);
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
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::QUARANTINE,
            time: new \DateTime(),
        );
    }

    private function thenChunHasMoralePoints(int $moralePoints, FunctionalTester $I): void
    {
        $I->assertEquals($moralePoints, $this->chun->getMoralPoint());
    }
}
