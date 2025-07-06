<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Renovate;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RenovateActionCest extends AbstractFunctionalTest
{
    private Renovate $renovateAction;
    private ActionConfig $action;
    private Place $alphaBay2;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraRooms($I, $this->daedalus);

        $this->alphaBay2 = $this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2);

        $this->player1->changePlace($this->alphaBay2);
        $this->kuanTi->changePlace($this->alphaBay2);

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::RENOVATE]);
        $this->renovateAction = $I->grabService(Renovate::class);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testRenovateSuccess(FunctionalTester $I): void
    {
        $pasiphae = $this->givenThereIsABrokenPasiphae();

        $this->givenThereIsAMetalScrap();

        // given the success rate is set to 100
        $this->action->setSuccessRate(100);

        $this->givenTheActionIsLoaded($pasiphae);

        $this->whenTheActionIsExecuted();

        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player2->getPlayerInfo(),
            'log' => ActionLogEnum::RENOVATE_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertFalse($pasiphae->hasStatus(EquipmentStatusEnum::BROKEN));

        $I->assertEquals(1, $this->player2->getPlayerInfo()->getStatistics()->getTechSuccesses());
        $I->assertEquals(0, $this->player2->getPlayerInfo()->getStatistics()->getTechFails());
    }

    public function testRenovateFail(FunctionalTester $I): void
    {
        $pasiphae = $this->givenThereIsA1HPPasiphae();

        $this->givenThereIsAMetalScrap();

        // given the success rate is set to 0
        $this->action->setSuccessRate(0);

        $this->givenTheActionIsLoaded($pasiphae);

        $this->whenTheActionIsExecuted();

        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player2->getPlayerInfo(),
            'log' => ActionLogEnum::RENOVATE_FAIL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);

        $I->assertEquals(0, $this->player2->getPlayerInfo()->getStatistics()->getTechSuccesses());
        $I->assertEquals(1, $this->player2->getPlayerInfo()->getStatistics()->getTechFails());
    }

    public function testRenovateNotVisibleIfPatrolShipNotBrokenAndNotDamaged(FunctionalTester $I): void
    {
        $pasiphae = $this->givenThereIsAFullHPPasiphae();

        $this->givenThereIsAMetalScrap();

        $this->givenTheActionIsLoaded($pasiphae);

        $I->assertFalse($this->renovateAction->isVisible());
    }

    public function testRenovateActionIsVisibleIfPatrolShipIsBroken(FunctionalTester $I): void
    {
        $pasiphae = $this->givenThereIsABrokenPasiphae();

        $this->givenThereIsAMetalScrap();

        $this->givenTheActionIsLoaded($pasiphae);

        $I->assertTrue($pasiphae->hasStatus(EquipmentStatusEnum::BROKEN));
        $I->assertTrue($this->renovateAction->isVisible());
    }

    public function testRenovateActionIsVisibleIfPatrolShipIsDamaged(FunctionalTester $I): void
    {
        $pasiphae = $this->givenThereIsA1HPPasiphae();

        $this->givenThereIsAMetalScrap();

        $this->givenTheActionIsLoaded($pasiphae);

        $I->assertTrue($this->renovateAction->isVisible());
    }

    public function testRenovateNotExecutableIfNoScrapAvailable(FunctionalTester $I): void
    {
        $pasiphae = $this->givenThereIsA1HPPasiphae();

        $this->givenTheActionIsLoaded($pasiphae);

        $I->assertEquals(
            expected: $this->renovateAction->cannotExecuteReason(),
            actual: ActionImpossibleCauseEnum::RENOVATE_LACK_RESSOURCES,
        );
    }

    public function shouldSuccessRateBeDoubledByTechnicianSkill(FunctionalTester $I): void
    {
        $pasiphae = $this->givenThereIsA1HPPasiphae();

        // given KT is a technician
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->kuanTi);

        // given renovate action has a 25% success rate
        $this->action->setSuccessRate(25);

        $this->givenTheActionIsLoaded($pasiphae);

        // then the success rate of the Repair action is boosted to 50%
        $I->assertEquals(50, $this->renovateAction->getSuccessRate());
    }

    public function shouldConsumeEngineerPointWhenRelevant(FunctionalTester $I): void
    {
        $pasiphae = $this->givenThereIsA1HPPasiphae();

        $this->givenThereIsAMetalScrap();

        // given KT is a technician
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->kuanTi);

        // given KT has two Technician points
        $technicianSkill = $this->kuanTi->getSkillByNameOrThrow(SkillEnum::TECHNICIAN);
        $I->assertEquals(
            expected: 2,
            actual: $technicianSkill->getSkillPoints(),
        );

        $this->givenTheActionIsLoaded($pasiphae);

        $this->whenTheActionIsExecuted();

        // then KT should have one Technician point left
        $I->assertEquals(
            expected: 1,
            actual: $technicianSkill->getSkillPoints(),
        );
    }

    private function createExtraRooms(FunctionalTester $I, Daedalus $daedalus): void
    {
        $alphaBay2Config = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::ALPHA_BAY_2]);
        $alphaBay2 = new Place();
        $alphaBay2
            ->setName(RoomEnum::ALPHA_BAY_2)
            ->setType($alphaBay2Config->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($alphaBay2);

        $I->refreshEntities($daedalus);
    }

    private function givenThereIsAFullHPPasiphae(): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PASIPHAE,
            equipmentHolder: $this->alphaBay2,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenThereIsA1HPPasiphae(): GameEquipment
    {
        $pasiphae = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PASIPHAE,
            equipmentHolder: $this->alphaBay2,
            reasons: [],
            time: new \DateTime()
        );

        $this->statusService->updateCharge(
            $pasiphae->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PATROL_SHIP_ARMOR),
            -11,
            [],
            new \DateTime()
        );

        return $pasiphae;
    }

    private function givenThereIsABrokenPasiphae(): GameEquipment
    {
        $pasiphae = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PASIPHAE,
            equipmentHolder: $this->alphaBay2,
            reasons: [],
            time: new \DateTime()
        );

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $pasiphae,
            [],
            new \DateTime(),
        );

        return $pasiphae;
    }

    private function givenThereIsAMetalScrap(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->alphaBay2,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenTheActionIsLoaded(GameEquipment $pasiphae): void
    {
        $this->renovateAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $pasiphae,
            player: $this->kuanTi,
            target: $pasiphae
        );
    }

    private function whenTheActionIsExecuted(): void
    {
        $this->renovateAction->execute();
    }
}
