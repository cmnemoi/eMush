<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Normalizer\RoomLogNormalizer;
use Mush\RoomLog\Service\RoomLogService;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TrackerCest extends AbstractFunctionalTest
{
    private AddSkillToPlayerService $addSkillToPlayer;
    private RoomLogService $roomLogService;
    private RoomLogNormalizer $roomLogNormalizer;

    private RoomLog $log;
    private RoomLogCollection $chunLogs;

    private ActionConfig $moveActionConfig;
    private Move $moveAction;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->roomLogService = $I->grabService(RoomLogService::class);
        $this->roomLogNormalizer = $I->grabService(RoomLogNormalizer::class);
        $this->moveActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MOVE]);
        $this->moveAction = $I->grabService(Move::class);

        $this->givenChunIsATracker();
    }

    public function shouldSeeLogsFromTwoDaysAgo(FunctionalTester $I): void
    {
        $this->givenALogFromTwoDaysAgo();

        $this->whenChunReadsTheLogs();

        $this->thenChunShouldBeAbleToSeeTheLog($I);
    }

    public function shouldSeeDirectionOfExitLog(FunctionalTester $I): void
    {
        $door = $this->givenADoorToFrontCorridor($I);

        $this->givenKuanTiMovesToFrontCorridor($door);

        $this->whenChunReadsTheLogs();

        $this->thenChunShouldSeeExitLog($I);
    }

    public function shouldSeeDirectionOfEnterLog(FunctionalTester $I): void
    {
        $door = $this->givenADoorToFrontCorridor($I);

        $this->givenKuanTiMovesToFrontCorridor($door);

        $this->givenChunGoesToTheFrontCorridor($door);

        $this->whenChunReadsTheLogs();

        $this->thenChunShouldSeeEnterLog($I);
    }

    private function givenChunIsATracker(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::TRACKER, $this->chun);
    }

    private function givenADoorToFrontCorridor(FunctionalTester $I): Door
    {
        $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $door = Door::createFromRooms($this->chun->getPlace(), $frontCorridor);
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));

        return $door;
    }

    private function givenALogFromTwoDaysAgo(): void
    {
        $this->log = $this->roomLogService->createLog(
            logKey: LogEnum::AWAKEN,
            place: $this->chun->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'event_log',
            player: $this->chun,
            dateTime: new \DateTime('-2 days'),
        );
    }

    private function givenKuanTiMovesToFrontCorridor($door): void
    {
        $this->moveAction->loadParameters(
            actionConfig: $this->moveActionConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door,
        );
        $this->moveAction->execute();
    }

    private function givenChunGoesToTheFrontCorridor($door): void
    {
        $this->moveAction->loadParameters(
            actionConfig: $this->moveActionConfig,
            actionProvider: $door,
            player: $this->chun,
            target: $door,
        );
        $this->moveAction->execute();
    }

    private function whenChunReadsTheLogs(): void
    {
        $this->chunLogs = $this->roomLogService->getRoomLog($this->chun);
    }

    private function thenChunShouldBeAbleToSeeTheLog(FunctionalTester $I): void
    {
        $I->assertNotEmpty($this->chunLogs->filter(fn (RoomLog $log) => $log->getId() === $this->log->getId()));
    }

    private function thenChunShouldSeeExitLog(FunctionalTester $I): void
    {
        $chunLogs = $this->roomLogNormalizer->normalize($this->chunLogs, null, ['currentPlayer' => $this->chun])[$this->daedalus->getDay()][$this->daedalus->getCycle()];

        $I->assertContains(
            '**Kuan Ti** est sorti dans le Couloir Avant.',
            array_map(static fn ($log) => $log['log'], $chunLogs),
        );
    }

    private function thenChunShouldSeeEnterLog(FunctionalTester $I): void
    {
        $chunLogs = $this->roomLogNormalizer->normalize($this->chunLogs, null, ['currentPlayer' => $this->chun])[$this->daedalus->getDay()][$this->daedalus->getCycle()];

        $I->assertContains(
            '**Kuan Ti** est entré depuis le Laboratoire.',
            array_map(static fn ($log) => $log['log'], $chunLogs),
        );
    }
}
