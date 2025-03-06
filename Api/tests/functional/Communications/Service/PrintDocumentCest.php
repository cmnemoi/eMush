<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Disassemble;
use Mush\Action\Actions\ReadBook;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Communications\Service\DecodeXylophDatabaseServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PrintDocumentCest extends AbstractFunctionalTest
{
    private DecodeXylophDatabaseServiceInterface $decodeXylophDatabaseService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RoomLogServiceInterface $roomLogService;
    private StatusServiceInterface $statusService;
    private XylophRepositoryInterface $xylophRepository;

    private GameEquipment $tabulatrix;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->decodeXylophDatabaseService = $I->grabService(DecodeXylophDatabaseServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->roomLogService = $I->grabService(RoomLogServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->xylophRepository = $I->grabService(XylophRepositoryInterface::class);

        $this->createExtraPlace(RoomEnum::TABULATRIX_QUEUE, $I, $this->daedalus);
    }

    public function shouldNotAddEquipmentWithNoTabulatrixInTheRoom(FunctionalTester $I): void
    {
        $initialEquipmentCount = $this->daedalusEquipmentCount();

        $this->givenXylophSendsChefBook($I);

        $this->thenRoomShouldNotHaveChefBook($I);

        $this->thenDaedalusEquipmentCountShouldBe($initialEquipmentCount, $I);

        $this->thenPlayerShouldSeeLackOfTabulatrixLog($I);

        $this->thenOtherPlayerShouldSeeLackOfTabulatrixLog($I);
    }

    public function shouldPrintDocumentAfterFixingTabulatrix(FunctionalTester $I): void
    {
        $this->givenTabulatrixInRoom();

        $this->givenTabulatrixIsBroken();

        $this->givenXylophSendsChefBook($I);

        $this->thenRoomShouldNotHaveChefBook($I);

        $this->whenPlayerFixesTabulatrix($I);

        $this->thenTabulatrixShouldBeFixed($I);

        $this->thenRoomShouldHaveChefBook($I);

        $this->thenPlayerShouldSeeTabulatrixBrokenLog($I);

        $this->thenOtherPlayerShouldSeeTabulatrixBrokenLog($I);

        $this->thenPlayerShouldNotSeeCookXylophDecodedLog($I);
    }

    public function shouldPrintFunctionalChefBook(FunctionalTester $I): void
    {
        $this->givenTabulatrixInRoom();

        $this->givenXylophSendsChefBook($I);

        $this->thenRoomShouldHaveChefBook($I);

        $this->whenPlayerReadsChefBook($I);

        $this->thenShouldHaveSkill(SkillEnum::CHEF, $I);

        $this->thenPlayerShouldSeeCookXylophDecodedLog($I);
    }

    public function shouldNotDuplicateItemWhenTabulatrixFixedAgain(FunctionalTester $I): void
    {
        $this->givenTabulatrixInRoom();

        $this->givenTabulatrixIsBroken();

        $this->givenXylophSendsChefBook($I);

        $this->whenPlayerFixesTabulatrix($I);

        $initialEquipmentCount = $this->playerRoomEquipmentCount();

        $this->givenTabulatrixIsBroken();

        $this->whenPlayerFixesTabulatrix($I);

        $this->thenRoomEquipmentCountShouldBe($initialEquipmentCount, $I);
    }

    public function shouldRemoveQueuedItemsWhenTabulatrixDismantled(FunctionalTester $I): void
    {
        $this->givenTabulatrixInRoom();

        $this->givenTabulatrixIsBroken();

        $this->givenPlayerIsTechnician($I);

        $this->givenXylophSendsChefBook($I);

        $this->thenMageBookExists($I);

        $this->whenPlayerDismantlesTabulatrix($I);

        $this->thenMageBookDoesNotExist($I);
    }

    private function givenXylophSendsChefBook(FunctionalTester $I): void
    {
        $config = $I->grabEntityFromRepository(XylophConfig::class, ['key' => XylophEnum::COOK->toString() . '_default']);
        $xylophEntry = new XylophEntry(
            xylophConfig: $config,
            daedalusId: $this->daedalus->getId(),
        );
        $this->xylophRepository->save($xylophEntry);

        $this->decodeXylophDatabaseService->execute(
            xylophEntry: $xylophEntry,
            player: $this->player,
        );
    }

    private function givenTabulatrixInRoom(): void
    {
        $this->tabulatrix = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::TABULATRIX,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenTabulatrixIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->tabulatrix,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsTechnician(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->player);
    }

    private function whenPlayerFixesTabulatrix(FunctionalTester $I): void
    {
        $repairAction = $I->grabService(Repair::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REPAIR->value . '_percent_12']);
        $actionConfig->setSuccessRate(100);

        $repairAction->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $this->tabulatrix,
            player: $this->player,
            target: $this->tabulatrix
        );
        $repairAction->execute();
    }

    private function whenPlayerReadsChefBook(FunctionalTester $I): void
    {
        $readAction = $I->grabService(ReadBook::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::READ_BOOK]);

        $chefBook = $this->player->getPlace()->getEquipmentByName('apprentron');

        $readAction->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $chefBook,
            player: $this->player,
            target: $chefBook
        );
        $readAction->execute();
    }

    private function whenPlayerDismantlesTabulatrix(FunctionalTester $I): void
    {
        $dismantleAction = $I->grabService(Disassemble::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DISASSEMBLE->value . '_percent_25_cost_3']);
        $actionConfig->setSuccessRate(100);

        $dismantleAction->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $this->tabulatrix,
            player: $this->player,
            target: $this->tabulatrix
        );
        $dismantleAction->execute();
    }

    private function thenDaedalusEquipmentCountShouldBe(int $expectedCount, FunctionalTester $I): void
    {
        $I->assertEquals($expectedCount, $this->daedalusEquipmentCount());
    }

    private function thenRoomShouldNotHaveChefBook(FunctionalTester $I): void
    {
        $I->assertNull($this->player->getPlace()->getEquipmentByName('apprentron'));
    }

    private function thenRoomShouldHaveChefBook(FunctionalTester $I): void
    {
        $I->assertNotNull($this->player->getPlace()->getEquipmentByName('apprentron'));
    }

    private function thenShouldHaveSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasSkill($skill));
    }

    private function thenTabulatrixShouldBeFixed(FunctionalTester $I): void
    {
        $I->assertFalse($this->tabulatrix->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    private function thenRoomEquipmentCountShouldBe(int $expectedCount, FunctionalTester $I): void
    {
        $I->assertCount($expectedCount, $this->player->getPlace()->getEquipments());
    }

    private function thenMageBookExists(FunctionalTester $I): void
    {
        $I->assertTrue($this->doesMageBookExist());
    }

    private function thenMageBookDoesNotExist(FunctionalTester $I): void
    {
        $I->assertFalse($this->doesMageBookExist());
    }

    private function daedalusEquipmentCount(): int
    {
        $equipmentCount = 0;
        foreach ($this->daedalus->getPlaces() as $place) {
            $equipmentCount += $place->GetEquipments()->count();
        }

        return $equipmentCount;
    }

    private function playerRoomEquipmentCount(): int
    {
        return $this->player->getPlace()->getEquipments()->count();
    }

    private function doesMageBookExist(): bool
    {
        foreach ($this->daedalus->getPlaces() as $place) {
            if ($place->getEquipmentByName('apprentron')) {
                return true;
            }
        }

        return false;
    }

    private function thenPlayerShouldSeeTabulatrixBrokenLog(FunctionalTester $I)
    {
        $I->assertNotEmpty(
            $this->roomLogService->getRoomLog($this->player)->filter(
                static fn (RoomLog $log) => $log->getLog() === 'xyloph_decoded_tabulatrix_broken'
            )->toArray()
        );
    }

    private function thenOtherPlayerShouldSeeTabulatrixBrokenLog(FunctionalTester $I)
    {
        $I->assertNotEmpty(
            $this->roomLogService->getRoomLog($this->player2)->filter(
                static fn (RoomLog $log) => $log->getLog() === 'xyloph_decoded_tabulatrix_broken'
            )->toArray()
        );
    }

    private function thenPlayerShouldSeeLackOfTabulatrixLog(FunctionalTester $I)
    {
        $I->assertNotEmpty(
            $this->roomLogService->getRoomLog($this->player)->filter(
                static fn (RoomLog $log) => $log->getLog() === 'xyloph_decoded_tabulatrix_none'
            )->toArray()
        );
    }

    private function thenOtherPlayerShouldSeeLackOfTabulatrixLog(FunctionalTester $I)
    {
        $I->assertNotEmpty(
            $this->roomLogService->getRoomLog($this->player2)->filter(
                static fn (RoomLog $log) => $log->getLog() === 'xyloph_decoded_tabulatrix_none'
            )->toArray()
        );
    }

    private function thenPlayerShouldSeeCookXylophDecodedLog(FunctionalTester $I)
    {
        $I->assertNotEmpty(
            $this->roomLogService->getRoomLog($this->player)->filter(
                static fn (RoomLog $log) => $log->getLog() === 'xyloph_decoded_cook'
            )->toArray()
        );
    }

    private function thenPlayerShouldNotSeeCookXylophDecodedLog(FunctionalTester $I)
    {
        $I->assertEmpty(
            $this->roomLogService->getRoomLog($this->player)->filter(
                static fn (RoomLog $log) => $log->getLog() === 'xyloph_decoded_cook'
            )->toArray()
        );
    }
}
