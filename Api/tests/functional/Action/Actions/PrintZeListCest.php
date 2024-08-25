<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\PrintZeList;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

final class PrintZeListCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private PrintZeList $printZeList;

    private AddSkillToPlayerService $addSkillToPlayer;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameEquipment $tabulatrix;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PRINT_ZE_LIST]);
        $this->printZeList = $I->grabService(PrintZeList::class);
        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenSomeExtraPlayersAreCreated($I);
        $this->givenATabulatrixInTheRoom();
        $this->givenKuanTiIsAlphaMush();
    }

    public function shouldNotBeVisibleIfPlayerIsNotATracker(FunctionalTester $I): void
    {
        $this->whenChunTriesToPrintZeList();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldCreateADocument(FunctionalTester $I): void
    {
        $this->givenChunIsATracker();

        $this->whenChunPrintsZeList();

        $this->thenDocumentShouldBeCreated($I);
    }

    public function shouldCreateAPublicLog(FunctionalTester $I): void
    {
        $this->givenChunIsATracker();

        $this->whenChunPrintsZeList();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "La tabulatrice émet un grondement étonnant. Une perforation est en cours...",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::TABULATRIX_PRINTS,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I
        );
    }

    public function zeListShouldContainEightNamesAtDayOne(FunctionalTester $I): void
    {
        $this->givenChunIsATracker();

        $this->whenChunPrintsZeList();

        $this->thenZeListShouldContainNumberOfNames(8, $I);
    }

    public function zeListShouldContainThreeNamesAtDaySix(FunctionalTester $I): void
    {   
        $this->givenDaedalusHasBeenCreatedDaysAgo(5);

        $this->givenChunIsATracker();

        $this->whenChunPrintsZeList();

        $this->thenZeListShouldContainNumberOfNames(3, $I);
    }

    public function zeListShouldContainAtLeastOneAlphaMush(FunctionalTester $I): void
    {   
        $this->givenDaedalusHasBeenCreatedDaysAgo(8);

        $this->givenChunIsATracker();

        $this->whenChunPrintsZeList();

        $this->thenZeListShouldContainPlayerName("Kuan Ti", $I);
    }

    public function shouldNotBeExecutableIfTabulatrixIsBroken(FunctionalTester $I): void
    {
        $this->givenChunIsATracker();

        $this->givenTabulatrixIsBroken();

        $this->whenChunTriesToPrintZeList();

        $this->thenActionShouldNotBeExecutableWithMessage(
            ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
            $I
        );
    }

    public function shouldNotBeExecutableIfAlreadyDone(FunctionalTester $I): void
    {
        $this->givenChunIsATracker();

        $this->givenChunPrintsZeList();

        $this->whenChunTriesToPrintZeList();

        $this->thenActionShouldNotBeExecutableWithMessage(
            ActionImpossibleCauseEnum::LIST_ALREADY_PRINTED,
            $I
        );
    }

    public function shouldNotBeExecutableIfThereIsNoMushAlive(FunctionalTester $I): void
    {
        $this->givenChunIsATracker();

        $this->givenAllMushAreDead();

        $this->whenChunTriesToPrintZeList();

        $this->thenActionShouldNotBeExecutableWithMessage(
            ActionImpossibleCauseEnum::LIST_NO_MUSH,
            $I
        );
    }

    private function givenSomeExtraPlayersAreCreated(FunctionalTester $I): void
    {
        $players = [CharacterEnum::CHAO, CharacterEnum::TERRENCE, CharacterEnum::JANICE, CharacterEnum::ELEESHA, CharacterEnum::STEPHEN, CharacterEnum::FINOLA, CharacterEnum::RALUCA];
        foreach ($players as $player) {
            $this->addPlayerByCharacter($I, $this->daedalus, $player);
        }
    }

    private function givenATabulatrixInTheRoom(): void
    {
        $this->tabulatrix = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::TABULATRIX,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsATracker(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::TRACKER, $this->chun);
    }

    private function givenDaedalusHasBeenCreatedDaysAgo(int $days): void
    {
        $this->daedalus->setCreatedAt(new \DateTime("-$days days"));
    }

    private function givenKuanTiIsAlphaMush(): void
    {   
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::ALPHA_MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenTabulatrixIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->tabulatrix,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunPrintsZeList(): void
    {
        $this->whenChunPrintsZeList();
    }

    private function givenAllMushAreDead(): void
    {
        $deathEvent = new PlayerEvent(
            player: $this->kuanTi,
            tags: [EndCauseEnum::QUARANTINE],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);
    }

    private function whenChunTriesToPrintZeList(): void
    {
        $this->printZeList->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->tabulatrix,
            player: $this->chun,
            target: $this->tabulatrix,
        );
    }

    private function whenChunPrintsZeList(): void
    {
        $this->whenChunTriesToPrintZeList();
        $this->printZeList->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->printZeList->isVisible());
    }

    private function thenDocumentShouldBeCreated(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasEquipmentByName(ItemEnum::DOCUMENT));
    }

    private function thenZeListShouldContainNumberOfNames(int $numberOfNames, FunctionalTester $I): void
    {
        $zeList = $this->chun->getEquipmentByName(ItemEnum::DOCUMENT);

        // replace "et" by ", " to count the names
        $zeListContent = str_replace(' et ', ', ', $zeList->getStatusByNameOrThrow(EquipmentStatusEnum::DOCUMENT_CONTENT)->getContent());

        $names = explode(', ', $zeListContent);
        $I->assertCount($numberOfNames, $names);
    }

    private function thenZeListShouldContainPlayerName(string $playerName, FunctionalTester $I): void
    {
        $zeList = $this->chun->getEquipmentByName(ItemEnum::DOCUMENT);

        $I->assertStringContainsString($playerName, $zeList->getStatusByNameOrThrow(EquipmentStatusEnum::DOCUMENT_CONTENT)->getContent());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $cause, FunctionalTester $I): void
    {
        $I->assertEquals($cause, $this->printZeList->cannotExecuteReason());
    }
}