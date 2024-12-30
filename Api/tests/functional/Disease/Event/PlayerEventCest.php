<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Event;

use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function testDiseaseModifierTriggersOnPlayerNewCycleEvent(FunctionalTester $I): void
    {
        // Remove initial diseases if any.
        foreach ($this->player->getMedicalConditions() as $condition) {
            $this->playerDiseaseService->removePlayerDisease(
                $condition,
                [],
                new \DateTime(),
                VisibilityEnum::HIDDEN,
                $this->player,
            );
        }
        // given player has a disease
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::REJUVENATION,
            player: $this->player,
            reasons: [],
        );
        $disease->setDiseasePoint(10);

        // given the fitful sleep modifier of the disease always triggers at cycle change
        // note : this modifier removes 1 AP to the player
        /** @var TriggerEventModifierConfig $modifierConfig */
        $modifierConfig = $disease->getDiseaseConfig()->getModifierConfigs()->filter(
            static fn (AbstractModifierConfig $modifierConfig) => $modifierConfig->getModifierName() === ModifierNameEnum::FITFUL_SLEEP
        )->first();
        $modifierConfig->setModifierActivationRequirements([]);

        // given player disease has only the fitful sleep modifier
        $diseaseConfig = $disease->getDiseaseConfig()->setModifierConfigs([$modifierConfig]);
        $I->assertCount(1, $diseaseConfig->getModifierConfigs(), 'Only one config should be taken for this disease.');

        // when player has a new cycle
        $playerEvent = new PlayerEvent(
            player: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        $this->eventService->callEvent($playerEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then I should see a single room log for the modifier
        $roomLog = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'playerInfo' => $this->player->getPlayerInfo(),
                'place' => $this->player->getPlace()->getLogName(),
                'log' => LogEnum::FITFUL_SLEEP,
            ]
        );

        $currentDiseases = $this->player->getMedicalConditions();
        $I->assertCount(1, $currentDiseases);
        $I->assertCount(1, $roomLog, 'Double FITFUL_SLEEP have been dispatched.');

        // the player gains 1 AP (cycle change) and lose 2 AP (disease), so they should have 7 AP
        $I->assertEquals(expected: 7, actual: $playerEvent->getPlayer()->getActionPoint());
    }

    public function testHealedDiseaseDoesNotActOnPlayerNewCycleEvent(FunctionalTester $I): void
    {
        // given player has a disease
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::COLD,
            player: $this->player,
            reasons: [],
        );

        // given the modifier of the disease always triggers at cycle change
        // note : this modifier removes 1 AP to the player
        /** @var TriggerEventModifierConfig $modifierConfig */
        $modifierConfig = $disease->getDiseaseConfig()->getModifierConfigs()->first();
        $modifierConfig->setModifierActivationRequirements([]);
        $I->haveInRepository($modifierConfig);

        // given the disease has no longer any disease points, so it heals at next cycle change
        $disease->setDiseasePoint(0);
        $I->haveInRepository($disease);

        // when player has a new cycle
        $playerEvent = new PlayerEvent(
            player: $this->player,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then I should see a healing room log
        $healingRoomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'playerInfo' => $this->player->getPlayerInfo(),
                'place' => $this->player->getPlace()->getLogName(),
                'log' => LogEnum::DISEASE_CURED,
            ]
        );

        // then the healing room correctly print the disease name
        $I->assertEquals(
            DiseaseEnum::COLD,
            $healingRoomLog->getParameters()['disease']
        );

        // then I should not see an AP loss room log
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'playerInfo' => $this->player->getPlayerInfo(),
                'place' => $this->player->getPlace()->getLogName(),
                'log' => PlayerModifierLogEnum::LOSS_ACTION_POINT,
            ]
        );
    }

    public function testDiseaseDoesNotActTwiceOnPlayerNewCycleEvent(FunctionalTester $I): void
    {
        // given player has a disease
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::COLD,
            player: $this->player,
            reasons: [],
        );
        $disease->setDiseasePoint(10);

        // given player has another disease
        $disease2 = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::CHRONIC_VERTIGO,
            player: $this->player,
            reasons: [],
        );
        $disease2->setDiseasePoint(10);

        $initialAP = $this->player->getActionPoint();

        // given the modifier of the disease always triggers at cycle change
        // note : this modifier removes 1 AP to the player
        /** @var TriggerEventModifierConfig $modifierConfig */
        $modifierConfig = $disease->getDiseaseConfig()->getModifierConfigs()->first();
        $modifierConfig->setModifierActivationRequirements([]);
        $I->haveInRepository($modifierConfig);

        // when player has a new cycle
        $playerEvent = new PlayerEvent(
            player: $this->player,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then I should see a room log reporting the AP loss
        $logs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: [
                'playerInfo' => $this->player->getPlayerInfo(),
                'place' => $this->player->getPlace()->getLogName(),
                'log' => PlayerModifierLogEnum::LOSS_ACTION_POINT,
            ]
        );
        $I->assertCount(1, $logs);

        // the player gains 1 AP (cycle change) and lose 1 AP (disease)
        $I->assertEquals($this->player->getActionPoint(), $initialAP);
    }

    public function testDiseaseMakesPlayerDirtyAtCycleChangeEvenWithAnApron(FunctionalTester $I): void
    {
        // given Chun has an apron
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::STAINPROOF_APRON,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime()
        );

        // given Chun has a disease which makes her dirty at cycle change
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::FUNGIC_INFECTION,
            player: $this->chun,
            reasons: [],
        );

        // when player has a new cycle
        $playerEvent = new PlayerEvent(
            player: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then Chun should be dirty
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::DIRTY));
    }

    public function shouldNotTriggerBitingSymptomIfTargetDiesAtCycleChange(FunctionalTester $I): void
    {
        // given Chun has space rabies, so she has the biting symptom
        $spaceRabies = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::SPACE_RABIES,
            player: $this->chun,
            reasons: [],
        );

        // given KT has 0 morale points so she will die at cycle change
        $this->kuanTi->setMoralPoint(0);

        // when cycle change occurs for both players
        $playerEvent = new PlayerCycleEvent(
            player: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);
        $playerEvent = new PlayerCycleEvent(
            player: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then no exception is thrown
        $I->expect('No exception is thrown');
    }

    public function shouldNotMakeMushPlayerSickEvenFromIncubatingDisease(FunctionalTester $I): void
    {
        // given I have an incubating disease
        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::CHRONIC_VERTIGO,
            player: $this->player,
            reasons: [],
            delayMin: 1,
            delayLength: 0,
        );

        // given player turns into a mush
        $this->eventService->callEvent(
            event: new PlayerEvent(
                player: $this->player,
                tags: [],
                time: new \DateTime(),
            ),
            name: PlayerEvent::CONVERSION_PLAYER,
        );

        // when player has a new cycle
        $playerEvent = new PlayerEvent(
            player: $this->player,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerEvent, PlayerCycleEvent::PLAYER_NEW_CYCLE);

        // then player should not be sick
        $I->assertFalse(
            $this->player->hasActiveDisorder(),
            'Player should not be sick'
        );
    }
}
