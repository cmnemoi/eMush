<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\MakeSick;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;
use Mush\User\Entity\User;

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
        $this->addSkillToPlayer(SkillEnum::BACTEROPHILIAC, $I);
    }

    public function testMakeSick(FunctionalTester $I)
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseConfig);

        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(ActionEnum::MAKE_SICK->value)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($diseaseCause);

        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setDiseaseCauseConfig(new ArrayCollection([$diseaseCause]))
            ->setDiseaseConfig(new ArrayCollection([$diseaseConfig]))
            ->setDaedalusConfig($daedalusConfig);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $action = new ActionConfig();
        $action
            ->setActionName(ActionEnum::MAKE_SICK)
            ->setRange(ActionRangeEnum::PLAYER)
            ->setDisplayHolder(ActionHolderEnum::OTHER_PLAYER)
            ->setActionCost(1)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::COVERT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'actionConfigs' => new ArrayCollection([$action]),
        ]);

        /** @var Player $mushPlayer */
        $mushPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
        ]);
        $mushPlayer->setPlayerVariables($characterConfig);
        $mushPlayer
            ->setActionPoint(2);

        /** @var User $user */
        $user = $I->have(User::class);
        $mushPlayerInfo = new PlayerInfo($mushPlayer, $user, $characterConfig);

        $I->haveInRepository($mushPlayerInfo);
        $mushPlayer->setPlayerInfo($mushPlayerInfo);
        $I->refreshEntities($mushPlayer);

        $mushConfig = new StatusConfig();
        $mushConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($mushConfig);
        $mushStatus = new Status($mushPlayer, $mushConfig);
        $I->haveInRepository($mushStatus);

        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $targetPlayer->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($targetPlayer, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $targetPlayer->setPlayerInfo($playerInfo);
        $I->refreshEntities($targetPlayer);

        $this->makeSickAction->loadParameters(
            actionConfig: $action,
            actionProvider: $mushPlayer,
            player: $mushPlayer,
            target: $targetPlayer
        );

        $this->makeSickAction->execute();

        $I->assertEquals(1, $mushPlayer->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $mushPlayer->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::MAKE_SICK,
            'visibility' => VisibilityEnum::COVERT,
        ]);

        $I->seeInRepository(PlayerDisease::class, [
            'player' => $targetPlayer->getId(),
            'status' => DiseaseStatusEnum::INCUBATING,
            'diseaseConfig' => $diseaseConfig,
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
        $diseaseCauseConfig->setDiseases([DiseaseEnum::FLU => 1]);
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
            expectedRoomLog: 'DRIIIIIIIIIIIIIIIIIIIIIIIIIINNNNNGGGGG!!!!',
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
