<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\SummerEventActions\UpgradeReactor;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class UpgradeReactorCest extends AbstractFunctionalTest
{
    private ActionConfig $config;
    private UpgradeReactor $upgradeReactor;
    private Place $bridge;
    private GameEquipment $emergencyReactor;
    private PlanetServiceInterface $planetService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->config = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::UPGRADE_REACTOR]);
        $this->upgradeReactor = $I->grabService(UpgradeReactor::class);

        $this->planetService = $I->grabService(PlanetServiceInterface::class);

        $this->bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);
        $this->emergencyReactor = $this->createEquipment(EquipmentEnum::EMERGENCY_REACTOR, $this->bridge);
        $this->chun->setPlace($this->bridge);
    }

    public function testUpgradeReactor(FunctionalTester $I): void
    {
        // we load the action
        $this->upgradeReactor->loadParameters(
            actionConfig: $this->config,
            actionProvider: $this->emergencyReactor,
            player: $this->chun,
            target: $this->emergencyReactor
        );

        // we can't do it without the tablet on the daedalus
        $I->assertFalse($this->upgradeReactor->isVisible());

        $this->createEquipment(ItemEnum::TREASURE_HUNT_TABLET, $this->kuanTi->getPlace());

        // we can't do it if we are not genius ou without the alien device
        $I->assertEquals('need_genius_or_alien_device', $this->upgradeReactor->cannotExecuteReason());

        $this->createStatusOn(PlayerStatusEnum::GENIUS_IDEA, $this->chun);

        // we can do it with genius activated
        $I->assertNull($this->upgradeReactor->cannotExecuteReason());

        $this->statusService->removeStatus(PlayerStatusEnum::GENIUS_IDEA, $this->chun, [], new \DateTime());
        // we can still do it without genious but with the alien device
        $this->createEquipment(ItemEnum::TREASURE_HUNT_DEVICE, $this->bridge);

        $I->assertNull($this->upgradeReactor->cannotExecuteReason());

        $this->upgradeReactor->execute();

        // the two status are given to the daedalus.
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::CAN_MOVE_TO_EVENT_PLANET));
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::REACTOR_UPGRADED));

        // can't do it again.
        $I->assertFalse($this->upgradeReactor->isVisible());
    }
}
