<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Status\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class StatusServiceCest extends AbstractFunctionalTest
{
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraPlace(RoomEnum::ALPHA_BAY, $I, $this->daedalus);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testBrokenEquipmentLosesTheirCharges(FunctionalTester $I)
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
        /** @var ChargeStatus $electricCharges */
        $electricCharges = $this->statusService->createStatusFromConfig(
            $electricChargesConfig,
            $pasiphae,
            [],
            new \DateTime()
        );

        // when patrol ship is broken
        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $pasiphae,
            ['test'],
            new \DateTime(),
        );

        // then electric charges status charge value is 0
        $I->assertEquals(0, $electricCharges->getCharge());
    }

    public function testBrokenTerminalRemovesPlayerFocusedStatus(FunctionalTester $I): void
    {
        // given there is a command terminal in player room
        $commandTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::COMMAND_TERMINAL]);
        $commandTerminal = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $commandTerminal
            ->setName(EquipmentEnum::COMMAND_TERMINAL)
            ->setEquipment($commandTerminalConfig)
        ;
        $I->haveInRepository($commandTerminal);

        // given player is focused on command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $commandTerminal
        );

        // when command terminal is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $commandTerminal,
            tags: [],
            time: new \DateTime(),
        );

        // then player is not focused on command terminal anymore
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
    }

    public function testDispatchEquipmentBroken(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::BROKEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$statusConfig])]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        // Case of a game Equipment
        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $gameEquipment,
            [EventEnum::NEW_CYCLE],
            new \DateTime(),
            null,
            VisibilityEnum::PUBLIC
        );

        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $I->assertTrue($room->getEquipments()->first()->isBroken());
        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'log' => StatusEventLogEnum::EQUIPMENT_BROKEN,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
