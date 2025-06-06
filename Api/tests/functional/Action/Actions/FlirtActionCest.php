<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Flirt;
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
final class FlirtActionCest extends AbstractFunctionalTest
{
    private Flirt $flirtAction;
    private ActionConfig $action;

    private StatusServiceInterface $statusService;

    private Player $derek;
    private Player $andie;
    private Player $gioele;
    private Player $paola;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->flirtAction = $I->grabService(Flirt::class);
        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::FLIRT]);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->andie = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);
        $this->gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);
        $this->paola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        $this->players->add($this->derek);
        $this->players->add($this->andie);
        $this->players->add($this->gioele);
        $this->players->add($this->paola);

        $this->givenFreeLoveIs(false);
    }

    public function shouldFlirtIfPlayersAreDifferentSexesAndFreeLoveIsFalse(FunctionalTester $I): void
    {
        $this->whenAFlirtsWithB($this->chun, $this->kuanTi);
        $this->thenAHasBInFlirtList($this->chun, $this->kuanTi, $I);
    }

    public function shouldNotFlirtIfPlayersAlreadyFlirted(FunctionalTester $I): void
    {
        $this->whenAFlirtsWithB($this->chun, $this->kuanTi);

        $this->whenATriesToFlirtWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::FLIRT_ALREADY_FLIRTED, $I);
    }

    public function shouldNotFlirtIfPlayersAreSameSexAndFreeLoveIsFalse(FunctionalTester $I): void
    {
        $this->whenATriesToFlirtWithB($this->derek, $this->kuanTi);
        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldMaleFlirtIfPlayerIsAndieAndFreeLoveIsFalse(FunctionalTester $I): void
    {
        $this->whenAFlirtsWithB($this->andie, $this->kuanTi);
        $this->thenAHasBInFlirtList($this->andie, $this->kuanTi, $I);
    }

    public function shouldFemaleFlirtIfPlayerIsAndieAndFreeLoveIsFalse(FunctionalTester $I): void
    {
        $this->whenAFlirtsWithB($this->andie, $this->chun);
        $this->thenAHasBInFlirtList($this->andie, $this->chun, $I);
    }

    public function shouldMaleFlirtIfTargetIsAndieAndFreeLoveIsFalse(FunctionalTester $I): void
    {
        $this->whenAFlirtsWithB($this->kuanTi, $this->andie);
        $this->thenAHasBInFlirtList($this->kuanTi, $this->andie, $I);
    }

    public function shouldFemaleFlirtIfTargetIsAndieAndFreeLoveIsFalse(FunctionalTester $I): void
    {
        $this->whenAFlirtsWithB($this->chun, $this->andie);
        $this->thenAHasBInFlirtList($this->chun, $this->andie, $I);
    }

    public function shouldFlirtIfFreeLoveIsTrue(FunctionalTester $I): void
    {
        $this->givenFreeLoveIs(true);

        $this->whenAFlirtsWithB($this->derek, $this->kuanTi);
        $this->thenAHasBInFlirtList($this->derek, $this->kuanTi, $I);
    }

    public function paolaAndGioeleCannotFlirtTogetherEver(FunctionalTester $I): void
    {
        $this->whenATriesToFlirtWithB($this->gioele, $this->paola);
        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::FLIRT_SAME_FAMILY, $I);

        $this->givenFreeLoveIs(true);

        $this->whenATriesToFlirtWithB($this->gioele, $this->paola);
        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::FLIRT_SAME_FAMILY, $I);
    }

    public function antisocialCannotFlirtEver(FunctionalTester $I): void
    {
        $this->givenPlayerIsAntisocial($this->chun);

        $this->whenATriesToFlirtWithB($this->chun, $this->derek);
        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::FLIRT_ANTISOCIAL, $I);

        $this->givenFreeLoveIs(true);

        $this->whenATriesToFlirtWithB($this->chun, $this->paola);
        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::FLIRT_ANTISOCIAL, $I);
    }

    public function derekShouldGainTriumphWhenLovingBack(FunctionalTester $I): void
    {
        $this->whenAFlirtsWithB($this->chun, $this->derek);
        $this->thenDerekShouldHaveTriumph(0, $I);

        $this->whenAFlirtsWithB($this->derek, $this->chun);
        $this->thenDerekShouldHaveTriumph(2, $I);
    }

    public function derekShouldGainTriumphWhenLovedBack(FunctionalTester $I): void
    {
        $this->whenAFlirtsWithB($this->derek, $this->chun);
        $this->thenDerekShouldHaveTriumph(0, $I);

        $this->whenAFlirtsWithB($this->chun, $this->derek);
        $this->thenDerekShouldHaveTriumph(2, $I);
    }

    public function derekShouldNotGainTriumphWhenTwoOtherPeopleLoveEachOther(FunctionalTester $I): void
    {
        $this->whenAFlirtsWithB($this->gioele, $this->chun);
        $this->whenAFlirtsWithB($this->chun, $this->gioele);
        $this->thenDerekShouldHaveTriumph(0, $I);
    }

    private function givenFreeLoveIs(bool $bool)
    {
        $this->daedalus->getDaedalusConfig()->setFreeLove($bool);
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

    private function whenATriesToFlirtWithB(Player $player, Player $target)
    {
        $this->flirtAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $player,
            player: $player,
            target: $target
        );
    }

    private function whenAFlirtsWithB(Player $player, Player $target)
    {
        $this->whenATriesToFlirtWithB($player, $target);
        $this->flirtAction->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->flirtAction->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->flirtAction->cannotExecuteReason(),
        );
    }

    private function thenAHasBInFlirtList(Player $player, Player $target, FunctionalTester $I): void
    {
        $I->assertTrue($player->hasFlirtedWith($target));
    }

    private function thenDerekShouldHaveTriumph(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->derek->getTriumph());
    }
}
