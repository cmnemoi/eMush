<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Listener;

use Mush\Action\Actions\Scan;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlanetCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private ActionConfig $scanActionConfig;
    private Scan $scanAction;

    private GameEquipment $astroTerminal;

    private Player $frieda;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->scanActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SCAN]);
        // Assume scanning is always successful and doesn't cost AP
        $this->scanActionConfig->setSuccessRate(100)->setActionCost(0);
        $this->scanAction = $I->grabService(Scan::class);

        $this->frieda = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::FRIEDA);
        // Assume Frieda can scan so many planets without having to delete them
        $this->frieda->getCharacterConfig()->setMaxDiscoverablePlanets(32);
        $this->astroTerminal = $this->givenAstroTerminal($this->frieda);
        $this->givenPlayerFocusedOnAstroTerminal($this->frieda);
    }

    public function shouldGainTriumphOnFriedaScan(FunctionalTester $I)
    {
        $this->whenPlayerScans($this->frieda, 5);

        // Then Frieda should have 5 * 2 triumph
        $this->thenPlayerShouldHaveTriumph($this->frieda, 10, $I);
        $this->thenPlayerShouldHaveTriumph($this->chun, 0, $I);

        $this->whenPlayerScans($this->frieda, 1);

        // Prevented by regressive factor
        $this->thenPlayerShouldHaveTriumph($this->frieda, 10, $I);

        $this->whenPlayerScans($this->frieda, 1);

        $this->thenPlayerShouldHaveTriumph($this->frieda, 12, $I);
    }

    public function shouldNotGainTriumphOnFailure(FunctionalTester $I)
    {
        $this->givenScanningAlwaysFails();

        $this->whenPlayerScans($this->frieda, 1);

        $this->thenPlayerShouldHaveTriumph($this->frieda, 0, $I);
    }

    public function shouldNotGainTriumphOnSomeoneElseScan(FunctionalTester $I)
    {
        $this->givenPlayerFocusedOnAstroTerminal($this->chun);

        $this->whenPlayerScans($this->chun, 1);

        $this->thenPlayerShouldHaveTriumph($this->frieda, 0, $I);
        $this->thenPlayerShouldHaveTriumph($this->chun, 0, $I);
    }

    private function givenAstroTerminal(): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::ASTRO_TERMINAL,
            equipmentHolder: $this->frieda->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerFocusedOnAstroTerminal(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $player,
            tags: [ActionEnum::ACCESS_TERMINAL->toString()],
            time: new \DateTime(),
            target: $this->astroTerminal
        );
    }

    private function givenScanningAlwaysFails(): void
    {
        $this->scanActionConfig->setSuccessRate(0);
    }

    private function whenPlayerScans(Player $player, int $quantity = 1): void
    {
        $this->scanAction->loadParameters(
            actionConfig: $this->scanActionConfig,
            actionProvider: $this->astroTerminal,
            player: $player,
            target: $this->astroTerminal,
        );
        for ($i = 0; $i < $quantity; ++$i) {
            $this->scanAction->execute();
        }
    }

    private function thenPlayerShouldHaveTriumph(Player $player, int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $player->getTriumph());
    }
}
