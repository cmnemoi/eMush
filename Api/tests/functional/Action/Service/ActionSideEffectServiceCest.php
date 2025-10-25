<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Service;

use Mush\Action\Actions\PetCat;
use Mush\Action\Actions\Search;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentInitEvent;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ActionSideEffectServiceCest extends AbstractFunctionalTest
{
    private Search $searchAction;
    private ActionConfig $action;
    private PetCat $petCatAction;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SEARCH]);

        $this->searchAction = $I->grabService(Search::class);
        $this->petCatAction = $I->grabService(PetCat::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function shouldMakePlayerDirty(FunctionalTester $I): void
    {
        $this->givenActionMakesPlayerDirty();

        $this->whenPlayerExecutesAction();

        $this->thenPlayerIsDirty($I);
        $this->thenPlayerHasNotification($I);
    }

    public function shouldPreventDirtyWithApron(FunctionalTester $I): void
    {
        $this->givenActionMakesPlayerDirty();
        $this->givenPlayerHasStainproofApron($I);

        $this->whenPlayerExecutesAction();

        $this->thenPlayerIsNotDirty($I);
        $this->thenSoilPreventedLogIsCreated($I);
        $this->thenSoiledLogIsNotCreated($I);
    }

    public function shouldNotPreventDirtyWithApronOnSuperDirtyAction(FunctionalTester $I): void
    {
        $this->givenActionMakesPlayerSuperDirty();
        $this->givenPlayerHasStainproofApron($I);

        $this->whenPlayerExecutesAction();

        $this->thenPlayerIsDirty($I);
    }

    public function shouldMakePlayerClumsyAndInjured(FunctionalTester $I): void
    {
        $this->givenActionMakesPlayerClumsy();

        $initHealthPoints = $this->player1->getHealthPoint();

        $this->whenPlayerExecutesAction();

        $this->thenPlayerLosesHealthPoints($initHealthPoints, 2, $I);
        $this->thenPlayerHasNotification($I);
        $this->thenClumsyLogIsCreated($I);
    }

    public function shouldPreventClumsyWithGloves(FunctionalTester $I): void
    {
        $this->givenActionMakesPlayerClumsy();
        $this->givenPlayerHasProtectiveGloves($I);

        $initHealthPoints = $this->player1->getHealthPoint();

        $this->whenPlayerExecutesAction();

        $this->thenPlayerHealthPointsStayTheSame($initHealthPoints, $I);
        $this->thenClumsyPreventedLogIsCreated($I);
    }

    public function shouldPrintClumsyCatLogWhenPettingCat(FunctionalTester $I): void
    {
        $this->givenPlayerHasCat($I);
        $this->givenPetCatActionMakesPlayerClumsy($I);

        $this->whenPlayerPetsCat();

        $this->thenClumsyCatLogIsCreated($I);
    }

    private function givenActionMakesPlayerDirty(): void
    {
        $this->action->setDirtyRate(100);
    }

    private function givenActionMakesPlayerSuperDirty(): void
    {
        $this->action->setDirtyRate(100)->setTypes([ActionTypeEnum::ACTION_SUPER_DIRTY]);
    }

    private function givenActionMakesPlayerClumsy(): void
    {
        $this->action->setInjuryRate(100);
    }

    private function givenPlayerHasStainproofApron(FunctionalTester $I): void
    {
        $apronConfig = $I->grabEntityFromRepository(ItemConfig::class, ['name' => GearItemEnum::STAINPROOF_APRON . '_' . GameConfigEnum::DEFAULT]);
        $apron = new GameItem($this->player1);
        $apron
            ->setName('apron_test')
            ->setEquipment($apronConfig);
        $I->haveInRepository($apron);

        $event = new EquipmentInitEvent($apron, $apronConfig, [], new \DateTime());
        $event->setAuthor($this->player1);

        $this->eventService->callEvent($event, EquipmentInitEvent::NEW_EQUIPMENT);
    }

    private function givenPlayerHasProtectiveGloves(FunctionalTester $I): void
    {
        $gloveConfig = $I->grabEntityFromRepository(ItemConfig::class, ['name' => GearItemEnum::PROTECTIVE_GLOVES . '_' . GameConfigEnum::DEFAULT]);
        $gloves = new GameItem($this->player1);
        $gloves
            ->setName('gloves_test')
            ->setEquipment($gloveConfig);
        $I->haveInRepository($gloves);

        $event = new EquipmentInitEvent($gloves, $gloveConfig, [], new \DateTime());
        $event->setAuthor($this->player1);

        $this->eventService->callEvent($event, EquipmentInitEvent::NEW_EQUIPMENT);
    }

    private function givenPlayerHasCat(FunctionalTester $I): void
    {
        $catConfig = $I->grabEntityFromRepository(ItemConfig::class, ['name' => ItemEnum::SCHRODINGER . '_' . GameConfigEnum::DEFAULT]);
        $cat = new GameItem($this->player1);
        $cat
            ->setName(ItemEnum::SCHRODINGER)
            ->setEquipment($catConfig);
        $I->haveInRepository($cat);

        $event = new EquipmentInitEvent($cat, $catConfig, [], new \DateTime());
        $event->setAuthor($this->player1);

        $this->eventService->callEvent($event, EquipmentInitEvent::NEW_EQUIPMENT);
    }

    private function givenPetCatActionMakesPlayerClumsy(FunctionalTester $I): void
    {
        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PET_CAT]);
        $this->action->setInjuryRate(100);
        $I->flushToDatabase($this->action);
    }

    private function whenPlayerExecutesAction(): void
    {
        $this->searchAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1
        );
        $this->searchAction->execute();
    }

    private function whenPlayerPetsCat(): void
    {
        $cat = $this->player1->getEquipmentByName(ItemEnum::SCHRODINGER);

        $this->petCatAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $cat,
        );
        $this->petCatAction->execute();
    }

    private function thenPlayerIsDirty(FunctionalTester $I): void
    {
        $I->assertTrue($this->player1->hasStatus(PlayerStatusEnum::DIRTY));
    }

    private function thenPlayerIsNotDirty(FunctionalTester $I): void
    {
        $I->assertFalse($this->player1->hasStatus(PlayerStatusEnum::DIRTY));
    }

    private function thenPlayerHasNotification(FunctionalTester $I): void
    {
        $I->assertTrue($this->player1->hasNotification());
    }

    private function thenSoilPreventedLogIsCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::SOIL_PREVENTED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    private function thenSoiledLogIsNotCreated(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => StatusEventLogEnum::SOILED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    private function thenPlayerLosesHealthPoints(int $initHealthPoints, int $lostHealthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($initHealthPoints - $lostHealthPoints, $this->player1->getHealthPoint());
    }

    private function thenPlayerHealthPointsStayTheSame(int $initHealthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($initHealthPoints, $this->player1->getHealthPoint());
    }

    private function thenClumsyLogIsCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::CLUMSINESS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    private function thenClumsyPreventedLogIsCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::CLUMSINESS_PREVENTED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    private function thenClumsyCatLogIsCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => PlayerModifierLogEnum::CLUMSINESS_CAT,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
