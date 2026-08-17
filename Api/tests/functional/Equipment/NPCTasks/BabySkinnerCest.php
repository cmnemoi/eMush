<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\NPCTasks;

use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\NPCTasks\AiHandler\BabySkinnerTasksHandler;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class BabySkinnerCest extends AbstractFunctionalTest
{
    private BabySkinnerTasksHandler $babySkinnerTasksHandler;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->babySkinnerTasksHandler = $I->grabService(BabySkinnerTasksHandler::class);
    }

    public function testBabySkinnerBite(FunctionalTester $I): void
    {
        // given baby skinner that protect chun
        $pet = $this->createEquipment(ItemEnum::TREASURE_HUNT_PET, $this->chun->getPlace());
        $status = $this->createStatusOn(PlayerStatusEnum::PROTECTED_BY_PET, $this->chun, $pet);

        // given that Kuan Ti has 14 hp
        $this->kuanTi->setHealthPoint(14);

        // when the pet bite
        $this->babySkinnerTasksHandler->biteTask($pet, $status, new \DateTime());

        // as kuan Ti is alone with chun, he should has lost 2 HP
        $I->assertEquals(12, $this->kuanTi->getHealthPoint());

        // and we should see a log.
        $I->seeInRepository(RoomLog::class, ['log' => 'baby_skinner_bite']);
    }

    public function testBabySkinnerBiteInfected(FunctionalTester $I): void
    {
        // given baby skinner that protect chun
        $pet = $this->createEquipment(ItemEnum::TREASURE_HUNT_PET, $this->chun->getPlace());
        $status = $this->createStatusOn(PlayerStatusEnum::PROTECTED_BY_PET, $this->chun, $pet);

        // which is infected
        $this->createStatusOn(EquipmentStatusEnum::BABY_SKINNER_INFECTED, $pet, $this->chun);

        // given that Kuan Ti has 14 hp
        $this->kuanTi->setHealthPoint(14);

        // when the pet bite
        $this->babySkinnerTasksHandler->biteTask($pet, $status, new \DateTime());

        // as kuan Ti is alone with chun, he should has lost 2 HP
        $I->assertEquals(12, $this->kuanTi->getHealthPoint());

        // and we should see a log.
        $I->seeInRepository(RoomLog::class, ['log' => 'baby_skinner_bite']);

        // and Kuan Ti should have a spore
        $I->assertEquals(1, $this->kuanTi->getSpores());
    }
}
