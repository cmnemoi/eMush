<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\Scan;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class ScanCest extends AbstractFunctionalTest
{
    private Action $scanActionConfig;
    private Scan $scanAction;

    private StatusServiceInterface $statusService;

    private Place $bridge;
    private GameEquipment $astroTerminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->scanActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::SCAN]);
        $this->scanAction = $I->grabService(Scan::class);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->scanActionConfig->setSuccessRate(100);

        // given there is an astro terminal on the bridge
        $this->bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);
        $astroTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ASTRO_TERMINAL]);
        $this->astroTerminal = new GameEquipment($this->bridge);
        $this->astroTerminal
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setEquipment($astroTerminalConfig)
        ;
        $I->haveInRepository($this->astroTerminal);

        // given player is on the bridge
        $this->player->changePlace($this->bridge);

        // given player is focused on the astro terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->astroTerminal
        );
    }

    public function testScanNotVisibleIfPlayerNotFocusedOnAstroTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the astro terminal
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then the action is not visible
        $I->assertFalse($this->scanAction->isVisible());
    }

    public function testScanSuccessCreatesAPlanet(FunctionalTester $I): void
    {
        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then a planet is created
        $I->seeInRepository(Planet::class);
    }

    public function testScanSuccessPlanetHasADistanceBetweenTwoAndNine(FunctionalTester $I): void
    {
        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then a planet is created
        /** @var Planet $planet */
        $planet = $I->grabEntityFromRepository(Planet::class);

        $I->assertGreaterThanOrEqual(2, $planet->getCoordinates()->getDistance());
        $I->assertLessThanOrEqual(9, $planet->getCoordinates()->getDistance());
    }

    public function testScanSuccessRateIsImprovedByWorkingPlanetScanner(FunctionalTester $I): void
    {
        // given success rate of the action is 50%
        $this->scanActionConfig->setSuccessRate(50);

        // given there is a planet scanner on the Daedalus
        $planetScannerConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PLANET_SCANNER]);
        $planetScanner = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $planetScanner
            ->setName(EquipmentEnum::PLANET_SCANNER)
            ->setEquipment($planetScannerConfig)
        ;
        $I->haveInRepository($planetScanner);

        // given this planet scanner has the right modifier
        /** @var VariableEventModifierConfig $planetScannerModifierConfig */
        $planetScannerModifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, ['name' => 'modifier_for_daedalus_+30percentage_on_action_scan']);
        $planetScannerModifier = new GameModifier($this->daedalus, $planetScannerModifierConfig);
        $I->haveInRepository($planetScannerModifier);

        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);

        // then success rate is improved by the right amount
        $I->assertEquals(
            expected: $this->scanActionConfig->getSuccessRate() + $planetScannerModifierConfig->getDelta(),
            actual: $this->scanAction->getSuccessRate()
        );
    }

    public function testScanRevealsPlanetSectorsIfMagellanLiquidMapIsInTheRoom(FunctionalTester $I): void
    {
        // given magellan' liquid map is on the bridge
        $liquidMapConfig = $I->grabEntityFromRepository(ItemConfig::class, ['name' => GearItemEnum::MAGELLAN_LIQUID_MAP . '_default']);
        $liquidMap = new GameItem($this->bridge);
        $liquidMap
            ->setName(GearItemEnum::MAGELLAN_LIQUID_MAP)
            ->setEquipment($liquidMapConfig)
        ;
        $I->haveInRepository($liquidMap);

        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then the scanned planet should have some sections revealed
        /** @var Planet $planet */
        $planet = $I->grabEntityFromRepository(Planet::class);
        $I->assertNotEmpty($planet->getRevealedSectors());

        // then there should be a specific public log to tell that the map worked
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::BRIDGE,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => LogEnum::LIQUID_MAP_HELPED,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }
}
