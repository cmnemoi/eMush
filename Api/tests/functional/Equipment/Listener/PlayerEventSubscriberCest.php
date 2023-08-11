<?php

declare(strict_types=1);

namespace functional\Equipment\Listener;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Listener\PlayerEventSubscriber;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;

final class PlayerEventSubscriberCest extends AbstractFunctionalTest
{
    private PlayerEventSubscriber $playerEventSubscriber;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->playerEventSubscriber = $I->grabService(PlayerEventSubscriber::class);
    }

    public function testOnDeathPlayerPatrolShipIsDestroyedIfCauseIsSpaceBattle(FunctionalTester $I): void
    {
        // given
        $pasiphaePlace = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);

        $patrolShipConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $patrolShip = new GameEquipment($pasiphaePlace);
        $patrolShip
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($patrolShipConfig)
        ;
        $I->haveInRepository($patrolShip);
        $this->player1->setPlace($pasiphaePlace);

        // when
        $deathPlayerEvent = new PlayerEvent(
            $this->player1,
            [EndCauseEnum::SPACE_BATTLE],
            new \DateTime()
        );
        $this->playerEventSubscriber->onDeathPlayer($deathPlayerEvent);

        // then
        $I->dontSeeInRepository(GameEquipment::class, ['name' => EquipmentEnum::PASIPHAE]);
    }

    public function testOnDeathPlayerPatrolShipIsNotDestroyedIfCauseIsNotSpaceBattle(FunctionalTester $I): void
    {
        // given
        $pasiphaePlace = $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);

        $patrolShipConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $patrolShip = new GameEquipment($pasiphaePlace);
        $patrolShip
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($patrolShipConfig)
        ;
        $I->haveInRepository($patrolShip);
        $this->player1->setPlace($pasiphaePlace);

        // when
        $deathPlayerEvent = new PlayerEvent(
            $this->player1,
            [EndCauseEnum::ALLERGY],
            new \DateTime()
        );
        $this->playerEventSubscriber->onDeathPlayer($deathPlayerEvent);

        // then
        $I->seeInRepository(GameEquipment::class, ['name' => EquipmentEnum::PASIPHAE]);
    }
}
