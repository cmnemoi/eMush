<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\SearchForMushGenome;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class SearchForMushGenomeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private SearchForMushGenome $searchForMushGenome;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private LinkWithSolRepositoryInterface $linkWithSolRepository;
    private StatusServiceInterface $statusService;
    private GameEquipment $commsCenter;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SEARCH_FOR_MUSH_GENOME->value]);
        $this->searchForMushGenome = $I->grabService(SearchForMushGenome::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenPlaceHasCommsCenter();
    }

    public function shouldNotBeExecutableIfLinkWithSolIsNotEstablished(FunctionalTester $I): void
    {
        $this->givenPlayerIsCommsOfficer();

        $this->whenPlayerSearchesForMushGenome();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::LINK_WITH_SOL_NOT_ESTABLISHED);
    }

    public function shouldNotBeExecutableIfPlayerIsNotCommsOfficer(FunctionalTester $I): void
    {
        $this->givenLinkWithSolIsEstablished();

        $this->whenPlayerSearchesForMushGenome();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::COMS_NOT_OFFICER);
    }

    public function shouldNotBeExecutableIfPlayerIsDirty(FunctionalTester $I): void
    {
        $this->givenLinkWithSolIsEstablished();
        $this->givenPlayerIsCommsOfficer();
        $this->givenPlayerIsDirty();

        $this->whenPlayerSearchesForMushGenome();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::DIRTY_RESTRICTION);
    }

    public function shouldSpawnMushGenomeDiskOnSuccess(FunctionalTester $I): void
    {
        $this->givenLinkWithSolIsEstablished();
        $this->givenPlayerIsCommsOfficer();

        $this->givenActionSuccessRateIs(100);

        $this->whenPlayerSearchesForMushGenome();

        $this->thenPlaceShouldHaveMushGenomeDisk($I);
    }

    public function shouldPrintPrivateLogOnFail(FunctionalTester $I): void
    {
        $this->givenLinkWithSolIsEstablished();
        $this->givenPlayerIsCommsOfficer();

        $this->givenActionSuccessRateIs(0);

        $this->whenPlayerSearchesForMushGenome();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Accès à la base de données de Xyloph refusée. Rien à en tirer... Saleté d\'admin de daube.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: ActionLogEnum::SEARCH_FOR_MUSH_GENOME_FAIL,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );
    }

    public function shouldPrintPrivateLogOnSuccess(FunctionalTester $I): void
    {
        $this->givenLinkWithSolIsEstablished();
        $this->givenPlayerIsCommsOfficer();

        $this->givenActionSuccessRateIs(100);

        $this->whenPlayerSearchesForMushGenome();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Accès à la base de données de Xyloph acceptée. Ce disque regroupe des informations cruciales sur le génome Mush rassemblée par Ian Soulton juste avant le départ du Daedalus.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: ActionLogEnum::SEARCH_FOR_MUSH_GENOME_SUCCESS,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );
    }

    public function shouldNotBeVisibleIfGenomeDiskAlreadyFound(FunctionalTester $I): void
    {
        $this->givenLinkWithSolIsEstablished();
        $this->givenPlayerIsCommsOfficer();

        $this->givenActionSuccessRateIs(100);

        $this->givenPlayerSearchesForMushGenome();

        $this->whenPlayerTriesToSearchForMushGenomeAgain();

        $this->thenActionShouldNotBeVisible($I);
    }

    private function givenPlaceHasCommsCenter(): void
    {
        $this->commsCenter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMUNICATION_CENTER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsCommsOfficer(): void
    {
        $this->player->addTitle(TitleEnum::COM_MANAGER);
    }

    private function givenActionSuccessRateIs(int $successRate): void
    {
        $this->actionConfig->setSuccessRate($successRate);
    }

    private function givenPlayerSearchesForMushGenome(): void
    {
        $this->whenPlayerSearchesForMushGenome();
    }

    private function whenPlayerSearchesForMushGenome(): void
    {
        $this->searchForMushGenome->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
        );
        $this->searchForMushGenome->execute();
    }

    private function whenPlayerTriesToSearchForMushGenomeAgain(): void
    {
        $this->searchForMushGenome->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
        );
    }

    private function thenPlaceShouldHaveMushGenomeDisk(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->getPlace()->hasEquipmentByName(ItemEnum::MUSH_GENOME_DISK));
    }

    private function thenActionShouldNotBeExecutableWithMessage(FunctionalTester $I, string $message): void
    {
        $I->assertEquals($message, $this->searchForMushGenome->cannotExecuteReason());
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->searchForMushGenome->isVisible());
    }

    private function givenLinkWithSolIsEstablished(): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        $linkWithSol->establish();
    }

    private function givenPlayerIsDirty(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }
}
