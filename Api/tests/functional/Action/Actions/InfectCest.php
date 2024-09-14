<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Infect;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class InfectCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Infect $infect;
    private EventServiceInterface $eventService;
    private PlayerDiseaseService $playerDiseaseService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::INFECT->value]);
        $this->infect = $I->grabService(Infect::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseService::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenKuanTiIsMush();
    }

    public function infectorShouldBeAbleToInfectTwiceADay(FunctionalTester $I)
    {
        $this->addSkillToPlayer(SkillEnum::INFECTOR, $I, $this->kuanTi);

        $this->givenKuanTiInfectsPlayer();

        $this->whenKuanTiTriesToInfect();

        $this->thenActionShouldBeExecutable($I);
    }

    public function dayChangeShouldMakeAbleToInfectAgain(FunctionalTester $I): void
    {
        $this->givenKuanTiInfectsPlayer();

        $this->whenANewDayPasses();

        $this->thenActionShouldBeExecutable($I);
    }

    public function mushAllergyShouldRemoveHealthPointsToTargetPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerHasMushAllergy();

        $this->givenPlayerHasHealthPoints(10);

        $this->whenKuanTiInfectsPlayer();

        $this->thenPlayerShouldHaveHealthPoints(6, $I);
    }

    private function givenKuanTiIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
        $this->kuanTi->setSpores(2);
    }

    private function givenKuanTiInfectsPlayer(): void
    {
        $this->infect->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->player,
        );
        $this->infect->execute();
    }

    private function givenPlayerHasMushAllergy(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::MUSH_ALLERGY,
            player: $this->player,
            reasons: [],
        );
    }

    private function givenPlayerHasHealthPoints(int $healthPoints): void
    {
        $this->player->setHealthPoint($healthPoints);
    }

    private function whenKuanTiTriesToInfect(): void
    {
        $this->infect->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->player,
        );
    }

    private function whenKuanTiInfectsPlayer(): void
    {
        $this->infect->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->player,
        );
        $this->infect->execute();
    }

    private function whenANewDayPasses(): void
    {
        $this->eventService->callEvent(
            event: new PlayerCycleEvent(
                player: $this->kuanTi,
                tags: [EventEnum::NEW_DAY],
                time: new \DateTime()
            ),
            name: PlayerCycleEvent::PLAYER_NEW_CYCLE,
        );
    }

    private function thenActionShouldBeExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->infect->cannotExecuteReason());
    }

    private function thenPlayerShouldHaveHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($healthPoints, $this->player->getHealthPoint());
    }
}
