<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Event;

use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DiseaseSubscriberCest extends AbstractFunctionalTest
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function testShouldGiveCritHaemorrhageWhenMashedLegs(FunctionalTester $I): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            InjuryEnum::MASHED_LEGS->toString(),
            $this->player,
        );

        $I->assertNotNull($this->player->getMedicalConditionByName(InjuryEnum::CRITICAL_HAEMORRHAGE->toString()));
    }
}
