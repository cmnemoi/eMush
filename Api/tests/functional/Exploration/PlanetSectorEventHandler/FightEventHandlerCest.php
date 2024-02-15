<?php

declare(strict_types=1);

namespace Mush\tests\functional\Exploration\PlanetSectorEventHandler;

use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\PlanetSectorEventHandler\Fight;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class FightEventHandlerCest extends AbstractExplorationTester
{
    private Fight $fightEventHandler;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Player $chun;
    private Player $kuanTi;
    private Player $derek;
    private Player $janice;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->fightEventHandler = $I->grabService(Fight::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given our explorators are Chun, Kuan-Ti, Derek, and Janice
        $this->chun = $this->player;
        $this->kuanTi = $this->player2;
        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
        $this->players->add($this->derek);
        $this->players->add($this->janice);

        // given Chun, Kuan-Ti, and Janice have a spacesuit
        foreach ([$this->chun, $this->kuanTi, $this->janice] as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: GearItemEnum::SPACESUIT,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given Janice is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->janice,
            tags: [],
            time: new \DateTime(),
        );

        // Derek is stuck in the ship (no spacesuit)
    }

    public function testFightEventExpeditionStrengthIsImprovedByWeapons(FunctionalTester $I): void
    {
        // given all explorators have a blaster
        foreach ($this->players as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::BLASTER,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given an exploration is created
        $exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players
        );

        // given fight planet sector event
        /** @var PlanetSectorEventConfig $fightEventConfig */
        $fightEventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => 'fight_12']);
        $intelligentLifePlanetSector = $exploration->getPlanet()->getSectors()->filter(fn ($sector) => $sector->getName() === PlanetSectorEnum::INTELLIGENT)->first();
        $event = new PlanetSectorEvent(
            planetSector: $intelligentLifePlanetSector,
            config: $fightEventConfig,
        );

        // when the event is handled by the fight event handler
        $explorationLog = $this->fightEventHandler->handle($event);

        // then the expedition strength should be 4 : 2 explorators + 2 blasters (one for Chun and one for Kuan-Ti)
        // Janice and Derek don't count as they are lost or stuck in the ship
        $I->assertEquals(4, $explorationLog->getParameters()['expedition_strength']);
    }
}
