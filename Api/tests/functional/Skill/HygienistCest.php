<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HygienistCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::HYGIENIST, $I);
    }

    public function shouldResistPhysicalDisease(FunctionalTester $I): void
    {
        $this->whenITryToCreateDiseaseForPlayer();

        $this->thenPlayerShouldNotHaveDisease($I);
    }

    public function shouldResistMushInfections(FunctionalTester $I): void
    {
        $this->whenITryToInfectPlayer();

        $this->thenPlayerShouldHaveZeroSpores($I);
    }

    public function shouldNotApplyAsMush(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush();

        $this->whenICreateASporeForPlayer();

        $this->thenPlayerShouldHaveSpores(1, $I);
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenITryToCreateDiseaseForPlayer(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::ACID_REFLUX,
            player: $this->player,
            reasons: [],
        );
    }

    private function whenITryToInfectPlayer(): void
    {
        $this->eventService->callEvent(
            event: new PlayerVariableEvent(
                player: $this->player,
                variableName: PlayerVariableEnum::SPORE,
                quantity: 1,
                tags: [ActionEnum::INFECT->value],
                time: new \DateTime(),
            ),
            name: VariableEventInterface::CHANGE_VARIABLE
        );
    }

    private function whenICreateASporeForPlayer(): void
    {
        $this->eventService->callEvent(
            event: new PlayerVariableEvent(
                player: $this->player,
                variableName: PlayerVariableEnum::SPORE,
                quantity: 1,
                tags: [ActionEnum::EXTRACT_SPORE->value],
                time: new \DateTime(),
            ),
            name: VariableEventInterface::CHANGE_VARIABLE
        );
    }

    private function thenPlayerShouldNotHaveDisease(FunctionalTester $I): void
    {
        $I->assertNull($this->player->getMedicalConditionByName(DiseaseEnum::ACID_REFLUX));
    }

    private function thenPlayerShouldHaveZeroSpores(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->player->getSpores());
    }

    private function thenPlayerShouldHaveSpores(int $expectedSpores, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSpores, $this->player->getSpores());
    }
}
