<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Bond;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class BondActionCest extends AbstractFunctionalTest
{
    private Bond $bondAction;
    private ActionConfig $action;

    private StatusServiceInterface $statusService;

    private Player $derek;
    private Player $andie;
    private Player $gioele;
    private Player $paola;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->bondAction = $I->grabService(Bond::class);
        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::BOND]);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->andie = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);
        $this->gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);
        $this->paola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        $this->players->add($this->derek);
        $this->players->add($this->andie);
        $this->players->add($this->gioele);
        $this->players->add($this->paola);
    }

    public function shouldBond(FunctionalTester $I): void
    {
        $this->whenABondsWithB($this->chun, $this->kuanTi);
        $this->thenAHasBInBondList($this->chun, $this->kuanTi, $I);
    }

    public function shouldNotBondIfPlayersAlreadyBonded(FunctionalTester $I): void
    {
        $this->whenABondsWithB($this->chun, $this->kuanTi);

        $this->whenATriesToBondWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeExecutableWithMessage('bonds did not match with expected values', $I);
    }

    public function antisocialCannotBondEver(FunctionalTester $I): void
    {
        $this->givenPlayerIsAntisocial($this->chun);

        $this->whenATriesToBondWithB($this->chun, $this->derek);
        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::FLIRT_ANTISOCIAL, $I);

        $this->whenATriesToBondWithB($this->chun, $this->paola);
        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::FLIRT_ANTISOCIAL, $I);
    }

    private function givenPlayerIsAntisocial(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::ANTISOCIAL,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenATriesToBondWithB(Player $player, Player $target): void
    {
        $this->bondAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $player,
            player: $player,
            target: $target
        );
    }

    private function whenABondsWithB(Player $player, Player $target): void
    {
        $this->whenATriesToBondWithB($player, $target);
        $this->bondAction->execute();
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->bondAction->cannotExecuteReason(),
        );
    }

    private function thenAHasBInBondList(Player $player, Player $target, FunctionalTester $I): void
    {
        $I->assertTrue($player->hasBondeddWith($target));
    }
}
