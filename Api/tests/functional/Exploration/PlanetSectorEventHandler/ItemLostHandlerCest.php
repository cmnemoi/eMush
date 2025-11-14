<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\PlanetSectorEventHandler;

use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\PlanetSectorEventHandler\ItemLost;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ItemLostHandlerCest extends AbstractExplorationTester
{
    private ItemLost $itemLostHandler;
    private GameEquipmentServiceInterface $gameEquipmentService;

    private Planet $planet;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->itemLostHandler = $I->grabService(ItemLost::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->planet = $this->createPlanet([PlanetSectorEnum::OXYGEN, PlanetSectorEnum::INTELLIGENT], $I);

        $this->createExploration(
            planet: $this->planet,
            explorators: $this->players,
        );
    }

    public function shouldRemoveBurdenedStatusOnLostHeavyItem(FunctionalTester $I): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::OLD_FAITHFUL,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::ITEM_LOST]);
        $sector = $this->planet->getSectorByNameOrThrow(PlanetSectorEnum::INTELLIGENT);
        $event = new PlanetSectorEvent($sector, $fightEventConfig);

        $this->itemLostHandler->handle($event);

        $I->assertTrue($this->chun->doesNotHaveEquipment(ItemEnum::OLD_FAITHFUL), 'Chun should not have the old faithful');
        $I->assertFalse($this->chun->hasStatus(PlayerStatusEnum::BURDENED), 'Chun should not have burdened status');
    }
}
