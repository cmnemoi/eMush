<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Event;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class StatusAppliedCest extends AbstractFunctionalTest
{
    private StatusServiceInterface $statusService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldNotSendSoiledNotificationWhenDirtyStatusIsPreventedByApron(FunctionalTester $I): void
    {
        $this->givenPlayerHasStainproofApron();

        $this->whenIApplyDirtyStatusToPlayer();

        $this->thenPlayerShouldNotReceiveSoiledNotification($I);
        $this->thenPlayerShouldNotBeDirty($I);
    }

    public function shouldSendSoiledNotificationWhenDirtyStatusIsSuccessfullyApplied(FunctionalTester $I): void
    {
        $this->whenIApplyDirtyStatusToPlayer();

        $this->thenPlayerShouldReceiveSoiledNotification($I);
        $this->thenPlayerShouldBeDirty($I);
    }

    public function shouldNotSendClumsinessNotificationWhenClumsinessEventIsPreventedByGloves(FunctionalTester $I): void
    {
        $this->givenPlayerHasProtectiveGloves();

        $this->whenITriggerClumsinessEvent($I);

        $this->thenPlayerShouldNotReceiveClumsinessNotification($I);
        $this->thenPlayerShouldNotLoseHealthPoints($I);
    }

    private function givenPlayerHasStainproofApron(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::STAINPROOF_APRON,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerHasProtectiveGloves(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::PROTECTIVE_GLOVES,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function whenIApplyDirtyStatusToPlayer(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenITriggerClumsinessEvent(FunctionalTester $I): void
    {
        $event = new ActionVariableEvent(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SEARCH]),
            actionProvider: $this->player,
            variableName: ActionVariableEnum::PERCENTAGE_INJURY,
            quantity: 100,
            player: $this->player,
            tags: [],
            actionTarget: null,
        );
        $this->eventService->callEvent($event, ActionVariableEvent::ROLL_ACTION_PERCENTAGE);
    }

    private function thenPlayerShouldNotReceiveSoiledNotification(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(PlayerNotification::class, [
            'player' => $this->player,
            'message' => PlayerNotificationEnum::SOILED,
        ]);
    }

    private function thenPlayerShouldReceiveSoiledNotification(FunctionalTester $I): void
    {
        $I->seeInRepository(PlayerNotification::class, [
            'player' => $this->player,
            'message' => PlayerNotificationEnum::SOILED,
        ]);
    }

    private function thenPlayerShouldNotReceiveClumsinessNotification(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(PlayerNotification::class, [
            'player' => $this->player,
            'message' => PlayerNotificationEnum::CLUMSINESS,
        ]);
    }

    private function thenPlayerShouldNotBeDirty(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::DIRTY));
    }

    private function thenPlayerShouldBeDirty(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::DIRTY));
    }

    private function thenPlayerShouldNotLoseHealthPoints(FunctionalTester $I): void
    {
        $I->assertEquals(10, $this->player->getHealthPoint());
    }
}
