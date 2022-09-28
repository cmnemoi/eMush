<?php

namespace Mush\Tests\Alert\Listener;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use function PHPUnit\Framework\assertNotNull;

class EquipmentSubscriberCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testDestroyBrokenEquipment(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        assertNotNull($room);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::GRAVITY_SIMULATOR,
            'gameConfig' => $gameConfig
        ]);

        codecept_debug($room);

        $gravitySimulator = new GameEquipment();
        $gravitySimulator
            ->setName(EquipmentEnum::GRAVITY_SIMULATOR)
            ->setEquipment($gravitySimulatorConfig)
            ->setHolder($room)
        ;

        $I->haveInRepository($gravitySimulator);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setGameConfig($gameConfig)
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($statusConfig);

        $broken = new Status($gravitySimulator, $statusConfig);
        $I->haveInRepository($broken);

        $reportedAlert = new AlertElement();
        $reportedAlert->setEquipment($gravitySimulator);
        $I->haveInRepository($reportedAlert);

        $alertBroken = new Alert();
        $alertBroken
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_EQUIPMENTS)
            ->addAlertElement($reportedAlert)
        ;

        $I->haveInRepository($alertBroken);

        codecept_debug($gravitySimulator);

        $equipmentEvent = new EquipmentEvent(
            $gravitySimulator,
            false,
            VisibilityEnum::HIDDEN,
            ActionEnum::DISASSEMBLE,
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
    }
}
