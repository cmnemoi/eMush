<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\DeletePlanet;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DeletePlanetCest extends AbstractFunctionalTest
{
    private ActionConfig $deletePlanetConfig;
    private DeletePlanet $deletePlanetAction;
    private Planet $planet;
    private GameEquipment $astroTerminal;
    private PlanetServiceInterface $planetService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->deletePlanetConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::DELETE_PLANET]);
        $this->deletePlanetAction = $I->grabService(DeletePlanet::class);
        $this->planetService = $I->grabService(PlanetServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given there is an astro terminal in player room
        $astroTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ASTRO_TERMINAL]);
        $this->astroTerminal = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $this->astroTerminal
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setEquipment($astroTerminalConfig);
        $I->haveInRepository($this->astroTerminal);

        // given player has found a planet
        $this->planet = $this->planetService->createPlanet($this->player);
    }

    public function testDeletePlanetNotVisibleIfPlayerNotFocusedOnAstroTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the astro terminal

        // when player tries to delete the planet
        $this->deletePlanetAction->loadParameters(
            actionConfig: $this->deletePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->deletePlanetAction->execute();

        // then planet is not visible
        $I->assertFalse($this->deletePlanetAction->isVisible());
    }

    public function testDeletePlanetNotVisibleIfDaedalusIsInPlanetOrbit(FunctionalTester $I): void
    {
        // given daedalus is in planet orbit
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to delete the planet
        $this->deletePlanetAction->loadParameters(
            actionConfig: $this->deletePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->deletePlanetAction->execute();

        // then action is not visible
        $I->assertFalse($this->deletePlanetAction->isVisible());
    }

    public function testDeletePlanetNotExecutableIfAstroTerminalIsBroken(FunctionalTester $I): void
    {
        // given astro terminal is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->astroTerminal,
            tags: [],
            time: new \DateTime(),
        );

        // when player tries to delete the planet
        $this->deletePlanetAction->loadParameters(
            actionConfig: $this->deletePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->deletePlanetAction->execute();

        // then action is not executable
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::BROKEN_EQUIPMENT,
            actual: $this->deletePlanetAction->cannotExecuteReason(),
        );
    }

    public function testDeletePlanetSuccessDeletePlanet(FunctionalTester $I): void
    {
        // given player is focused on the astro terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->astroTerminal,
        );

        // when player tries to delete the planet
        $this->deletePlanetAction->loadParameters(
            actionConfig: $this->deletePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->deletePlanetAction->execute();

        // then planet is deleted
        $I->dontSeeInRepository(Planet::class);
    }

    public function testDeletePlanetSuccessPrintsLog(FunctionalTester $I): void
    {
        // given player is focused on the astro terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->astroTerminal,
        );

        // when player tries to delete the planet
        $this->deletePlanetAction->loadParameters(
            actionConfig: $this->deletePlanetConfig,
            actionProvider: $this->astroTerminal,
            player: $this->player,
            target: $this->planet
        );
        $this->deletePlanetAction->execute();

        // then log is printed
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'log' => ActionLogEnum::DELETE_PLANET_SUCCESS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
