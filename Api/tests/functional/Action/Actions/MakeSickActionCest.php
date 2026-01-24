<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\MakeSick;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class MakeSickActionCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private MakeSick $makeSickAction;

    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MAKE_SICK->value]);
        $this->makeSickAction = $I->grabService(MakeSick::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::BACTEROPHILIAC, $I, $this->player);
    }

    public function testMakeSick(FunctionalTester $I)
    {
        $this->givenMakeSickOnlyGivesFlu($I);

        $this->whenChunMakesSickKuanTi();

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player1->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::MAKE_SICK,
            'visibility' => VisibilityEnum::COVERT,
        ]);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $this->player2->getId(),
            'status' => DiseaseStatusEnum::INCUBATING,
            'diseaseConfig' => $I->grabEntityFromRepository(DiseaseConfig::class, ['diseaseName' => DiseaseEnum::FLU->toString()]),
        ]);
    }

    public function shouldMakeMycoAlarmRing(FunctionalTester $I): void
    {
        $this->givenMycoAlarmInRoom();

        $this->whenChunMakesSickKuanTi();

        $this->thenMycoAlarmPrintsPublicLog($I);
    }

    public function shouldNotModifyPlayerHealthAfterConversion(FunctionalTester $I): void
    {
        $this->givenMakeSickOnlyGivesFlu($I);

        $this->givenChunMakesSickKuanTi();

        $this->givenKuanTiTurnsIntoMush();

        $this->whenACyclePassesForKuanTi();

        $this->thenKuanTiShouldHaveMaxHealth($I, 14);
    }

    private function givenMycoAlarmInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MYCO_ALARM,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunMakesSickKuanTi(): void
    {
        $this->whenChunMakesSickKuanTi();
    }

    private function givenKuanTiTurnsIntoMush(): void
    {
        $this->eventService->callEvent(
            event: new PlayerEvent(player: $this->kuanTi, tags: [], time: new \DateTime()),
            name: PlayerEvent::CONVERSION_PLAYER,
        );
    }

    private function givenMakeSickOnlyGivesFlu(FunctionalTester $I): void
    {
        $diseaseCauseConfig = $I->grabEntityFromRepository(DiseaseCauseConfig::class, ['causeName' => ActionEnum::MAKE_SICK->toString()]);
        $diseaseCauseConfig->setDiseases([DiseaseEnum::FLU->toString() => 1]);
    }

    private function whenChunMakesSickKuanTi(): void
    {
        $this->makeSickAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->makeSickAction->execute();
    }

    private function whenACyclePassesForKuanTi(): void
    {
        $this->eventService->callEvent(
            event: new PlayerCycleEvent(player: $this->kuanTi, tags: [EventEnum::NEW_CYCLE], time: new \DateTime()),
            name: PlayerCycleEvent::PLAYER_NEW_CYCLE
        );
    }

    private function thenMycoAlarmPrintsPublicLog(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ':mycoalarm: DRIIIIIIIIIIIIIIIIIIIIIIIIIINNNNNGGGGG!!!!',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: LogEnum::MYCO_ALARM_RING,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    private function thenKuanTiShouldHaveMaxHealth(FunctionalTester $I, int $expectedHealth): void
    {
        $I->assertEquals($expectedHealth, $this->kuanTi->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->getMaxValue(), 'Kuan Ti max health should be ' . $expectedHealth);
    }
}
