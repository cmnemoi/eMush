<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Status\Listener;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Listener\PlayerCycleSubscriber;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class InactivityStatusCest extends AbstractFunctionalTest
{
    private PlayerCycleSubscriber $playerCycleSubscriber;
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->playerCycleSubscriber = $I->grabService(PlayerCycleSubscriber::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldBeCreatedAtCycleChange(FunctionalTester $I): void
    {
        $this->givenPlayerHasAllTheirActionPoints();

        $this->givenUserLastActivityIsFrom(new \DateTime('-1 day'));

        $this->whenANewCycleIsTriggered();

        $this->thenThePlayerShouldHaveTheInactiveStatus($I);
    }

    public function shouldPrintAPublicLogWhenCreated(FunctionalTester $I): void
    {
        $this->givenPlayerHasAllTheirActionPoints();

        $this->givenUserLastActivityIsFrom(new \DateTime('-1 day'));

        $this->whenANewCycleIsTriggered();

        $this->thenAPublicCreationRoomLogShouldBeCreated($I);
    }

    public function shouldBeRemovedAfterAnAction(FunctionalTester $I): void
    {
        $this->givenPlayerHasAllTheirActionPoints();

        $this->givenUserLastActivityIsFrom(new \DateTime());

        $this->givenPlayerHasInactiveStatus();

        $this->whenPlayerMakesAnAction($I);

        $this->thenThePlayerShouldNotHaveTheInactiveStatus($I);
    }

    public function shouldPrintAPublicLogWhenDeleted(FunctionalTester $I): void
    {
        $this->givenPlayerHasAllTheirActionPoints();

        $this->givenPlayerHasInactiveStatus();

        $this->givenUserLastActivityIsFrom(new \DateTime());

        $this->whenPlayerMakesAnAction($I);

        $this->thenAPublicRemovalRoomLogShouldBeCreated($I);
    }

    public function shouldDropAllCriticalItemsWhenHighlyInactiveCreated(FunctionalTester $I): void
    {
        $this->givenPlayerHasATrackie();

        $this->givenPlayerHasAnApron();

        $this->givenPlayerHasPlasteniteArmor();

        $this->whenPlayerGetsHighlyInactiveStatus();

        $this->thenTheApronShouldHaveDropped($I);

        $this->thenTheTrackieShouldNotHaveDropped($I);

        $this->thenTheArmorShouldNotHaveDropped($I);

        $this->thenPlayerShouldStillBeHighlyInactive($I);
    }

    public function shouldDropHydropotsWhenPlayerTurnsHighlyInactive(FunctionalTester $I): void
    {
        $this->givenPlayerHasItem(ItemEnum::HYDROPOT);

        $this->whenPlayerGetsHighlyInactiveStatus();

        $this->thenItemShouldHaveDropped(ItemEnum::HYDROPOT, $I);
    }

    #[DataProvider('plants')]
    public function shouldDropPlantsWhenPlayerTurnsHighlyInactive(FunctionalTester $I, Example $example): void
    {
        $this->givenPlayerHasItem($example['plant']);

        $this->whenPlayerGetsHighlyInactiveStatus();

        $this->thenItemShouldHaveDropped($example['plant'], $I);
    }

    public function plants(): array
    {
        return array_map(
            static fn (string $plant) => ['plant' => $plant],
            GamePlantEnum::getAll()
        );
    }

    private function givenPlayerHasAllTheirActionPoints(): void
    {
        $this->player->setActionPoint($this->player->getCharacterConfig()->getMaxActionPoint());
    }

    private function givenPlayerHasInactiveStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenUserLastActivityIsFrom(\DateTime $date): void
    {
        (new \ReflectionProperty($this->player->getUser(), 'lastActivityAt'))->setValue($this->player->getUser(), $date);
    }

    private function givenPlayerHasATrackie(): void
    {
        $talkie = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ITRACKIE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasPlasteniteArmor(): void
    {
        $talkie = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::PLASTENITE_ARMOR,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasAnApron(): void
    {
        $talkie = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::STAINPROOF_APRON,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasItem(string $itemName): void
    {
        $talkie = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $itemName,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenANewCycleIsTriggered(): void
    {
        $playerCycleEvent = new PlayerCycleEvent($this->player, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->playerCycleSubscriber->onNewCycle($playerCycleEvent);
    }

    private function whenPlayerMakesAnAction(FunctionalTester $I): void
    {
        $actionEvent = new ActionEvent(
            actionConfig: $I->grabEntityFromRepository(entity: ActionConfig::class, params: ['name' => ActionEnum::SEARCH]),
            actionProvider: $this->player,
            player: $this->player,
            tags: []
        );
        $this->eventService->callEvent($actionEvent, ActionEvent::PRE_ACTION);
    }

    private function whenPlayerGetsHighlyInactiveStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HIGHLY_INACTIVE,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function thenThePlayerShouldHaveTheInactiveStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::INACTIVE));
    }

    private function thenThePlayerShouldNotHaveTheInactiveStatus(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::INACTIVE));
    }

    private function thenAPublicCreationRoomLogShouldBeCreated(FunctionalTester $I): void
    {
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => StatusEventLogEnum::PLAYER_FALL_ASLEEP,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertEquals(
            expected: 'chun',
            actual: $log->getParameters()['character']
        );
    }

    private function thenAPublicRemovalRoomLogShouldBeCreated(FunctionalTester $I): void
    {
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => StatusEventLogEnum::PLAYER_WAKE_UP,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertEquals(
            expected: 'chun',
            actual: $log->getParameters()['character']
        );
    }

    private function thenTheApronShouldHaveDropped(FunctionalTester $I): void
    {
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => LogEnum::OBJECT_FELL,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertEquals(
            expected: 'chun',
            actual: $log->getParameters()['character']
        );

        $I->assertTrue($this->player->getPlace()->hasEquipmentByName(GearItemEnum::STAINPROOF_APRON), 'No Apron found in room shelf!');
    }

    private function thenTheArmorShouldNotHaveDropped(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasEquipmentByName(GearItemEnum::PLASTENITE_ARMOR), 'No plastenite Armor found on player!');
    }

    private function thenTheTrackieShouldNotHaveDropped(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasEquipmentByName(ItemEnum::ITRACKIE), 'No iTrackie found on player!');
    }

    private function thenPlayerShouldStillBeHighlyInactive(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::HIGHLY_INACTIVE), 'player woke up!');
    }

    private function thenItemShouldHaveDropped(string $itemName, FunctionalTester $I): void
    {
        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->player->getPlace()->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => LogEnum::OBJECT_FELL,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertEquals(
            expected: $itemName,
            actual: $log->getParameters()['target_item']
        );

        $I->assertTrue($this->player->getPlace()->hasEquipmentByName($itemName), 'No ' . $itemName . ' found in room shelf!');
    }
}
