<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Event;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class OnPlayerDeathCest extends AbstractExplorationTester
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

    public function shouldGiveHumanGioeleTriumphOnHumanDeath(FunctionalTester $I): void
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

    public function shouldGiveMushGioeleTriumphOnNonChunDeath(FunctionalTester $I): void
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
}
