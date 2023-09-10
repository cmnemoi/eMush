<?php

namespace functional\Game\Service;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Action\Actions\Search;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Event\EquipmentInitEvent;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class ActionSideEffectServiceCest extends AbstractFunctionalTest
{
    private Search $searchAction;
    private Action $action;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::SEARCH]);

        $this->searchAction = $I->grabService(Search::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testGetDirty(FunctionalTester $I)
    {
        $this->action->setDirtyRate(100);
        $I->flushToDatabase($this->action);

        $this->searchAction->loadParameters($this->action, $this->player1, null);
        $this->searchAction->execute();

        $I->assertTrue($this->player1->hasStatus(PlayerStatusEnum::DIRTY));
    }

    public function testDirtyPreventedByApron(FunctionalTester $I)
    {
        $this->action->setDirtyRate(100);
        $I->flushToDatabase($this->action);

        $apronConfig = $I->grabEntityFromRepository(ItemConfig::class, ['name' => GearItemEnum::STAINPROOF_APRON . '_' . GameConfigEnum::DEFAULT]);
        $apron = new GameItem($this->player1);
        $apron
            ->setName('apron_test')
            ->setEquipment($apronConfig)
        ;
        $I->haveInRepository($apron);

        $event = new EquipmentInitEvent($apron, $apronConfig, [], new \DateTime());
        $event->setAuthor($this->player1);

        $this->eventService->callEvent($event, EquipmentInitEvent::NEW_EQUIPMENT);

        $I->assertCount(1, $this->player1->getEquipments());
        $I->assertCount(1, $this->player1->getModifiers());

        $this->searchAction->loadParameters($this->action, $this->player1, null);
        $this->searchAction->execute();

        $I->assertFalse($this->player1->hasStatus(PlayerStatusEnum::DIRTY));
        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::SOIL_PREVENTED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testDirtyNotPreventedByApronSuperDirty(FunctionalTester $I)
    {
        $this->action->setDirtyRate(100)->setTypes([ActionTypeEnum::ACTION_SUPER_DIRTY]);
        $I->flushToDatabase($this->action);

        $apronConfig = $I->grabEntityFromRepository(ItemConfig::class, ['name' => GearItemEnum::STAINPROOF_APRON . '_' . GameConfigEnum::DEFAULT]);
        $apron = new GameItem($this->player1);
        $apron
            ->setName('apron_test')
            ->setEquipment($apronConfig)
        ;
        $I->haveInRepository($apron);

        $event = new EquipmentInitEvent($apron, $apronConfig, [], new \DateTime());
        $event->setAuthor($this->player1);

        $this->eventService->callEvent($event, EquipmentInitEvent::NEW_EQUIPMENT);

        $I->assertCount(1, $this->player1->getEquipments());
        $I->assertCount(1, $this->player1->getModifiers());

        $this->searchAction->loadParameters($this->action, $this->player1, null);
        $this->searchAction->execute();

        $I->assertTrue($this->player1->hasStatus(PlayerStatusEnum::DIRTY));
    }

    public function testClumsiness(FunctionalTester $I)
    {
        $this->action->setInjuryRate(100);
        $I->flushToDatabase($this->action);

        $initHealthPoints = $this->player1->getHealthPoint();

        $this->searchAction->loadParameters($this->action, $this->player1, null);
        $this->searchAction->execute();

        $I->assertEquals($initHealthPoints - 2, $this->player1->getHealthPoint());
    }

    public function testClumsinessPrevented(FunctionalTester $I)
    {
        $this->action->setInjuryRate(100);
        $I->flushToDatabase($this->action);

        $initHealthPoints = $this->player1->getHealthPoint();

        $gloveConfig = $I->grabEntityFromRepository(ItemConfig::class, ['name' => GearItemEnum::PROTECTIVE_GLOVES . '_' . GameConfigEnum::DEFAULT]);
        $gloves = new GameItem($this->player1);
        $gloves
            ->setName('apron_test')
            ->setEquipment($gloveConfig)
        ;
        $I->haveInRepository($gloves);

        $event = new EquipmentInitEvent($gloves, $gloveConfig, [], new \DateTime());
        $event->setAuthor($this->player1);

        $this->eventService->callEvent($event, EquipmentInitEvent::NEW_EQUIPMENT);

        $I->assertCount(1, $this->player1->getEquipments());
        $I->assertCount(1, $this->player1->getModifiers());

        $this->searchAction->loadParameters($this->action, $this->player1, null);

        $this->searchAction->execute();

        $I->assertEquals($initHealthPoints, $this->player1->getHealthPoint());
        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::CLUMSINESS_PREVENTED,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
