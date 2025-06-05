<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class EdenCest extends AbstractExplorationTester
{
    private DaedalusServiceInterface $daedalusService;
    private EventServiceInterface $eventService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // Given Chun and Kuan Ti in the ship
    }

    // NOTE: Include no cat triumph on implementation
    public function testTwoHumanMenEden(FunctionalTester $I): void
    {
        $this->givenPlayerDies($this->chun);

        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        $this->whenDaedalusTravelsToEden();

        $aliveHumansTriumph = [
            $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph(),
            $gioele->getPlayerInfo()->getClosedPlayer()->getTriumph(),
        ];
        // triumph: 6 (eden_at_least) + 2 (eden_one_man) + 8 to random person (lander)
        $I->assertEqualsCanonicalizing([16, 8], $aliveHumansTriumph);
        $I->assertEquals(0, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function testTwoMushMenEden(FunctionalTester $I): void
    {
        $this->givenPlayerDies($this->chun);

        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->convertPlayerToMush($I, $gioele);

        $this->givenEveryoneHasTriumph(0);

        $this->whenDaedalusTravelsToEden();

        $aliveHumansTriumph = [
            $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph(),
            $gioele->getPlayerInfo()->getClosedPlayer()->getTriumph(),
        ];
        // triumph: 32 (eden_mush_invasion) + 8 to random person (lander)
        $I->assertEqualsCanonicalizing([40, 32], $aliveHumansTriumph);
        $I->assertEquals(0, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    private function givenPlayerDies(Player $player): void
    {
        $this->playerService->killPlayer(
            player: $player,
            endReason: EndCauseEnum::DEPRESSION,
        );
    }

    private function givenEveryoneHasTriumph(int $quantity): void
    {
        /** @var Player $player */
        foreach ($this->daedalus->getPlayers() as $player) {
            $player->setTriumph($quantity);
        }
    }

    private function whenDaedalusTravelsToEden(): void
    {
        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::TRAVEL_TO_EDEN->toString()],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusEvent::FINISH_DAEDALUS);
    }
}
