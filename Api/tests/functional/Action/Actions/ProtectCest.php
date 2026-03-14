<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Hit;
use Mush\Action\Actions\Protect;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProtectCest extends AbstractFunctionalTest
{
    private Hit $hitAction;
    private ActionConfig $action;

    private Protect $protectAction;
    private ActionConfig $protectConfig;
    private AddSkillToPlayerService $addSkillToPlayer;
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::HIT]);
        $this->protectConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PROTECT]);

        $this->hitAction = $I->grabService(Hit::class);
        $this->protectAction = $I->grabService(Protect::class);

        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
    }

    public function shouldGiveStatusAndRemoveOldOnes(FunctionalTester $I)
    {
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->givenChunHasBodyguardSkill();

        $this->whenChunProtectPlayer($this->kuanTi);

        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::BODYGUARD_USER));
        $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::BODYGUARD_VIP));

        $this->whenChunProtectPlayer($derek);

        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::BODYGUARD_USER));
        $I->assertTrue($derek->hasStatus(PlayerStatusEnum::BODYGUARD_VIP));

        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::BODYGUARD_VIP));
    }

    public function shouldRemoveStatusIfUserDie(FunctionalTester $I)
    {
        $this->givenChunHasBodyguardSkill();

        $this->whenChunProtectPlayer($this->kuanTi);
        $this->playerService->killPlayer($this->chun, EndCauseEnum::SUPER_NOVA);

        $I->assertFalse($this->kuanTi->hasStatus(PlayerStatusEnum::BODYGUARD_VIP));
    }

    public function shouldRemoveStatusIfVIPDie(FunctionalTester $I)
    {
        $this->givenChunHasBodyguardSkill();

        $this->whenChunProtectPlayer($this->kuanTi);
        $this->playerService->killPlayer($this->kuanTi, EndCauseEnum::SUPER_NOVA);

        $I->assertFalse($this->chun->hasStatus(PlayerStatusEnum::BODYGUARD_USER));
    }

    public function shouldIncreaseAPCostIfBodyguardInRoom(FunctionalTester $I)
    {
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);

        $this->givenChunHasBodyguardSkill();

        $this->whenChunProtectPlayer($derek);

        $this->givenPlayerHasActionPoint($this->kuanTi, 8);

        $this->whenPlayerHitsPlayer($this->kuanTi, $derek);

        $I->assertEquals(5, $this->kuanTi->getActionPoint());
    }

    public function shouldNotIncreaseAPCostIfBodyguardNotInRoom(FunctionalTester $I)
    {
        $derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $nexus = $this->createExtraPlace(RoomEnum::NEXUS, $I, $this->daedalus);
        $this->givenChunHasBodyguardSkill();

        $this->whenChunProtectPlayer($derek);
        $this->chun->setPlace($nexus); // chun move to the nexus

        $this->givenPlayerHasActionPoint($this->kuanTi, 8);

        $this->whenPlayerHitsPlayer($this->kuanTi, $derek);

        $I->assertEquals(7, $this->kuanTi->getActionPoint());
    }

    private function givenChunHasBodyguardSkill(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::BODYGUARD, $this->chun);
    }

    private function givenPlayerHasActionPoint(Player $player, int $actionPoint): void
    {
        $player->setActionPoint($actionPoint);
    }

    private function whenPlayerHitsPlayer(Player $player1, Player $player2): void
    {
        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $player1,
            player: $player1,
            target: $player2,
        );
        $this->hitAction->execute();
    }

    private function whenChunProtectPlayer(Player $player): void
    {
        $this->protectAction->loadParameters(
            actionConfig: $this->protectConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $player,
        );
        $this->protectAction->execute();
    }
}
