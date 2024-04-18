<?php

namespace Mush\Tests\functional\Equipment\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Enum\StatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class NewFruitNotHazardousCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testPlantHealthy(FunctionalTester $I)
    {
        $bananaTreeConfig = $I->grabEntityFromRepository(ItemConfig::class, ['equipmentName' => GamePlantEnum::BANANA_TREE]);

        $bananaTree = $bananaTreeConfig->createGameItem($this->player1);

        $I->haveInRepository($bananaTree);
        $this->player1->addEquipment($bananaTree);
        $I->refreshEntities($this->player1);

        $characterConfig = $this->player1->getPlayerInfo()->getCharacterConfig();
        $characterConfig->setMaxItemInInventory(1);
        $I->refreshEntities($characterConfig);

        $this->daedalus->setCycle(8);
        $I->flushToDatabase($this->daedalus);

        $daedalusNewCycle = new DaedalusCycleEvent($this->daedalus, [], new \DateTime());

        $this->eventService->callEvent($daedalusNewCycle, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

        if ($this->player1->getPlace()->hasStatus(StatusEnum::FIRE)) {
            $I->assertCount(1, $this->player1->getEquipments());
            $I->assertCount(1, $this->player1->getPlace()->getEquipments());
            $plant = $this->player1->getEquipments()->first();
            $fruit = $this->player1->getPlace()->getEquipments()->first();
            $I->assertInstanceOf(GameEquipment::class, $plant);
            $I->assertInstanceOf(GameEquipment::class, $fruit);
            $I->assertTrue($plant->getStatuses()->count() > 0);
            $I->assertCount(0, $fruit->getStatuses());
        } else {
            $I->assertCount(1, $this->player1->getEquipments());
            $plant = $this->player1->getEquipments()->first();
            $I->assertInstanceOf(GameEquipment::class, $plant);
            $I->assertTrue($plant->getStatuses()->count() > 0);
        }
    }
}
