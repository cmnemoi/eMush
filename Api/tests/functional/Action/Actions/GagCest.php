<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\Gag;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class GagCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Gag $gag;
    private DeleteEquipmentServiceInterface $deleteEquipmentService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::GAG->value]);
        $this->gag = $I->grabService(Gag::class);
        $this->deleteEquipmentService = $I->grabService(DeleteEquipmentServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);

        $this->givenPlayerHasDuctTape();
    }

    public function shouldNotBeVisibleIfPlayerDoesNotHaveDuctTape(FunctionalTester $I): void
    {
        $this->givenPlayerDoesNotHaveDuctTape();

        $this->whenPlayerTriesToGagPlayer2();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeVisibleIfTargetAlreadyGagged(FunctionalTester $I): void
    {
        $this->givenPlayer2IsGagged();

        $this->whenPlayerTriesToGagPlayer2();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableOnPlanet(FunctionalTester $I): void
    {
        $this->givenPlayersAreOnPlanet();

        $this->whenPlayerTriesToGagPlayer2();

        $this->thenActionShouldNotBeExecutable(
            message: ActionImpossibleCauseEnum::ON_PLANET,
            I: $I,
        );
    }

    public function shouldGagTargetPlayer(FunctionalTester $I): void
    {
        $this->whenPlayerGagsPlayer2();

        $this->thenPlayer2ShouldBeGagged($I);
    }

    public function shouldIncrementPendingStatistic(FunctionalTester $I): void
    {
        $this->whenPlayerGagsPlayer2();

        $statistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            StatisticEnum::GAGGED,
            $this->player2->getUser()->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );
        $I->assertEquals(1, $statistic?->getCount());
    }

    private function givenPlayerHasDuctTape(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::DUCT_TAPE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerDoesNotHaveDuctTape(): void
    {
        $ductTape = $this->player->getEquipmentByName(ToolItemEnum::DUCT_TAPE);
        if ($ductTape !== null) {
            $this->deleteEquipmentService->execute($ductTape);
        }
    }

    private function givenPlayer2IsGagged(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GAGGED,
            holder: $this->player2,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayersAreOnPlanet(): void
    {
        foreach ($this->players as $player) {
            $player->changePlace($this->daedalus->getPlanetPlace());
        }
    }

    private function whenPlayerTriesToGagPlayer2(): void
    {
        $this->gag->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
            target: $this->player2,
        );
    }

    private function whenPlayerGagsPlayer2(): void
    {
        $this->whenPlayerTriesToGagPlayer2();
        $this->gag->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->gag->isVisible());
    }

    private function thenActionShouldNotBeExecutable(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->gag->cannotExecuteReason());
    }

    private function thenPlayer2ShouldBeGagged(FunctionalTester $I): void
    {
        $I->assertTrue($this->player2->hasStatus(PlayerStatusEnum::GAGGED));
    }
}
