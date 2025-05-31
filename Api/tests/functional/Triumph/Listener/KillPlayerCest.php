<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Listener;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class KillPlayerCest extends AbstractExplorationTester
{
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playerService = $I->grabService(PlayerServiceInterface::class);
    }

    public function shouldGiveMushTriumphOnChunDeath(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->kuanTi->setTriumph(0);

        // When Chun dies
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::DEPRESSION,
        );

        // Chun dead triumph
        $I->assertEquals(7, $this->kuanTi->getTriumph());
    }

    public function shouldNotGiveMushTriumphOnNonChunDeath(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $hua = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::HUA);
        $this->kuanTi->setTriumph(0);

        // When Hua dies
        $this->playerService->killPlayer(
            player: $hua,
            endReason: EndCauseEnum::DEPRESSION,
        );

        // Mush should not gain triumph
        $I->assertEquals(0, $this->kuanTi->getTriumph());
    }

    public function shouldGiveHumanGioeleTriumphOnMushDeath(FunctionalTester $I): void
    {
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);
        $this->convertPlayerToMush($I, $this->kuanTi);
        $gioele->setTriumph(0);
        $this->chun->setTriumph(0);

        // When Mush dies
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::DEPRESSION,
        );

        // Gioele gets triumph
        $I->assertEquals(3, $gioele->getTriumph());
        // Chun gets no triumph
        $I->assertEquals(0, $this->chun->getTriumph());
    }

    public function shouldNotGiveHumanGioeleTriumphOnHumanDeath(FunctionalTester $I): void
    {
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);
        $gioele->setTriumph(0);
        $this->chun->setTriumph(0);

        // When human dies
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::DEPRESSION,
        );

        // Gioele gets no triumph
        $I->assertEquals(0, $gioele->getTriumph());
        // Chun gets no triumph
        $I->assertEquals(0, $this->chun->getTriumph());
    }

    public function shouldNotGiveMushGioeleTriumphOnNonChunDeath(FunctionalTester $I): void
    {
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);
        $hua = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::HUA);
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->convertPlayerToMush($I, $gioele);
        $gioele->setTriumph(0);

        // When human dies
        $this->playerService->killPlayer(
            player: $hua,
            endReason: EndCauseEnum::DEPRESSION,
        );

        // Gioele gets no triumph
        $I->assertEquals(0, $gioele->getTriumph());

        // When Mush dies
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::DEPRESSION,
        );

        // Gioele gets no triumph
        $I->assertEquals(0, $gioele->getTriumph());
    }

    public function shouldNotGiveTriumphOnMushGioeleDeath(FunctionalTester $I): void
    {
        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);
        $this->convertPlayerToMush($I, $gioele);
        $gioele->setTriumph(0);
        $this->chun->setTriumph(0);

        // When Gioele dies
        $this->playerService->killPlayer(
            player: $gioele,
            endReason: EndCauseEnum::DEPRESSION,
        );

        // Gioele gets no triumph
        $I->assertEquals(0, $gioele->getTriumph());
        $I->assertEquals(0, $gioele->getPlayerInfo()->getClosedPlayer()->getTriumph());
        // Chun gets no triumph
        $I->assertEquals(0, $this->chun->getTriumph());
    }

    public function shouldGiveMushGloryOnHumanKill(FunctionalTester $I): void
    {
        $roland = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ROLAND);
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->convertPlayerToMush($I, $roland);
        $roland->setTriumph(0);
        $this->kuanTi->setTriumph(0);
        $this->chun->setTriumph(0);

        // When Mush kills Chun (+7 triumph to Mush team)
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::BLED,
            author: $this->kuanTi
        );

        // Killer Mush gains +3 triumph from a kill
        $I->assertEquals(10, $this->kuanTi->getTriumph());
        // Other Mush gain no extra triumph
        $I->assertEquals(7, $roland->getTriumph());
        // Human victim gains no triumph
        $I->assertEquals(0, $this->chun->getTriumph());
        $I->assertEquals(0, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function shouldNotGiveMushGloryOnMushKill(FunctionalTester $I): void
    {
        $roland = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ROLAND);
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->convertPlayerToMush($I, $roland);
        $roland->setTriumph(0);
        $this->kuanTi->setTriumph(0);

        // When Mush kills Mush
        $this->playerService->killPlayer(
            player: $roland,
            endReason: EndCauseEnum::BLED,
            author: $this->kuanTi
        );

        // No triumph gain
        $I->assertEquals(0, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $roland->getTriumph());
        $I->assertEquals(0, $roland->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function shouldGiveHumanGloryOnMushKill(FunctionalTester $I): void
    {
        $roland = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ROLAND);
        $this->convertPlayerToMush($I, $this->kuanTi);
        $roland->setTriumph(0);
        $this->chun->setTriumph(0);

        // When Chun kills Mush
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::ASSASSINATED,
            author: $this->chun
        );

        // Human killer gains triumph
        $I->assertEquals(3, $this->chun->getTriumph());
        // Non-killing humans gain no triumph from a kill
        $I->assertEquals(0, $roland->getTriumph());
    }

    public function shouldNotGiveHumanGloryOnHumanKill(FunctionalTester $I): void
    {
        $this->kuanTi->setTriumph(0);
        $this->chun->setTriumph(0);

        // When Chun kills human
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::INJURY,
            author: $this->chun
        );

        // No triumph gain
        $I->assertEquals(0, $this->chun->getTriumph());
        $I->assertEquals(0, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function shouldHumanChaoGetExtraGloryOnKill(FunctionalTester $I): void
    {
        $chao = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::CHAO);
        $this->convertPlayerToMush($I, $this->kuanTi);
        $chao->setTriumph(0);
        $this->kuanTi->setTriumph(0);
        $this->chun->setTriumph(0);

        // When Chao kills human
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::ROCKETED,
            author: $chao
        );

        // Chao gained 3 triumph from kill
        $I->assertEquals(3, $chao->getTriumph());

        // When Chao kills Mush
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::BEHEADED,
            author: $chao
        );

        // Chao gained 3 triumph from kill and 3 triumph from Mush kill
        $I->assertEquals(9, $chao->getTriumph());

        // Kuan Ti has gained 7 triumph from Chun death
        $I->assertEquals(7, $this->kuanTi->getTriumph());
        $I->assertEquals(7, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());

        // Chun gained no triumph
        $I->assertEquals(0, $this->chun->getTriumph());
        $I->assertEquals(0, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function shouldMushChaoGetNoExtraGloryOnKill(FunctionalTester $I): void
    {
        $chao = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::CHAO);
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->convertPlayerToMush($I, $chao);
        $chao->setTriumph(0);
        $this->kuanTi->setTriumph(0);
        $this->chun->setTriumph(0);

        // When Chao kills Mush
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::BEHEADED,
            author: $chao
        );

        // Chao gained no triumph
        $I->assertEquals(0, $chao->getTriumph());

        // When Chao kills Chun
        $this->playerService->killPlayer(
            player: $this->chun,
            endReason: EndCauseEnum::ROCKETED,
            author: $chao
        );

        // Chao gained 3 triumph from enemy kill and 7 triumph from Chun death (no personal kill triumph)
        $I->assertEquals(10, $chao->getTriumph());

        // Kuan Ti and Chun gained no triumph
        $I->assertEquals(0, $this->kuanTi->getTriumph());
        $I->assertEquals(0, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(0, $this->chun->getTriumph());
        $I->assertEquals(0, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function shouldDistributeTriumphOnPlayerAbducted(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->kuanTi->setTriumph(0);
        $this->chun->setTriumph(0);

        // When Chun sells Mush
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: EndCauseEnum::ALIEN_ABDUCTED,
            author: $this->chun
        );

        // Sold player gets triumph
        $I->assertEquals(16, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());

        // Trader gets no triumph despite selling an enemy
        $I->assertEquals(0, $this->chun->getTriumph());
    }
}
