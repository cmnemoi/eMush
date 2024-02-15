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
use Mush\Status\Enum\EquipmentStatusEnum;
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
        // given Chun and Kuan-Ti have a blaster
        foreach ([$this->chun, $this->kuanTi] as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::BLASTER,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given Kuan-Ti's blaster is unloaded
        $this->kuanTi->getEquipmentByName(ItemEnum::BLASTER)->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->setCharge(0);

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

        // then the expedition strength should be 3 :
        // 2 points from Chun : 1 (base) + 1 (loaded blaster)
        // 1 points from Kuan-Ti : 1 (base) + 0 (unloaded blaster)
        // 0 points from Derek and Janice as they are lost or stuck in the ship
        $I->assertEquals(3, $explorationLog->getParameters()['expedition_strength']);
    }

    public function testFightEventExpeditionStrengthIsImprovedByShooterSkill(FunctionalTester $I): void
    {
        // given Chun and Kuan-Ti have a blaster
        foreach ([$this->chun, $this->kuanTi] as $player) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::BLASTER,
                equipmentHolder: $player,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given Chun has an extra blaster
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given Kuan-Ti's blaster is unloaded
        $this->kuanTi->getEquipmentByName(ItemEnum::BLASTER)->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->setCharge(0);

        // given Chun and kuan-ti have the shooter skill
        foreach ([$this->chun, $this->kuanTi] as $player) {
            $this->statusService->createStatusFromName(
                statusName: PlayerStatusEnum::POC_SHOOTER_SKILL,
                holder: $player,
                tags: [],
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

        // then the expedition strength should be 4 :
        // 4 points from Chun : 1 (base) + 2 (2 blasters) + 1 (shooter skill with a loaded gun)
        // 1 points from Kuan-Ti : 1 (base) + 0 (unloaded blaster) + 0 (shooter skill but unloaded gun)
        // 0 points from Derek and Janice as they are lost or stuck in the ship
        $I->assertEquals(5, $explorationLog->getParameters()['expedition_strength']);
    }

    public function testFightEventUsesGrenades(FunctionalTester $I): void
    {
        // given Chun has two grenades
        for ($i = 0; $i < 2; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::GRENADE,
                equipmentHolder: $this->chun,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given Kuan-Ti has two grenades
        for ($i = 0; $i < 2; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: ItemEnum::GRENADE,
                equipmentHolder: $this->kuanTi,
                reasons: [],
                time: new \DateTime(),
            );
        }

        // given an extra explorator : Raluca
        $raluca = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
        $this->players->add($raluca);

        // given Raluca has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $raluca,
            reasons: [],
            time: new \DateTime(),
        );

        // given Raluca has a grenade
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::GRENADE,
            equipmentHolder: $raluca,
            reasons: [],
            time: new \DateTime(),
        );

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

        // then the expedition strength should be 18 :
        // 7 points from Chun : 1 (base) + 2 * 3 (2 grenades)
        // 7 points from Kuan-Ti : 1 (base) + 2 * 3 (2 grenades)
        // 4 points from Raluca : 1 (base) + 3 (grenade)
        // 0 points from Derek and Janice as they are lost or stuck in the ship
        $I->assertEquals(18, $explorationLog->getParameters()['expedition_strength']);

        // then Chun does not have grenades anymore
        $I->assertFalse($this->chun->hasEquipmentByName(ItemEnum::GRENADE));

        // then Kuan-Ti does not have grenades anymore
        $I->assertFalse($this->kuanTi->hasEquipmentByName(ItemEnum::GRENADE));

        // then Raluca still has a grenade, because it was not needed
        $I->assertTrue($raluca->hasEquipmentByName(ItemEnum::GRENADE));
    }
}
