<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Service\HunterServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HunterCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private HunterServiceInterface $hunterService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->hunterService = $I->grabService(HunterServiceInterface::class);
    }

    public function shouldGainTriumphOnShootingDownHunters(FunctionalTester $I)
    {
        $roland = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ROLAND);
        $this->givenHuntersSpawnOfAmount(30);
        $this->whenPlayerShootsDownHunters($roland, 9);
        $this->thenPlayerShouldHaveTriumph($roland, 18, $I); // should get double points up to this point
        $this->whenPlayerShootsDownHunters($roland, 1);
        $this->thenPlayerShouldHaveTriumph($roland, 19, $I); // gained only 1 point, extra point prevented due to regressive factor
        $this->whenPlayerShootsDownHunters($roland, 1);
        $this->thenPlayerShouldHaveTriumph($roland, 21, $I);
        $this->whenPlayerShootsDownHunters($roland, 1);
        $this->thenPlayerShouldHaveTriumph($roland, 22, $I);
    }

    private function givenHuntersSpawnOfAmount(int $quantity): void
    {
        $hunterValue = $this->daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow(HunterEnum::HUNTER)->getDrawCost();
        $this->daedalus->setHunterPoints($hunterValue * $quantity);
        $unpoolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($unpoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
    }

    private function whenPlayerShootsDownHunters(Player $player, int $quantity): void
    {
        for ($i = 0; $i < $quantity; ++$i) {
            $hunter = $this->daedalus->getHuntersAroundDaedalus()->first();
            $this->hunterService->killHunter($hunter, [ActionEnum::SHOOT_HUNTER->value], $player);
        }
    }

    private function thenPlayerShouldHaveTriumph(Player $player, int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $player->getTriumph());
    }
}
