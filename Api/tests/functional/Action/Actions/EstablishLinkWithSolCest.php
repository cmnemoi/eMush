<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\EstablishLinkWithSol;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepository;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class EstablishLinkWithSolCest extends AbstractFunctionalTest
{
    private CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalus;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private LinkWithSolRepository $linkWithSolRepository;

    private ActionConfig $actionConfig;
    private EstablishLinkWithSol $establishLinkWithSol;

    private GameEquipment $commsCenter;
    private GameEquipment $antenna;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->createLinkWithSolForDaedalus = $I->grabService(CreateLinkWithSolForDaedalusService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepository::class);

        $this->actionConfig = $I->grabEntityFromRepository(
            ActionConfig::class,
            params: ['name' => ActionEnum::ESTABLISH_LINK_WITH_SOL->toString()]
        );
        $this->establishLinkWithSol = $I->grabService(EstablishLinkWithSol::class);

        $this->givenLinkWithSolIsNotEstablished();
        $this->givenAnAntennaInDaedalus();
        $this->givenACommsCenterInChunRoom();
        $this->givenChunIsFocusedOnCommsCenter();
        $this->givenKuanTiIsFocusedOnCommsCenter();
    }

    public function shouldNotBeVisibleIfPLayerIsNotFocusedOnCommsCenter(FunctionalTester $I): void
    {
        $this->givenChunIsNotFocusedOnCommsCenter();

        $this->whenChunTriesToEstablishLinkWithSol();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldIncreaseLinkStrength(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(12);

        $this->whenChunEstablishesLinkWithSol();

        $this->thenLinkStrengthIs($I, 16);
    }

    public function brokenAntennaShouldIncreasesAPCost(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(3);

        $this->givenAntennaIsBroken();

        $this->whenChunEstablishesLinkWithSol();

        $this->thenChunHasActionPoints($I, 0);
    }

    public function shouldEstablishLinkWithSolOnSuccess(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(100);

        $this->whenChunEstablishesLinkWithSol();

        $this->thenLinkIsEstablished($I);
    }

    public function shouldBeExecutableOnePerDay(FunctionalTester $I): void
    {
        $this->givenChunEstablishesLinkWithSol();

        $this->whenChunTriesToEstablishLinkWithSol();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::COMS_ALREADY_ATTEMPTED);
    }

    public function shouldNotBeExecutableIfPlayerIsDirty(FunctionalTester $I): void
    {
        $this->givenChunIsDirty();

        $this->whenChunTriesToEstablishLinkWithSol();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::DIRTY_RESTRICTION);
    }

    public function shouldGiveMoraleToAllCrewWhenSucceedsForTheFirstTime(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(100);

        $this->givenAllPlayersHaveMorale(0);

        $this->whenChunEstablishesLinkWithSol();

        $this->thenAllPlayersShouldHaveMorale(3, $I);
    }

    public function shouldNotGiveMoraleToAllCrewWhenSucceedsForTheSecondTime(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(100);

        $this->givenAllPlayersHaveMorale(0);

        $this->givenChunEstablishesLinkWithSol();

        $this->whenKuanTiEstablishesLinkWithSol();

        $this->thenAllPlayersShouldHaveMorale(3, $I);
    }

    public function radioExpertInRoomShouldGiveBonusToLinkStrength(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(0);

        $this->givenChunIsARadioExpert($I);

        $this->whenKuanTiEstablishesLinkWithSol();

        $this->thenLinkStrengthIs($I, 6);
    }

    public function radioExpertShouldHaveBonusToLinkStrength(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(0);

        $this->givenChunIsARadioExpert($I);

        $this->whenChunEstablishesLinkWithSol();

        $this->thenLinkStrengthIs($I, 8);
    }

    public function shouldCreateANeronAnnouncementWhenSucceedsForTheFirstTime(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(100);

        $this->givenChunEstablishesLinkWithSol();

        $this->thenNeronAnnouncementShouldBeCreatedWithMessage(NeronMessageEnum::SOL_CONTACT, $I);
    }

    public function shouldPrintAPrivateLogOnFailure(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(0);

        $this->whenChunEstablishesLinkWithSol();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "Vous n'avez pas réussi à établir une communication avec le système solaire Sol, mais vous avez **amélioré la qualité du signal**. Vous aurez le droit à un nouvel essai demain.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::ESTBALISH_LINK_WITH_SOL_FAIL,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );
    }

    public function shouldPrintAPrivateLogOnSuccess(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(100);

        $this->whenChunEstablishesLinkWithSol();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous avez réussi à établir une communication entre le Daedalus et le système solaire Sol !',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::ESTBALISH_LINK_WITH_SOL_SUCCESS,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );
    }

    public function spatialWaveRadarProjectShouldDoubleSignalStrength(FunctionalTester $I): void
    {
        $this->givenSpatialWaveRadarProjectIsFinished($I);

        $this->givenLinkWithSolStrengthIs(0);

        $this->whenChunEstablishesLinkWithSol();

        $this->thenLinkStrengthIs($I, 8);
    }

    public function shouldNotBeVisibleIfLinkIsAlreadyEstablished(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(100);

        $this->givenChunEstablishesLinkWithSol();

        $this->whenKuanTiTriesToEstablishLinkWithSol();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldUseITPointsInsteadOfActionPoints(FunctionalTester $I): void
    {
        $this->givenChunHasITPoints(1);
        $this->givenChunHasActionPoints(2);

        $this->whenChunEstablishesLinkWithSol();

        $this->thenChunHasActionPoints($I, 2);
        $this->thenChunHasITPoints(0, $I);
    }

    public function radioExpertInRoomBonusDoNotStack(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(0);
        $this->givenChunIsARadioExpert($I);
        $this->givenJaniceIsARadioExpert($I);

        $this->whenKuanTiEstablishesLinkWithSol();

        $this->thenLinkStrengthIs($I, 6);
    }

    public function shouldRemoveCommunicationsDownAlertOnSuccess(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(100);

        $this->whenChunEstablishesLinkWithSol();

        $this->thenCommsDownAlertIsDeleted($I);
    }

    private function givenSpatialWaveRadarProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::RADAR_TRANS_VOID),
            $this->chun,
            $I
        );
    }

    private function givenACommsCenterInChunRoom(): void
    {
        $this->commsCenter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMUNICATION_CENTER,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsFocusedOnCommsCenter(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->commsCenter,
        );
    }

    private function getLinkWithSol(): LinkWithSol
    {
        return $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
    }

    private function givenLinkWithSolIsNotEstablished(): void
    {
        $this->createLinkWithSolForDaedalus->execute($this->daedalus->getId());
    }

    private function givenLinkWithSolStrengthIs(int $strength): void
    {
        $linkWithSol = $this->getLinkWithSol();
        $linkWithSol->increaseStrength($strength);
    }

    private function givenAnAntennaInDaedalus(): void
    {
        $this->antenna = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::ANTENNA,
            equipmentHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunHasActionPoints(int $quantity): void
    {
        $this->chun->setActionPoint($quantity);
    }

    private function givenAntennaIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->antenna,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenChunEstablishesLinkWithSol(): void
    {
        $this->establishLinkWithSol->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->chun,
            target: $this->commsCenter,
        );
        $this->establishLinkWithSol->execute();
    }

    private function thenLinkStrengthIs(FunctionalTester $I, int $expectedStrength): void
    {
        $I->assertEquals($this->getLinkWithSol()->getStrength(), $expectedStrength, message: "Link strength should be {$expectedStrength}");
    }

    private function thenChunHasActionPoints(FunctionalTester $I, int $quantity): void
    {
        $I->assertEquals($this->chun->getActionPoint(), $quantity, message: "Chun should have {$quantity} action points");
    }

    private function thenLinkIsEstablished(FunctionalTester $I): void
    {
        $I->assertTrue($this->getLinkWithSol()->isEstablished(), message: 'Link should be established');
    }

    private function givenChunIsDirty(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunEstablishesLinkWithSol(): void
    {
        $this->whenChunEstablishesLinkWithSol();
    }

    private function whenChunTriesToEstablishLinkWithSol(): void
    {
        $this->establishLinkWithSol->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->chun,
            target: $this->commsCenter,
        );
    }

    private function thenActionShouldNotBeExecutableWithMessage(FunctionalTester $I, string $message): void
    {
        $I->assertEquals($this->establishLinkWithSol->cannotExecuteReason(), $message, message: "Action should not be executable with message '{$message}'");
    }

    private function givenAllPlayersHaveMorale(int $quantity): void
    {
        foreach ($this->players as $player) {
            $player->setMoralPoint($quantity);
        }
    }

    private function thenAllPlayersShouldHaveMorale(int $quantity, FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            $I->assertEquals($player->getMoralPoint(), $quantity, message: "{$player->getName()} should have {$quantity} morale");
        }
    }

    private function whenKuanTiEstablishesLinkWithSol(): void
    {
        $this->establishLinkWithSol->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->kuanTi,
            target: $this->commsCenter,
        );
        $this->establishLinkWithSol->execute();
    }

    private function thenNeronAnnouncementShouldBeCreatedWithMessage(string $message, FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: Message::class,
            params: [
                'message' => $message,
            ]
        );
    }

    private function givenChunIsARadioExpert(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::RADIO_EXPERT, $I);
    }

    private function givenJaniceIsARadioExpert(FunctionalTester $I): void
    {
        $janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);

        $this->addSkillToPlayer(SkillEnum::RADIO_EXPERT, $I, $janice);
    }

    private function whenKuanTiTriesToEstablishLinkWithSol(): void
    {
        $this->establishLinkWithSol->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->kuanTi,
            target: $this->commsCenter,
        );
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->establishLinkWithSol->isVisible(), message: 'Action should not be visible');
    }

    private function givenChunHasITPoints(int $quantity): void
    {
        /** @var ChargeStatus $itPointsStatus */
        $itPointsStatus = $this->statusService->createStatusFromName(
            statusName: SkillPointsEnum::IT_EXPERT_POINTS->toString(),
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
        $this->statusService->updateCharge(
            chargeStatus: $itPointsStatus,
            delta: $quantity,
            tags: [],
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
    }

    private function thenChunHasITPoints(int $quantity, FunctionalTester $I): void
    {
        $itPointsStatus = $this->chun->getChargeStatusByNameOrThrow(SkillPointsEnum::IT_EXPERT_POINTS->toString());
        $I->assertEquals($itPointsStatus->getCharge(), $quantity, message: "Chun should have {$quantity} IT points, but has {$itPointsStatus->getCharge()}");
    }

    private function thenCommsDownAlertIsDeleted(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(
            entity: Alert::class,
            params: [
                'name' => AlertEnum::COMMUNICATIONS_DOWN,
                'daedalus' => $this->daedalus,
            ]
        );
    }

    private function givenChunIsNotFocusedOnCommsCenter(): void
    {
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->establishLinkWithSol->isVisible(), message: 'Action should not be visible');
    }

    private function givenKuanTiIsFocusedOnCommsCenter(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
            target: $this->commsCenter,
        );
    }
}
