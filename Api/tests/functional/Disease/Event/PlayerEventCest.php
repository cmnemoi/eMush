<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Event;

use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class PlayerEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function testDiseaseModifierTriggersOnPlayerNewCycleEvent(FunctionalTester $I): void
    {
        // given player has a disease
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::REJUVENATION,
            player: $this->player,
            reasons: [],
        );
        $disease->setDiseasePoint(10);

        $initialAP = $this->player->getActionPoint();

        // given the fitful sleep modifier of the disease always triggers at cycle change
        // note : this modifier removes 1 AP to the player
        /** @var TriggerEventModifierConfig $modifierConfig */
        $modifierConfig = $disease->getDiseaseConfig()->getModifierConfigs()->filter(
            fn (AbstractModifierConfig $modifierConfig) => $modifierConfig->getModifierName() === ModifierNameEnum::FITFUL_SLEEP
        )->first();
        $modifierConfig->setModifierActivationRequirements([]);
        $I->haveInRepository($modifierConfig);

        // when player has a new cycle
        $playerEvent = new PlayerEvent(
            player: $this->player,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::PLAYER_NEW_CYCLE);

        // then I should see a room log reporting the AP loss
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'playerInfo' => $this->player->getPlayerInfo(),
                'place' => $this->player->getPlace()->getLogName(),
                'log' => PlayerModifierLogEnum::LOSS_ACTION_POINT,
            ]
        );

        // Then player lost 1 AP
        $I->assertEquals($this->player->getActionPoint(), $initialAP);
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
        $this->eventService->callEvent($playerEvent, PlayerEvent::PLAYER_NEW_CYCLE);

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

        $I->assertEquals($this->player->getActionPoint(), $initialAP);
    }
}
