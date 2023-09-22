<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Status\Listener;

use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Listener\StatusSubscriber;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class StatusSubscriberCest extends AbstractFunctionalTest
{
    private StatusSubscriber $statusSubscriber;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraPlace(RoomEnum::ALPHA_BAY, $I, $this->daedalus);
        $this->statusSubscriber = $I->grabService(StatusSubscriber::class);
    }

    public function testOnBrokenStatusAppliedOnEquipmentWithElectricCharges(FunctionalTester $I)
    {
        // given a patrol ship in alpha bay with electric charges charge status
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        $electricChargesConfig = $I->grabEntityFromRepository(StatusConfig::class, ['name' => EquipmentStatusEnum::ELECTRIC_CHARGES . '_patrol_ship_default']);
        $electricCharges = new ChargeStatus($pasiphae, $electricChargesConfig);
        $I->haveInRepository($electricCharges);

        // when status subscriber listens to broken status applied event
        $brokenConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => EquipmentStatusEnum::BROKEN]);
        $statusEvent = new StatusEvent(
            EquipmentStatusEnum::BROKEN,
            $pasiphae,
            ['test'],
            new \DateTime(),
        );
        $this->statusSubscriber->onStatusApplied($statusEvent);

        // then electric charges status charge value is 0
        $I->assertEquals(0, $electricCharges->getCharge());
    }
}
